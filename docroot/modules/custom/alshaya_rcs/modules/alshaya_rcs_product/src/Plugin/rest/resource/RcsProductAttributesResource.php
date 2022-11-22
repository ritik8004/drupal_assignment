<?php

namespace Drupal\alshaya_rcs_product\Plugin\rest\resource;

use Drupal\alshaya_rcs_product\Services\AlshayaRcsProductAttributesHelper;
use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get list of all product attributes.
 *
 * @RestResource(
 *   id = "rcsproductoptions",
 *   label = @Translation("Returns list all rcs product attributes options."),
 *   uri_paths = {
 *     "canonical" = "/rcs/product-attribute-options"
 *   }
 * )
 */
class RcsProductAttributesResource extends ResourceBase {

  /**
   * Product Attributes Helper service.
   *
   * @var \Drupal\alshaya_rcs_product\Services\AlshayaRcsProductAttributesHelper
   */
  protected $productAttributesHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              AlshayaRcsProductAttributesHelper $product_attributes_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->productAttributesHelper = $product_attributes_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('alshaya_mobile_app'),
      $container->get('alshaya_rcs_product.product_attributes_helper'),
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing list of categories.
   */
  public function get() {
    $response_data = $this->productAttributesHelper->getProductAttributesOptions();

    $response = new ResourceResponse($response_data);

    $cacheableMetadata = $response->getCacheableMetadata();
    $cacheableMetadata->addCacheTags(['taxonomy_term:sku_product_option']);
    $response->addCacheableDependency($cacheableMetadata);

    return $response;
  }

}
