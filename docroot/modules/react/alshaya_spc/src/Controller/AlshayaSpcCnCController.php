<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\alshaya_click_collect\Service\AlshayaClickCollect;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Alshaya Spc CnC Controller.
 */
class AlshayaSpcCnCController extends ControllerBase {

  /**
   * Alshaya click and collect helper.
   *
   * @var \Drupal\alshaya_click_collect\Service\AlshayaClickCollect
   */
  protected $clickCollect;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * AlshayaSpcCnCController constructor.
   *
   * @param \Drupal\alshaya_click_collect\Service\AlshayaClickCollect $click_collect
   *   Alshaya click and collect helper.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   */
  public function __construct(AlshayaClickCollect $click_collect,
                              ModuleHandlerInterface $module_handler) {
    $this->clickCollect = $click_collect;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_click_collect.helper'),
      $container->get('module_handler')
    );
  }

  /**
   * Get the click n collect stores for given cart and lat/long.
   *
   * @param int $cart_id
   *   The cart id.
   * @param float $lat
   *   The latitude.
   * @param float $lon
   *   The longitude.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getCncStoresJson($cart_id, $lat = NULL, $lon = NULL) {
    $stores = $this->clickCollect->getCartStores($cart_id, $lat, $lon);
    return new JsonResponse($stores);
  }

  /**
   * Get store info for given code.
   *
   * @param string $store_code
   *   The store code.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getStoreInfoJson(string $store_code) {
    $stores = $this->clickCollect->getStoreInfo($store_code);
    return new JsonResponse(!empty($stores) ? reset($stores) : []);
  }

}
