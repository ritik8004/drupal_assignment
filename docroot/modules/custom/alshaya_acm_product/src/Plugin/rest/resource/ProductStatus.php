<?php

namespace Drupal\alshaya_acm_product\Plugin\rest\resource;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a resource to get stock details.
 *
 * @RestResource(
 *   id = "product_status",
 *   label = @Translation("Product Status"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/product-status/{sku}"
 *   }
 * )
 */
class ProductStatus extends ResourceBase {

  /**
   * Sku info helper.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuInfoHelper
   */
  protected $skuInfoHelper;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    SkuInfoHelper $sku_info_helper,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->skuInfoHelper = $sku_info_helper;
    $this->moduleHandler = $module_handler;
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
      $container->get('logger.factory')->get('alshaya_acm_product'),
      $container->get('alshaya_acm_product.sku_info'),
      $container->get('module_handler')
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
    $sku = base64_decode($sku);
    $skuEntity = SKU::loadFromSku($sku);

    if (!($skuEntity instanceof SKUInterface)) {
      throw (new NotFoundHttpException());
    }

    $stockInfo = $this->skuInfoHelper->stockInfo($skuEntity);
    $data['stock'] = $stockInfo['stock'];
    $data['in_stock'] = $stockInfo['in_stock'];
    $data['max_sale_qty'] = $stockInfo['max_sale_qty'];
    $this->moduleHandler->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
    $data['cnc_enabled'] = alshaya_acm_product_available_click_collect($sku);
    $response = new ResourceResponse($data);

    $cacheableMetadata = $response->getCacheableMetadata();
    $cacheableMetadata->addCacheContexts(['url']);
    $cacheableMetadata->addCacheTags(Cache::mergeTags(
      $skuEntity->getCacheTags(),
      [
        StockResource::CACHE_PREFIX . $skuEntity->id(),
        'config:alshaya_click_collect.settings',
      ]
    ));
    $response->addCacheableDependency($cacheableMetadata);
    return $response;
  }

}
