<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a resource to get stock details.
 *
 * @RestResource(
 *   id = "stock",
 *   label = @Translation("Stock"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/stock/{sku}"
 *   }
 * )
 */
class StockResource extends ResourceBase {

  /**
   * Sku info helper.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuInfoHelper
   */
  protected $skuInfoHelper;

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
   * @param \Drupal\alshaya_acm_product\Service\SkuInfoHelper $sku_info_helper
   *   Sku info helper object.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    SkuInfoHelper $sku_info_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->skuInfoHelper = $sku_info_helper;
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
      $container->get('alshaya_acm_product.sku_info')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns stock info for sku.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing stock data.
   */
  public function get(string $sku) {
    $skuEntity = SKU::loadFromSku($sku);

    if (!($skuEntity instanceof SKUInterface)) {
      throw (new NotFoundHttpException());
    }

    $stockInfo = $this->skuInfoHelper->stockInfo($skuEntity);
    $data['stock'] = $stockInfo['stock'];
    $data['in_stock'] = $stockInfo['in_stock'];
    $data['max_sale_qty'] = $stockInfo['max_sale_qty'];

    $response = new ResourceResponse($data);

    // Add sku cache tags to response.
    $cacheableMetadata = $response->getCacheableMetadata();
    $cacheableMetadata->addCacheContexts($skuEntity->getCacheContexts());
    $cacheableMetadata->addCacheTags($skuEntity->getCacheTags());
    $response->addCacheableDependency($cacheableMetadata);

    return $response;
  }

}
