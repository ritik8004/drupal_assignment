<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\alshaya_click_collect\Service\AlshayaClickCollect;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AlshayaSpcCnCController.
 */
class AlshayaSpcCnCController extends ControllerBase {

  /**
   * Alshaya click and collect helper.
   *
   * @var \Drupal\alshaya_click_collect\Service\AlshayaClickCollect
   */
  protected $clickCollect;

  /**
   * AlshayaSpcCnCController constructor.
   *
   * @param \Drupal\alshaya_click_collect\Service\AlshayaClickCollect $click_collect
   *   Alshaya click and collect helper.
   */
  public function __construct(AlshayaClickCollect $click_collect) {
    $this->clickCollect = $click_collect;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_click_collect.helper')
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

}
