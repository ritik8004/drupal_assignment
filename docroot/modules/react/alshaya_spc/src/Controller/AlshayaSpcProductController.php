<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\alshaya_spc\Helper\AlshayaSpcStockHelper;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for Alshaya Single Page Checkout Product routes.
 */
class AlshayaSpcProductController extends ControllerBase {

  /**
   * Stock helper service.
   *
   * @var \Drupal\alshaya_spc\Helper\AlshayaSpcStockHelper
   */
  protected $stockHelper;

  /**
   * The controller constructor.
   *
   * @param \Drupal\alshaya_spc\Helper\AlshayaSpcStockHelper $stock_helper
   *   The stock helper service.
   */
  public function __construct(
    AlshayaSpcStockHelper $stock_helper
  ) {
    $this->stockHelper = $stock_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_spc.stock_helper')
    );
  }

  /**
   * Refreshes stock for skus.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response.
   */
  public function stockRefresh(Request $request) {
    $skus = $request->get('skus');
    $response['status'] = FALSE;

    if (!empty($skus)) {
      $stock = $this->stockHelper->refreshStockForSkus($skus);

      if (!empty($stock)) {
        $response = [
          'status' => TRUE,
          'data' => $stock,
        ];
      }
    }

    return new JsonResponse($response);
  }

}
