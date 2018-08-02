<?php

namespace Drupal\acq_sku\Plugin\rest\resource;

use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_sku\CategoryRepositoryInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductOptionsManager;
use Drupal\acq_sku\SKUFieldsManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
                              SKUFieldsManager $sku_fields_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->queryFactory = $query_factory;
    $this->categoryRepo = $cat_repo;
    $this->productOptionsManager = $product_options_manager;
    $this->i18nHelper = $i18n_helper;
    $this->skuFieldsManager = $sku_fields_manager;
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
      $container->get('logger.factory')->get('acq_commerce'),
      $container->get('config.factory'),
      $container->get('entity.query'),
      $container->get('acq_sku.category_repo'),
      $container->get('acq_sku.product_options_manager'),
      $container->get('acq_commerce.i18n_helper'),
      $container->get('acq_sku.fields_manager')
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
  public function post(array $products = []) {
    $lock = \Drupal::lock();

    $em = $this->entityManager->getStorage('acq_sku');
    $created = 0;
    $updated = 0;
    $failed = 0;
    $ignored = 0;
    $deleted = 0;

    $config = $this->configFactory->get('acq_commerce.conductor');
    $debug = $config->get('debug');
    $debug_dir = $config->get('debug_dir');

    foreach ($products as $product) {
      try {
        $langcode = $this->i18nHelper->getLangcodeFromStoreId($product['store_id']);

        // Magento might have stores that what we don't support.
        if (empty($langcode)) {
          $this->logger->error('Langcode not found for product @sku with store id @store_id.', [
            '@store_id' => $product['store_id'],
            '@sku' => $product['sku'],
          ]);
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
          continue;
        }

        $query = $this->queryFactory->get('acq_sku_type');
        $query->condition('id', $product['type']);
        $query->count();

        $has_bundle = $query->execute();

        if (!$has_bundle) {
          $this->logger->warning('Product type @type not supported.', [
            '@type' => $product['type'],
          ]);
          $ignored++;
          continue;
        }

        if (!isset($product['sku']) || !strlen($product['sku'])) {
          $this->logger->warning('Invalid or empty product SKU.');
          $ignored++;
          continue;
        }

        // Don't import configurable SKU if it has no configurable options.
        if ($product['type'] == 'configurable' && empty($product['extension']['configurable_product_options'])) {
          $this->logger->warning('Empty configurable options for SKU: @sku', ['@sku' => $product['sku']]);
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

        if ($sku = SKU::loadFromSku($product['sku'], $langcode, FALSE, TRUE)) {
          if ($product['status'] != 1) {
            $this->logger->info('Removing disabled SKU from system: @sku.', ['@sku' => $product['sku']]);

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

            continue;
          }

          $this->logger->info('Updating product SKU @sku.', ['@sku' => $product['sku']]);
          $updated++;
        }
        else {
          if ($product['status'] != 1) {
            $this->logger->info('Not creating disabled SKU in system: @sku.', ['@sku' => $product['sku']]);
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

          $this->logger->info('Creating product SKU @sku.', ['@sku' => $product['sku']]);

          $created++;
        }

        $sku->name->value = html_entity_decode($product['name']);
        $sku->price->value = $product['price'];
        $sku->special_price->value = $product['special_price'];
        $sku->final_price->value = $product['final_price'];
        $sku->attributes = $this->formatProductAttributes($product['attributes']);

        // Set default value of stock to 0.
        $stock = 0;

        if (isset($product['extension']['stock_item'],
            $product['extension']['stock_item']['is_in_stock'],
            $product['extension']['stock_item']['qty'])
          && $product['extension']['stock_item']['is_in_stock']) {

          // Store stock value in sku.
          $stock = $product['extension']['stock_item']['qty'];
        }

        $sku->get('stock')->setValue($stock);

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

        if ($product['status'] == 1 && $product['visibility'] == 1) {
          $node = $plugin->getDisplayNode($sku, FALSE, TRUE);

          if (empty($node)) {
            $node = $this->createDisplayNode($product, $langcode);
          }

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

          // Invoke the alter hook to allow all modules to update the node.
          \Drupal::moduleHandler()->alter('acq_sku_product_node', $node, $product);

          $node->save();
        }
        else {
          try {
            // Un-publish if node available.
            if ($node = $plugin->getDisplayNode($sku, FALSE, FALSE)) {
              $node->setPublished(FALSE);
              $node->save();
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
        $failed++;

        // Add the unknown reason to logs.
        $this->logger->warning('Not able to save product SKU @sku. Exception: @message', [
          '@sku' => $product['sku'],
          '@message' => $e->getMessage(),
        ]);
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
      'success' => !$failed && ($created || $updated || $ignored || $deleted),
      'created' => $created,
      'updated' => $updated,
      'failed' => $failed,
      'ignored' => $ignored,
      'deleted' => $deleted,
    ];

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

      if (!isset($values[$source])) {
        continue;
      }

      $value = $values[$source];
      $field_key = 'attr_' . $key;

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
          $value = isset($field['serialize']) ? serialize($value) : $value;
          $sku->{$field_key}->setValue($value);
          break;
      }
    }
  }

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
          if (substr_compare($data['file'], $image, -strlen($image) ) === 0) {
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

}
