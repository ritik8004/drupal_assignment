<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\alshaya_spc\Helper\AlshayaSpcStockHelper;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AlshayaSpcStockController.
 *
 * @package Drupal\alshaya_spc\Controller
 */
class AlshayaSpcStockController extends ControllerBase {

  /**
   * SPC stock helper.
   *
   * @var \Drupal\alshaya_spc\Helper\AlshayaSpcStockHelper
   */
  protected $spcStockHelper;

  /**
   * AlshayaSpcStockController constructor.
   *
   * @param \Drupal\alshaya_spc\Helper\AlshayaSpcStockHelper $spc_stock_helper
   *   SPC stock helper.
   */
  public function __construct(AlshayaSpcStockHelper $spc_stock_helper) {
    $this->spcStockHelper = $spc_stock_helper;
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
   * Refresh the stock from MDC to drupal.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function refreshStock(Request $request) {
    $data = $request->request->get('data');
    if (empty($data)) {
      throw new BadRequestHttpException($this->t('Missing required parameters'));
    }

    // @TODO: Check cacheability.
    $json_response = new JsonResponse(['status' => TRUE]);
    $json_response->setMaxAge(0);
    return $json_response;
  }

}
