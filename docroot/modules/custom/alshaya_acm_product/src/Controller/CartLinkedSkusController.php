<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\acq_sku\AcqSkuLinkedSku;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_commerce\SKUInterface;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CartLinkedSkusController extends ControllerBase {

  /**
   * Sku info helper.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuInfoHelper
   */
  protected $skuInfoHelper;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * SKU manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  public function __construct(SkuInfoHelper $sku_info_helper,
                              RequestStack $request_stack,
                              SkuManager $skuManager) {
    $this->skuInfoHelper = $sku_info_helper;
    $this->request = $request_stack->getCurrentRequest();
    $this->skuManager = $skuManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm_product.sku_info'),
      $container->get('request_stack'),
      $container->get('alshaya_acm_product.skumanager')
    );
  }

  public function getLinkedSkusForCart(Request $request) {
    try {
      $queryParams = $request->query->all();
      // If type or sku not available or not of crosssell type.
      if (empty($queryParams['type'])
        || empty($queryParams['skus'])
        || $queryParams['type'] != AcqSkuLinkedSku::LINKED_SKU_TYPE_CROSSSELL) {
        return new ModifiedResourceResponse([]);
      }

      $result = [];
      $cache_tags = [];
      foreach ($queryParams['skus'] as $sku) {
        $skuEntity = SKU::loadFromSku($sku);
        if (!$skuEntity instanceof SKUInterface) {
          continue;
        }

        $cache_tags[] = 'sku:' . $skuEntity->id();
        $linkedSkus = $this->skuInfoHelper->getLinkedSkus($skuEntity, $queryParams['type']);
        foreach (array_keys($linkedSkus) as $linkedSku) {
          $linkedSkuEntity = SKU::loadFromSku($linkedSku);
          if ($linkedSkuEntity instanceof SKUInterface
            && $lightProduct = $this->skuInfoHelper->getLightProduct($linkedSkuEntity)) {
            $cache_tags[] = 'sku:' . $linkedSkuEntity->id();
            $cache_tags[] = 'node:' . $lightProduct['nid'];
            $result['data'][$lightProduct['sku']] = $lightProduct;
          }
        }
      }

      $response = new CacheableJsonResponse($result);
      $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
        '#cache' => [
          'tags' => $cache_tags,
          'contexts' => ['url.query_args', 'languages'],
        ],
      ]));

      return $response;
    }
    catch (\Exception $e) {
      return new ModifiedResourceResponse(['error' => TRUE]);
    }
  }

}
