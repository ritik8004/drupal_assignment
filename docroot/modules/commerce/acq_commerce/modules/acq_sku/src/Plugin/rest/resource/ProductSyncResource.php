<?php

namespace Drupal\acq_sku\Plugin\rest\resource;

use Drupal\acq_sku\CategoryRepositoryInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductOptionsManager;
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, array $serializer_formats, LoggerInterface $logger, ConfigFactoryInterface $config_factory, QueryFactory $query_factory, CategoryRepositoryInterface $cat_repo, ProductOptionsManager $product_options_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->queryFactory = $query_factory;
    $this->categoryRepo = $cat_repo;
    $this->productOptionsManager = $product_options_manager;
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
      $container->get('acq_sku.product_options_manager')
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
      $langcode = acq_commerce_get_langcode_from_store_id($product['store_id']);

      if (!isset($product['type'])) {
        continue;
      }

      $query = $this->queryFactory->get('acq_sku_type');
      $query->condition('id', $product['type']);
      $query->count();

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

      if ($sku = SKU::loadFromSku($product['sku'], $langcode, FALSE)) {
        $this->logger->info('Updating product SKU @sku.', ['@sku' => $product['sku']]);
        $updated++;
      }
      else {
        /** @var \Drupal\acq_sku\Entity\SKU $sku */
        $sku = $em->create([
          'type' => $product['type'],
          'sku' => $product['sku'],
        ]);

        if ($langcode) {
          $sku->langcode = $langcode;
        }

        $this->logger->info('Creating product SKU @sku.', ['@sku' => $product['sku']]);

        $created++;
      }

      $sku->name->value = html_entity_decode($product['name']);
      $sku->price->value = $product['price'];
      $sku->special_price->value = $product['special_price'];
      $sku->final_price->value = $product['final_price'];
      $sku->attributes = $this->formatProductAttributes($product['attributes']);
      $sku->media = serialize($product['extension']['media']);

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

      $sku->save();

      if ($product['visibility'] == 1) {
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
        if ($node = $plugin->getDisplayNode($sku, FALSE, FALSE)) {
          $node->setPublished(FALSE);
          $node->save;
        }

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
    $additionalFields = \Drupal::config('acq_sku.base_field_additions')->getRawData();

    // Loop through all the attributes available for this particular SKU.
    foreach ($values as $key => $value) {
      // Check if attribute is required by us.
      if (isset($additionalFields[$key])) {
        $field = $additionalFields[$key];

        if ($field['parent'] != $parent) {
          continue;
        }

        $field_key = 'attr_' . $key;

        switch ($field['type']) {
          case 'attribute':
            $value = $field['cardinality'] != 1 ? explode(',', $value) : [$value];
            foreach ($value as $index => $val) {
              if ($term = $this->productOptionsManager->loadProductOptionByOptionId($key, $val)) {
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
  }

}
