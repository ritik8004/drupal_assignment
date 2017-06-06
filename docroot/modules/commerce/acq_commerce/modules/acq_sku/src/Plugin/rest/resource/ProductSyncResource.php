<?php

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, array $serializer_formats, LoggerInterface $logger, ConfigFactoryInterface $config_factory, QueryFactory $query_factory, CategoryRepositoryInterface $cat_repo) {
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
   * Post.
   *
   * Handle Conductor posting an array of product / SKU data for update.
   *
   * @param array $products
   *   Product / SKU Data.
   *
   * @return \Drupal\rest\ResourceResponse
   *   HTTP Response object.
   */
  public function post(array $products = []) {

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

      // Don't import configurable SKU if it has no configurable options.
      if ($product['type'] == 'configurable' && empty($product['extension']['configurable_product_options'])) {
        $this->logger->error('Empty configurable options for SKU: @sku', ['@sku' => $product['sku']]);
        $failed++;
        continue;
      }

      $query = $this->queryFactory->get('acq_sku')
        ->condition('sku', $product['sku']);
      $sku_ids = $query->execute();

      if (count($sku_ids) > 1) {
        $this->logger->error(
          'Duplicate product SKU @sku found.',
          ['@sku' => $product['sku']]
        );
        $failed++;
        continue;
      }

      if (count($sku_ids) > 0) {
        $sku_id = array_shift($sku_ids);
        $sku = $em->load($sku_id);

        if (!$sku->id()) {
          $this->logger->error(
            'Loading product SKU @sku failed.',
            ['@sku' => $product['sku']]
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
          if ($product['visibility'] == 1) {
            $this->logger->info(
              'Existing product SKU @sku has no display node, creating.',
              ['@sku' => $product['sku']]
            );

            $display = $this->createDisplayNode($product);
          }
        }
        else {
          $this->updateNodeCategories($nids, $product);
        }

        $sku->name->value = $product['name'];
        $sku->price->value = $product['price'];
        $sku->special_price->value = $product['special_price'];
        $sku->final_price->value = $product['final_price'];

        $sku->attributes = $this->formatProductAttributes($product['attributes']);

        $sku->media = serialize($product['extension']['media']);

        $this->logger->info(
          'Updating product SKU @sku.',
          ['@sku' => $product['sku']]
        );

        $updated++;
      }
      else {
        $sku = $em->create([
          'type' => $product['type'],
          'sku' => $product['sku'],
          'name' => html_entity_decode($product['name']),
          'price' => $product['price'],
          'special_price' => $product['special_price'],
          'final_price' => $product['final_price'],
          'attributes' => $this->formatProductAttributes($product['attributes']),
          'media' => serialize($product['extension']['media']),
        ]);

        if ($product['visibility'] == 1) {
          $display = $this->createDisplayNode($product);
        }

        $this->logger->info(
          'Creating product SKU @sku.',
          ['@sku' => $product['sku']]
        );

        $created++;
      }

      // Update the fields based on the values from attributes.
      $this->updateAttributeFields($sku, $product['attributes']);

      // Update the fields based on the values from extension.
      $this->updateExtensionFields($sku, $product['extension']);

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

      if ($display) {
        $display->save();
      }
    }

    $response = [
      'success' => (bool) (($created > 0) || ($updated > 0)),
      'created' => $created,
      'updated' => $updated,
      'failed' => $failed,
    ];

    return (new ResourceResponse($response));
  }

  /**
   * CreateDisplayNode.
   *
   * Create a product display node for a set of SKU entities.
   *
   * @param array $product
   *   Product data.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Node object.
   */
  private function createDisplayNode(array $product) {

    $description = (isset($product['attributes']['description'])) ? $product['attributes']['description'] : '';

    $categories = (isset($product['categories'])) ? $product['categories'] : [];
    $categories = $this->formatCategories($categories);

    $node = $this->entityManager->getStorage('node')->create([
      'type' => 'acq_product',
      'title' => html_entity_decode($product['name']),
      'body' => [
        'value' => $description,
        'format' => 'rich_text',
      ],
      'field_skus' => [$product['sku']],
      'field_category' => $categories,
    ]);

    $node->setPublished(FALSE);

    // Invoke the alter hook to allow all modules to update the node.
    \Drupal::moduleHandler()->alter('acq_sku_product_node', $node, $product);

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

      if (!strlen($value)) {
        continue;
      }

      $formatted[] = [
        'key' => $name,
        'value' => substr((string) $value, 0, 100),
      ];
    }

    return ($formatted);
  }

  /**
   * UpdateNodeCategories.
   *
   * Update the assigned categories for display nodes (by ID).
   *
   * @param int[] $nids
   *   Node IDs.
   * @param array $product
   *   Product Data.
   */
  private function updateNodeCategories(array $nids, array $product) {

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
   * @param Drupal\acq_sku\Entity\SKU $sku
   *   The root SKU.
   * @param array $attributes
   *   The attributes to set.
   */
  private function updateAttributeFields(SKU $sku, array $attributes) {
    $additionalFields = \Drupal::config('acq_sku.base_field_additions')->getRawData();

    // Loop through all the attributes available for this particular SKU.
    foreach ($attributes as $key => $value) {
      // Check if attribute is required by us.
      if (isset($additionalFields[$key])) {
        $field = $additionalFields[$key];

        if ($field['parent'] != 'attributes') {
          continue;
        }

        $value = $field['cardinality'] != 1 ? explode(',', $value) : $value;
        $field_key = 'attr_' . $key;

        switch ($field['type']) {
          case 'string':
            $sku->{$field_key}->setValue($value);
            break;

          case 'text_long':
            $value = isset($field['serialize']) ? serialize($value) : $value;
            $sku->{$field_key}->setValue($value);
            break;
        }
      }
    }
  }

  /**
   * Update extension fields.
   *
   * Update the fields based on the values from extension.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   The root SKU.
   * @param array $attributes
   *   The attributes to set.
   */
  private function updateExtensionFields(SKU $sku, array $attributes) {
    $additionalFields = \Drupal::config('acq_sku.base_field_additions')->getRawData();

    // Loop through all the attributes available for this particular SKU.
    foreach ($attributes as $key => $value) {
      // Check if attribute is required by us.
      if (isset($additionalFields[$key])) {
        $field = $additionalFields[$key];

        if ($field['parent'] != 'extension') {
          continue;
        }

        $field_key = 'attr_' . $key;

        switch ($field['type']) {
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
  }

}
