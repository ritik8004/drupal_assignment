<?php

namespace Drupal\acq_sku\Plugin\rest\resource;

use Differ\ArrayDiff;
use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_sku\CategoryRepositoryInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Event\AcqSkuValidateEvent;
use Drupal\acq_sku\ProductOptionsManager;
use Drupal\acq_sku\SKUFieldsManager;
use Drupal\Component\Utility\DiffArray;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ProductSyncResource.
 *
 * @package Drupal\acq_sku\Plugin
 *
 * @ingroup acq_sku
 *
 * @RestResource(
 *   id = "acq_productsync",
 *   label = @Translation("Acquia Commerce Product Sync"),
 *   uri_paths = {
 *     "canonical" = "/productsync",
 *     "https://www.drupal.org/link-relations/create" = "/productsync"
 *   }
 * )
 */
class ProductSyncResource extends ResourceBase {

  /**
   * Category Repository.
   *
   * @var \Drupal\acq_sku\CategoryRepositoryInterface
   */
  private $categoryRepo;

  /**
   * Product Options Manager service instance.
   *
   * @var \Drupal\acq_sku\ProductOptionsManager
   */
  private $productOptionsManager;

  /**
   * Drupal Config Factory Instance.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * Drupal Entity Type Manager Instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityManager;

  /**
   * Drupal Entity Query Factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  private $queryFactory;

  /**
   * I18n Helper.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  private $i18nHelper;

  /**
   * SKU Fields Manager.
   *
   * @var \Drupal\acq_sku\SKUFieldsManager
   */
  private $skuFieldsManager;

  /**
   * Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcher
   */
  private $eventDispatcher;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The query factory.
   * @param \Drupal\acq_sku\CategoryRepositoryInterface $cat_repo
   *   Category Repository instance.
   * @param \Drupal\acq_sku\ProductOptionsManager $product_options_manager
   *   Product Options Manager service instance.
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18nHelper object.
   * @param \Drupal\acq_sku\SKUFieldsManager $sku_fields_manager
   *   SKU Fields Manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher object.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entity_type_manager,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              ConfigFactoryInterface $config_factory,
                              QueryFactory $query_factory,
                              CategoryRepositoryInterface $cat_repo,
                              ProductOptionsManager $product_options_manager,
                              I18nHelper $i18n_helper,
                              SKUFieldsManager $sku_fields_manager,
                              EventDispatcherInterface $event_dispatcher) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->queryFactory = $query_factory;
    $this->categoryRepo = $cat_repo;
    $this->productOptionsManager = $product_options_manager;
    $this->i18nHelper = $i18n_helper;
    $this->skuFieldsManager = $sku_fields_manager;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get(self::class),
      $container->get('config.factory'),
      $container->get('entity.query'),
      $container->get('acq_sku.category_repo'),
      $container->get('acq_sku.product_options_manager'),
      $container->get('acq_commerce.i18n_helper'),
      $container->get('acq_sku.fields_manager'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * Post.
   *
   * Handle Conductor posting an array of product / SKU data for update.
   *
   * @param array $products
   *   Product / SKU Data.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   HTTP Response object.
   */
  public function post(array $products) {
    /** @var \Drupal\Core\Lock\PersistentDatabaseLockBackend $lock */
    $lock = \Drupal::service('lock.persistent');

    $em = $this->entityManager->getStorage('acq_sku');
    $created = 0;
    $updated = 0;
    $failed = 0;
    $ignored = 0;
    $deleted = 0;
    $ignored_skus = [];

    $config = $this->configFactory->get('acq_commerce.conductor');
    $debug = $config->get('debug');
    $debug_dir = $config->get('debug_dir');

    foreach ($products as $product) {
      try {
        // Allow other modules to subscribe to pre-validation of the SKU being
        // imported.
        $event = new AcqSkuValidateEvent($product);
        $this->eventDispatcher->dispatch(AcqSkuValidateEvent::ACQ_SKU_VALIDATE, $event);
        $product = $event->getProduct();

        // If skip attribute is set via any event subscriber, skip importing the
        // product.
        if ($product['skip']) {
          $ignored_skus[] = $product['sku'] . '(SKU doesn\'t meet the criteria for import set for this site.)';
          $ignored++;
          continue;
        }

        $langcode = $this->i18nHelper->getLangcodeFromStoreId($product['store_id']);

        // Magento might have stores that what we don't support.
        if (empty($langcode)) {
          $ignored_skus[] = $product['sku'] . '(unsupported store id:' . $product['store_id'] . ')';
          $ignored++;
          continue;
        }

        if ($debug && !empty($debug_dir)) {
          // Export product data into file.
          if (!isset($fps) || !isset($fps[$langcode])) {
            $filename = $debug_dir . '/products_' . $langcode . '.data';
            $fps[$langcode] = fopen($filename, 'a');
          }
          fwrite($fps[$langcode], var_export($product, 1));
          fwrite($fps[$langcode], '\n');
        }

        if (!isset($product['type'])) {
          $ignored_skus[] = $product['sku'] . '(Missing product type)';
          $ignored++;
          continue;
        }

        $query = $this->queryFactory->get('acq_sku_type');
        $query->condition('id', $product['type']);
        $query->count();

        $has_bundle = $query->execute();

        if (!$has_bundle) {
          $ignored_skus[] = $product['sku'] . '(unsupported product type:' . $product['type'] . ' )';
          $ignored++;
          continue;
        }

        if (!isset($product['sku']) || !strlen($product['sku'])) {
          $this->logger->warning('Invalid or empty product SKU.');
          $ignored_skus[] = $product['sku'] . '(invalid sku)';
          $ignored++;
          continue;
        }

        // Don't import configurable SKU if it has no configurable options.
        if ($product['type'] == 'configurable' && empty($product['extension']['configurable_product_options'])) {
          $ignored_skus[] = $product['sku'] . '(empty configurable options)';
          $ignored++;
          continue;
        }

        $lock_key = 'synchronizeProduct' . $product['sku'];

        // Acquire lock to ensure parallel processes are executed one by one.
        do {
          $lock_acquired = $lock->acquire($lock_key);

          // Sleep for half a second before trying again.
          if (!$lock_acquired) {
            usleep(500000);
          }
        } while (!$lock_acquired);

        $skuData = [];

        if ($sku = SKU::loadFromSku($product['sku'], $langcode, FALSE, TRUE)) {
          $skuData = $sku->toArray();

          if ($product['status'] != 1) {
            try {
              /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
              $plugin = $sku->getPluginInstance();

              if ($node = $plugin->getDisplayNode($sku, FALSE, FALSE)) {
                // Delete the node if it is linked to this SKU only.
                $node->delete();
              }
            }
            catch (\Exception $e) {
              // Not doing anything, we might not have node for the sku.
            }

            // Delete the SKU.
            $sku->delete();

            $deleted++;

            // Release the lock.
            $lock->release($lock_key);

            $this->logger->info('Deleted SKU @sku for @langcode', [
              '@sku' => $sku->getSku(),
              '@langcode' => $langcode,
            ]);

            continue;
          }

          $updated++;
        }
        else {
          if ($product['status'] != 1) {
            $ignored_skus[] = $product['sku'] . '(disabled)';
            $ignored++;

            // Release the lock.
            $lock->release($lock_key);

            continue;
          }

          /** @var \Drupal\acq_sku\Entity\SKU $sku */
          $sku = $em->create([
            'type' => $product['type'],
            'sku' => $product['sku'],
            'langcode' => $langcode,
          ]);

          $created++;
        }

        $sku->name->value = html_entity_decode($product['name']);
        $sku->price->value = $product['price'];
        $sku->special_price->value = $product['special_price'];
        $sku->final_price->value = $product['final_price'];
        $sku->attributes = $this->formatProductAttributes($product['attributes']);
        $sku->get('attr_description')->setValue([
          'value' => (isset($product['attributes']['description'])) ? $product['attributes']['description'] : '',
          'format' => 'rich_text',
        ]);

        // Update product media to set proper position.
        $sku->media = $this->getProcessedMedia($product, $sku->media->value);

        $sku->attribute_set = $product['attribute_set_label'];
        $sku->product_id = $product['product_id'];

        // Update the fields based on the values from attributes.
        $this->updateFields('attributes', $sku, $product['attributes']);

        // Update the fields based on the values from extension.
        $this->updateFields('extension', $sku, $product['extension']);

        // Update upsell linked SKUs.
        $this->updateLinkedSkus('upsell', $sku, $product['linked']);

        // Update crosssell linked SKUs.
        $this->updateLinkedSkus('crosssell', $sku, $product['linked']);

        // Update related linked SKUs.
        $this->updateLinkedSkus('related', $sku, $product['linked']);

        /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
        $plugin = $sku->getPluginInstance();
        $plugin->processImport($sku, $product);

        // Invoke the alter hook to allow all modules to update the sku.
        \Drupal::moduleHandler()->alter('acq_sku_product_sku', $sku, $product);

        $sku->save();

        // $skuData will have value when it is updating.
        if ($skuData) {
          // Load SKU again to have exact same data structure.
          $sku_entity = SKU::loadFromSku($product['sku'], $langcode, FALSE, TRUE);
          $updatedSkuData = $sku_entity->toArray();

          $this->logger->info('Updated SKU @sku for @langcode: @diff', [
            '@sku' => $sku->getSku(),
            '@langcode' => $langcode,
            '@diff' => self::getArrayDiff($skuData, $updatedSkuData),
          ]);
        }
        else {
          $this->logger->info('New SKU @sku for @langcode', [
            '@sku' => $sku->getSku(),
            '@langcode' => $langcode,
          ]);
        }

        if ($product['status'] == 1 && $product['visibility'] == 1) {
          $node = $plugin->getDisplayNode($sku, FALSE, TRUE);

          if (empty($node)) {
            $node = $this->createDisplayNode($product, $langcode);
          }

          $existingCategories = $node->get('field_category')->getValue();

          $node->get('title')->setValue(html_entity_decode($product['name']));

          $description = (isset($product['attributes']['description'])) ? $product['attributes']['description'] : '';
          $node->get('body')->setValue([
            'value' => $description,
            'format' => 'rich_text',
          ]);

          $categories = (isset($product['categories'])) ? $product['categories'] : [];
          $categories = $this->formatCategories($categories);
          $node->field_category = $categories;

          $node->setPublished(TRUE);

          // We doing this because when the translation of node is created by
          // addTranslation(), pathauto alias is not created for the translated
          // version.
          // @see https://www.drupal.org/project/pathauto/issues/2995829.
          if (\Drupal::moduleHandler()->moduleExists('pathauto')) {
            $node->path->pathauto = 1;
          }

          // Invoke the alter hook to allow all modules to update the node.
          \Drupal::moduleHandler()->alter('acq_sku_product_node', $node, $product);

          $node->save();

          $updatedCategories = $node->get('field_category')->getValue();
          $this->logger->info('Categories diff for @sku for @langcode: @diff', [
            '@sku' => $sku->getSku(),
            '@langcode' => $langcode,
            '@diff' => self::getArrayDiff($existingCategories, $updatedCategories),
          ]);
        }
        else {
          try {
            // Un-publish if node available.
            if ($node = $plugin->getDisplayNode($sku, FALSE, FALSE)) {
              $node->delete();
            }
          }
          catch (\Exception $e) {
            // Do nothing, we may not have the node available in system.
          }
        }
      }
      catch (\Exception $e) {
        // We consider this as failure as it failed for an unknown reason.
        // (not taken care of above).
        $failed_skus[] = $product['sku'] . '(' . $e->getMessage() .')';
        $failed++;
      }
      catch (\Throwable $e) {
        // We consider this as failure as it failed for an unknown reason.
        // (not taken care of above).
        $failed_skus[] = $product['sku'] . '(' . $e->getMessage() .')';
        $failed++;
      }
      finally {
        // Release the lock if acquired.
        if (!empty($lock_key) && !empty($lock_acquired)) {
          $lock->release($lock_key);

          // We will come here again for next loop item and we might face
          // exception before we reach the code that sets $lock_key.
          // To ensure we don't keep releasing the lock again and again
          // we set it to NULL here.
          $lock_key = NULL;
        }
      }
    }

    if (isset($fps)) {
      foreach ($fps as $fp) {
        fclose($fp);
      }
    }

    $response = [
      'success' => TRUE,
      'created' => $created,
      'updated' => $updated,
      'failed' => $failed,
      'ignored' => $ignored,
      'deleted' => $deleted,
    ];

    // Log Product sync summary for ignored ones.
    if (!empty($ignored_skus)) {
      $this->logger->info('Ignored SKUs: @ignored_skus', ['@ignored_skus' => implode(',', $ignored_skus)]);
    }

    // Log Product sync summary for failed ones.
    if (!empty($failed_skus)) {
      $this->logger->error('Failed SKUs: @failed_skus', ['@failed_skus' => implode(',', $failed_skus)]);
    }

    return (new ModifiedResourceResponse($response));
  }

  /**
   * CreateDisplayNode.
   *
   * Create a product display node for a set of SKU entities.
   *
   * @param array $product
   *   Product data.
   * @param string $langcode
   *   Language code.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Node object.
   */
  private function createDisplayNode(array $product, $langcode = '') {
    $data = [
      'type' => 'acq_product',
      'field_skus' => [$product['sku']],
    ];

    if ($langcode) {
      $data['langcode'] = $langcode;
    }

    $node = $this->entityManager->getStorage('node')->create($data);

    return $node;
  }

  /**
   * FormatCategories.
   *
   * @return array
   *   Array of terms.
   */
  private function formatCategories(array $categories) {

    $terms = [];

    foreach ($categories as $cid) {
      $term = $this->categoryRepo->loadCategoryTerm($cid);
      if ($term) {
        $terms[] = $term->id();
      }
    }

    return ($terms);
  }

  /**
   * FormatProductAttributes.
   *
   * Format the product attributes data as an array for saving in a
   * key value field.
   *
   * @param array $attributes
   *   Array of product attributes.
   *
   * @return array
   *   Array of formatted product attributes.
   */
  private function formatProductAttributes(array $attributes) {

    $formatted = [];

    foreach ($attributes as $name => $value) {
      if (is_array($value)) {
        continue;
      }

      if (!strlen($value)) {
        continue;
      }

      $formatted[] = [
        'key' => $name,
        'value' => utf8_encode(substr((string) $value, 0, 100)),
      ];
    }

    return ($formatted);
  }

  /**
   * Update linked Skus.
   *
   * Prepare the field value for linked type (upsell, crosssell, etc.).
   * Get the position based on the position coming from API.
   *
   * @param string $type
   *   Type of link.
   * @param Drupal\acq_sku\Entity\SKU $sku
   *   Root SKU.
   * @param array $linked
   *   Linked SKUs.
   */
  private function updateLinkedSkus($type, SKU &$sku, array $linked) {
    // Reset the upsell skus to null.
    $sku->{$type}->setValue([]);

    $fieldData = [];

    foreach ($linked as $link) {
      if ($link['type'] != $type) {
        continue;
      }

      $fieldData[$link['position']] = $link['linked_sku'];
    }

    // If there is no upsell skus to link, we simply return from here.
    if (empty($fieldData)) {
      return;
    }

    // Sort them based on position.
    ksort($fieldData);

    // Update the index to sequential values so we can set in field.
    $fieldData = array_values($fieldData);

    foreach ($fieldData as $delta => $value) {
      $sku->{$type}->set($delta, $value);
    }
  }

  /**
   * Update attribute fields.
   *
   * Update the fields based on the values from attributes.
   *
   * @param string $parent
   *   Fields to get from this parent will be processed.
   * @param Drupal\acq_sku\Entity\SKU $sku
   *   The root SKU.
   * @param array $values
   *   The product attributes/extensions to get value from.
   */
  private function updateFields($parent, SKU $sku, array $values) {

    $additionalFields = $this->skuFieldsManager->getFieldAdditions();

    // Filter fields for the parent requested.
    $additionalFields = array_filter($additionalFields, function ($field) use ($parent) {
      return ($field['parent'] == $parent);
    });

    // Loop through all the fields we want to read from product data.
    foreach ($additionalFields as $key => $field) {
      $source = isset($field['source']) ? $field['source'] : $key;

      // Field key.
      $field_key = 'attr_' . $key;

      // If attribute is not coming in response, then unset it.
      if (!isset($values[$source])) {
        if ($sku->{$field_key}->getValue()) {
          $sku->{$field_key}->setValue(NULL);
        }

        continue;
      }

      $value = $values[$source];

      switch ($field['type']) {
        case 'attribute':
          $value = $field['cardinality'] != 1 ? explode(',', $value) : [$value];
          foreach ($value as $index => $val) {
            if ($term = $this->productOptionsManager->loadProductOptionByOptionId($source, $val, $sku->language()->getId())) {
              $sku->{$field_key}->set($index, $term->getName());
            }
            else {
              $sku->{$field_key}->set($index, $val);
            }
          }
          break;

        case 'string':
          $value = $field['cardinality'] != 1 ? explode(',', $value) : $value;
          $sku->{$field_key}->setValue($value);
          break;

        case 'text_long':
          $value = !empty($field['serialize']) ? serialize($value) : $value;
          $sku->{$field_key}->setValue($value);
          break;
      }
    }
  }

  /**
   *
   */
  protected function getProcessedMedia($product, $current_value) {
    $media = [];

    if (isset($product['extension'], $product['extension']['media'])) {
      $media = $product['extension']['media'];

      // @TODO: Remove this hard coded fix after getting answer why we have
      // empty second array index and why all media come in first array index.
      $media = reset($media);

      if (isset($product['attributes'], $product['attributes']['image'])) {
        $image = $product['attributes']['image'];

        foreach ($media as &$data) {
          if (substr_compare($data['file'], $image, -strlen($image)) === 0) {
            $data['position'] = -1;
            break;
          }
        }
      }

      // Sort media data by position.
      usort($media, function ($a, $b) {
        $position1 = (int) $a['position'];
        $position2 = (int) $b['position'];

        if ($position1 == $position2) {
          return 0;
        }

        return ($position1 < $position2) ? -1 : 1;
      });
    }

    // Reassign old files to not have to redownload them.
    if (!empty($media)) {
      $current_media = unserialize($current_value);
      if (!empty($current_media) && is_array($current_media)) {
        $current_mapping = [];
        foreach ($current_media as $value) {
          if (!empty($value['fid'])) {
            $current_mapping[$value['value_id']]['fid'] = $value['fid'];
            $current_mapping[$value['value_id']]['file'] = $value['file'];
          }
        }

        foreach ($media as $key => $value) {
          if (isset($current_mapping[$value['value_id']])) {
            $media[$key]['fid'] = $current_mapping[$value['value_id']]['fid'];
            $media[$key]['file'] = $current_mapping[$value['value_id']]['file'];
          }
        }
      }
    }

    return serialize($media);
  }

  /**
   * Helper function to get recursive array diff.
   *
   * @param array $array1
   *   Array one.
   * @param array $array2
   *   Array two.
   *
   * @return string
   *   JSON string of array containing diff of two arrays.
   */
  public static function getArrayDiff($array1, $array2): string {
    $differ = new ArrayDiff();
    return json_encode($differ->diff($array1, $array2));
  }

}
