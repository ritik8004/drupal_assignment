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
 * Class CartLinkedSkusController.
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
      // If type or sku not available or not of crosssell type.
      if (empty($queryParams['type'])
        || empty($queryParams['skus'])
        || $queryParams['type'] != AcqSkuLinkedSku::LINKED_SKU_TYPE_CROSSSELL) {
        $response = new CacheableJsonResponse([]);
        $response->addCacheableDependency(CacheableMetadata::createFromRenderArray(['#cache' => $cache_array]));
        return $response;
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

      $cache_array['tags'] = $cache_tags;
      $response = new CacheableJsonResponse($result);
      $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
        '#cache' => $cache_array,
      ]));

      return $response;
    }
    catch (\Exception $e) {
      return new ModifiedResourceResponse(['error' => TRUE]);
    }
  }

}
