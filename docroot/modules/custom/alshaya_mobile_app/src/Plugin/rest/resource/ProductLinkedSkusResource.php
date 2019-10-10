<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\AcqSkuLinkedSku;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get product details with linked skus.
 *
 * @RestResource(
 *   id = "product_linked_skus",
 *   label = @Translation("Product Linked Skus"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/product/{sku}/linked"
 *   }
 * )
 */
class ProductLinkedSkusResource extends ResourceBase {

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  private $mobileAppUtility;

  /**
   * ProductResource constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param array $serializer_formats
   *   Serializer formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    MobileAppUtility $mobile_app_utility
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->mobileAppUtility = $mobile_app_utility;
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
      $container->get('alshaya_mobile_app.utility')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns linked skus of the given sku.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing linked skus of the given sku.
   */
  public function get(string $sku) {
    $skuEntity = SKU::loadFromSku($sku);

    if (!$skuEntity instanceof SKUInterface) {
      $this->mobileAppUtility->throwException();
    }

    $data = [];
    foreach (AcqSkuLinkedSku::LINKED_SKU_TYPES as $linked_type) {
      $data['linked'][] = [
        'link_type' => $linked_type,
        'skus' => $this->mobileAppUtility->getLinkedSkus($skuEntity, $linked_type),
      ];
    }

    $response = new ResourceResponse($data);

    $cacheableMetadata = $response->getCacheableMetadata();
    $cacheableMetadata->addCacheTags($skuEntity->getCacheTags());
    $cacheableMetadata->addCacheContexts($skuEntity->getCacheContexts());
    $response->addCacheableDependency($cacheableMetadata);

    return $response;
  }

}
