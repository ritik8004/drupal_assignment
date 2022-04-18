<?php

namespace Drupal\alshaya_acm_product_category\Plugin\rest\resource;

use Drupal\alshaya_acm_product_category\Service\ProductCategoryMappingManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Product Category Mapping Sync Resource.
 *
 * @package Drupal\alshaya_acm_product_category\Plugin
 *
 * @ingroup alshaya_acm_product_category
 *
 * @RestResource(
 *   id = "alshaya_acm_product_category_mapping",
 *   label = @Translation("Alshaya Product Category Mapping Update"),
 *   uri_paths = {
 *     "create" = "/rest/v1/product-category-mapping"
 *   }
 * )
 */
class ProductCategoryMappingSyncResource extends ResourceBase {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Product Category Mapping Manager.
   *
   * @var \Drupal\alshaya_acm_product_category\Service\ProductCategoryMappingManager
   */
  protected $mappingManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('ProductCategoryMappingSyncResource'),
      $container->get('config.factory'),
      $container->get('alshaya_acm_product_category.mapping_manager')
    );
  }

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\alshaya_acm_product_category\Service\ProductCategoryMappingManager $mapping_manager
   *   Product Category Mapping Manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              ConfigFactoryInterface $config_factory,
                              ProductCategoryMappingManager $mapping_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->configFactory = $config_factory;
    $this->mappingManager = $mapping_manager;
  }

  /**
   * Post.
   *
   * Handle Conductor posting an array of category data for update.
   *
   * @param array $data
   *   Array containing product and category mappings.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   HTTP Response.
   */
  public function post(array $data) {
    $cats_to_ignore = $this->configFactory->get('alshaya_api.settings')->get('ignored_mdc_cats_on_sanity_check');
    $skip_cats = empty(trim($cats_to_ignore))
      ? []
      : explode(',', $cats_to_ignore);

    foreach ($data as $row) {
      if (empty($row['sku'])) {
        $this->logger->warning('Invalid data received for a row @data', [
          '@data' => json_encode($row),
        ]);

        continue;
      }

      $product_categories = $row['categories'] ?? [];
      if ($skip_cats) {
        $product_categories = array_diff($product_categories, $skip_cats);
      }

      $this->mappingManager->mapCategoriesToProduct($row['sku'], $product_categories);
    }

    return (new ModifiedResourceResponse(['success' => TRUE]));
  }

}
