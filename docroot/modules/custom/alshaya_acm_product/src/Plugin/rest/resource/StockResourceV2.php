<?php

namespace Drupal\alshaya_acm_product\Plugin\rest\resource;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a resource to get stock details.
 *
 * @RestResource(
 *   id = "stock_v2",
 *   label = @Translation("Stock V2"),
 *   uri_paths = {
 *     "canonical" = "/rest/v2/stock/{sku}"
 *   }
 * )
 */
class StockResourceV2 extends StockResource {

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

    $response = new ResourceResponse($data);

    // Add sku cache tags to response.
    $cacheableMetadata = $response->getCacheableMetadata();
    $cacheableMetadata->addCacheContexts($skuEntity->getCacheContexts());
    $cacheableMetadata->addCacheTags(array_merge($skuEntity->getCacheTags(), [parent::CACHE_PREFIX . $skuEntity->id()]));
    $response->addCacheableDependency($cacheableMetadata);

    return $response;
  }

}
