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

/**
 * Class Cart Linked Skus Controller.
 */
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

  /**
   * CartLinkedSkusController constructor.
   *
   * @param \Drupal\alshaya_acm_product\Service\SkuInfoHelper $sku_info_helper
   *   Sku info helper.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   SKU manager service.
   */
  public function __construct(SkuInfoHelper $sku_info_helper,
                              RequestStack $request_stack,
                              SkuManager $skuManager) {
    $this->skuInfoHelper = $sku_info_helper;
    $this->request = $request_stack->getCurrentRequest();
    $this->skuManager = $skuManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm_product.sku_info'),
      $container->get('request_stack'),
      $container->get('alshaya_acm_product.skumanager')
    );
  }

  /**
   * Get linked crosssell skus of given skus.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   Response object.
   */
  public function getLinkedSkusForCart(Request $request) {
    try {
      $cache_array = [
        'contexts' => ['url.query_args', 'languages'],
      ];
      $queryParams = $request->query->all();
      // Filter out duplicate skus from query param.
      $querySkus = !empty($queryParams['skus'])
        ? array_filter($queryParams['skus'])
        : $queryParams['skus'];
      // If type or sku not available or not of crosssell type.
      if (empty($queryParams['type'])
        || empty($querySkus)
        || $queryParams['type'] != AcqSkuLinkedSku::LINKED_SKU_TYPE_CROSSSELL
      ) {
        $response = new CacheableJsonResponse([]);
        $response->addCacheableDependency(CacheableMetadata::createFromRenderArray(['#cache' => $cache_array]));
        return $response;
      }

      $result = [];
      $cache_tags = [];
      $cross_sell_skus = [];
      $parent_skus = [];

      foreach ($querySkus as $sku) {
        if ($sku_entity = SKU::loadFromSku($sku)) {
          $cache_tags = array_merge($cache_tags, $sku_entity->getCacheTags());
          $cross_sell_skus += $this->skuManager->getLinkedSkus($sku_entity, $queryParams['type']);
          if ($sku_entity->bundle() === 'simple') {
            $parent_sku = $this->skuManager->getParentSkuBySku($sku_entity);
            if ($parent_sku instanceof SKUInterface) {
              $parent_skus[] = $parent_sku->getSku();
              $cache_tags = array_merge($cache_tags, $parent_sku->getCacheTags());
              $cross_sell_skus += $this->skuManager->getLinkedSkus($parent_sku, $queryParams['type']);
            }
          }
        }
      }

      // Filter out / Remove skus from cross sell which are already added in
      // cart (Which we receive in query string).
      $cross_sell_skus = array_diff($cross_sell_skus, array_merge($querySkus, $parent_skus));
      $linkedSkus = $this->skuManager->filterRelatedSkus(array_unique($cross_sell_skus));

      foreach (array_keys($linkedSkus) as $linkedSku) {
        $linkedSkuEntity = SKU::loadFromSku($linkedSku);
        if ($linkedSkuEntity instanceof SKUInterface
          && $lightProduct = $this->skuInfoHelper->getLightProduct($linkedSkuEntity)
        ) {
          $result['data'][$lightProduct['sku']] = $lightProduct;
          $cache_tags = array_merge(
            $cache_tags,
            $linkedSkuEntity->getCacheTags(),
            ['node:' . $lightProduct['nid']]
          );
        }
      }

      $cache_array['tags'] = $cache_tags;
      $response = new CacheableJsonResponse($result);
      $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
        '#cache' => $cache_array,
      ]));

      return $response;
    }
    catch (\Exception) {
      return new ModifiedResourceResponse(['error' => TRUE]);
    }
  }

}
