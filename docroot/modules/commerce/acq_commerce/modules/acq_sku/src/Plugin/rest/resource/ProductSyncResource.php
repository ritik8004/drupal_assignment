<?php
/**
 * @file
 * Contains Drupal\acq_sku\Plugin\rest\resource\ProductSyncResource
 */

namespace Drupal\acq_sku\Plugin\rest\resource;

use Drupal\acq_sku\CategoryRepositoryInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ProductSyncResource
 * @package Drupal\acq_sku\Plugin
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
   * Category Repository
   * @var CategoryRepositoryInterface $categoryRepo
   */
  private $categoryRepo;

  /**
   * Drupal Config Factory Instance
   * @var ConfigFactoryInterface $configFactory
   */
  private $configFactory;

  /**
   * Drupal Entity Type Manager Instance
   * @var EntityTypeManagerInterface $entityManager
   */
  private $entityManager;

  /**
   * Drupal Entity Query Factory
   * @var QueryFactory $queryFactory
   */
  private $queryFactory;

  /**
   * @var DownloadImagesQueue $downloadImagesQueueManager
   */
  private $downloadImagesQueueManager = NULL;

  /**
   * Construct
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The query factory
   * @param \Drupal\acq_sku\CategoryRepositoryInterface $cat_repo
   *   Category Repository instance
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, $serializer_formats, LoggerInterface $logger, ConfigFactoryInterface $config_factory, QueryFactory $query_factory, CategoryRepositoryInterface $cat_repo) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->queryFactory = $query_factory;
    $this->categoryRepo = $cat_repo;
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
      $container->get('acq_sku.category_repo')
    );
  }

  /**
   * post
   *
   * Handle Conductor posting an array of product / SKU data for update.
   *
   * @param array $products Product / SKU Data
   *
   * @return ResourceResponse $response
   */
  public function post(array $products = [])
  {
    $em = $this->entityManager->getStorage('acq_sku');
    $created = 0;
    $updated = 0;
    $failed = 0;

    foreach ($products as $product) {
      $display = NULL;

      if (!isset($product['type'])) {
        continue;
      }

      $query = $this->queryFactory->get('acq_sku_type')
        ->condition('id', $product['type'])
        ->count();

      $has_bundle = $query->execute();

      if (!$has_bundle) {
        continue;
      }

      if (!isset($product['sku']) || !strlen($product['sku'])) {
        $this->logger->error('Invalid or empty product SKU.');
        $failed++;
        continue;
      }

      $query = $this->queryFactory->get('acq_sku')
        ->condition('sku', $product['sku']);
      $nids = $query->execute();

      if (count($nids) > 1) {
        $this->logger->error(
          'Duplicate product SKU @sku found.',
          array('@sku' => $product['sku'])
        );
        $failed++;
        continue;
      }

      if (count($nids) > 0) {
        $nid = array_shift($nids);
        $sku = $em->load($nid);

        if (!$sku->id()) {
          $this->logger->error(
            'Loading product SKU @sku failed.',
            array('@sku' => $product['sku'])
          );
          $failed++;
          continue;
        }

        // Load associated product display node.
        $query = $this->queryFactory->get('node')
          ->condition('type', 'acq_product')
          ->condition('field_skus', $product['sku']);
        $nids = $query->execute();

        if (!count($nids)) {
          $this->logger->info(
            'Existing product SKU @sku has no display node, creating.',
            array('@sku' => $product['sku'])
          );

          $display = $this->createDisplayNode($product);
        } else {
          $this->updateNodeCategories($nids, $product);
        }

        $sku->name->value = $product['name'];
        $sku->price->value = $product['price'];
        $sku->attributes = $this->formatProductAttributes($product['attributes']);

        $this->logger->info(
          'Updating product SKU @sku.',
          array('@sku' => $product['sku'])
        );

        $updated++;
      } else {
        $sku = $em->create(array(
          'type'       => $product['type'],
          'sku'        => $product['sku'],
          'name'       => html_entity_decode($product['name']),
          'price'      => $product['price'],
          'attributes' => $this->formatProductAttributes($product['attributes']),
        ));

        $display = $this->createDisplayNode($product);

        $this->logger->info(
          'Creating product SKU @sku.',
          array('@sku' => $product['sku'])
        );

        $created++;
      }

      // Update the fields based on the values from attributes.
      $this->updateAttributeFields($sku, $product['attributes']);

      // Update upsell linked SKUs.
      $this->updateLinkedSkus('upsell', $sku, $product['linked']);

      // Update crosssell linked SKUs.
      $this->updateLinkedSkus('crosssell', $sku, $product['linked']);

      // Update related linked SKUs.
      $this->updateLinkedSkus('related', $sku, $product['linked']);

      $plugin_manager = \Drupal::service('plugin.manager.sku');
      $plugin_definition = $plugin_manager->pluginFromSKU($sku);

      if (!empty($plugin_definition)) {
        $class = $plugin_definition['class'];
        $plugin = new $class();
        $plugin->processImport($sku, $product);
      }

      $sku->save();

      // Update fields based on the values from attributes that require SKU id.
      $this->updateAttributeFieldsPostSave($sku, $product['attributes']);

      if ($display) {
        $display->save();
      }
    }

    $response = array(
      'success' => (bool) (($created > 0) || ($updated > 0)),
      'created' => $created,
      'updated' => $updated,
      'failed'  => $failed,
    );

    return(new ResourceResponse($response));
  }

  /**
   * createDisplayNode
   *
   * Create a product display node for a set of SKU entities.
   *
   * @param Array $product Product data
   *
   * @return EntityInterface $node
   */
  private function createDisplayNode(array $product)
  {
    $description = (isset($product['attributes']['description'])) ? $product['attributes']['description'] : '';

    $categories = (isset($product['categories'])) ? $product['categories'] : [];
    $categories = $this->formatCategories($categories);

    $node = $this->entityManager->getStorage('node')->create(array(
      'type'           => 'acq_product',
      'title'          => html_entity_decode($product['name']),
      'body'           => [
        'value' => $description,
        'format' => 'rich_text',
      ],
      'field_skus'     => array($product['sku']),
      'field_category' => $categories,
    ));

    $node->setPublished(FALSE);

    // Invoke the alter hook to allow all modules to update the node.
    \Drupal::moduleHandler()->alter('acq_sku_product_node', $node, $product);

    return($node);
  }

  /**
   * formatCategories
   *
   * @return array $terms
   */
  private function formatCategories(array $categories)
  {
    $terms = array();

    foreach ($categories as $cid) {
      $term = $this->categoryRepo->loadCategoryTerm($cid);
      if ($term) {
        $terms[] = $term->id();
      }
    }

    return($terms);
  }

  /**
   * formatProductAttributes
   *
   * Format the product attributes data as an array for saving in a
   * key value field.
   *
   * @param array $attributes
   *
   * @return array $formatted
   */
  private function formatProductAttributes(array $attributes)
  {
    $formatted = array();

    foreach ($attributes as $name => $value) {

      if (!strlen($value)) {
        continue;
      }

      $formatted[] = array(
        'key'   => $name,
        'value' => substr((string) $value, 0, 100),
      );
    }

    return($formatted);
  }

  /**
   * updateNodeCategories
   *
   * Update the assigned categories for display nodes (by ID).
   *
   * @param int[] $nids Node IDs
   * @param array $product Product Data
   *
   * @return void
   */
  private function updateNodeCategories(array $nids, array $product)
  {
    $categories = (isset($product['categories'])) ? $product['categories'] : [];
    $categories = $this->formatCategories($categories);

    foreach ($nids as $nid) {
      $node = $this->entityManager->getStorage('node')->load($nid);
      if ($node && $node->id()) {
        $node->field_category = $categories;

        // Invoke the alter hook to allow all modules to update the node.
        \Drupal::moduleHandler()->alter('acq_sku_product_node', $node, $product);

        $node->save();
      }
    }
  }

  /**
   * updateLinkedSkus
   *
   * Prepare the field value for linked type (upsell, crosssell, etc.).
   * Get the position based on the position coming from API.
   *
   * @param string $type
   * @param SKU $sku
   * @param array $linked
   *
   * @return void
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
   * updateAttributeFields
   *
   * Update the fields based on the values from attributes.
   *
   * @param SKU $sku
   * @param array $attributes
   */
  private function updateAttributeFields(SKU $sku, array $attributes) {
    $additionalFields = \Drupal::config('acq_sku.base_field_additions')->getRawData();

    // Loop through all the attributes available for this particular SKU.
    foreach ($attributes as $key => $value) {
      // Check if attribute is required by us.
      if (isset($additionalFields[$key])) {
        $field = $additionalFields[$key];

        $value = $field['cardinality'] != 1 ? explode(',', $value) : $value;
        $field_key = 'attr_' . $key;

        switch ($field['type']) {
          case 'string':
            $sku->{$field_key}->setValue($value);
            break;

          case 'image':
            // We will manage this post save.
            break;
        }
      }
    }
  }

  /**
   * updateAttributeFieldsPostSave
   *
   * Update the fields based on the values from attributes.
   * We need the SKU id for some cases which will be handled in this.
   *
   * @param SKU $sku
   * @param array $attributes
   */
  private function updateAttributeFieldsPostSave(SKU $sku, array $attributes) {
    $additionalFields = \Drupal::config('acq_sku.base_field_additions')->getRawData();

    // Loop through all the attributes available for this particular SKU.
    foreach ($attributes as $key => $value) {
      // Check if attribute is required by us.
      if (isset($additionalFields[$key])) {
        $field = $additionalFields[$key];

        $value = $field['cardinality'] != 1 ? explode(',', $value) : $value;
        $field_key = 'attr_' . $key;

        switch ($field['type']) {
          case 'string':
            // Already managed in pre-save.
            break;

          case 'image':
            // Initialise queue manager if not already done.
            if (empty($this->downloadImagesQueueManager)) {
              $this->downloadImagesQueueManager = \Drupal::service('acq_sku.download_images_queue');
            }

            // @TODO: Enhance the process by checking if the same image is
            // already there during update.
            foreach ($value as $index => $val) {
              $this->downloadImagesQueueManager->addItem($sku->id(), $field_key, $index, $val);
            }

            break;
        }
      }
    }
  }

}
