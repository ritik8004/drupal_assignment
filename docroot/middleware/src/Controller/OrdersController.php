<?php

namespace App\Controller;

use App\Service\Drupal\Drupal;
use App\Service\Magento\MagentoInfo;
use App\Service\Orders;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class OrdersController.
 */
class OrdersController {

  /**
   * The last order id storage key.
   */
  const SESSION_STORAGE_KEY = 'last_order';

  /**
   * RequestStack Object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Service for magento info.
   *
   * @var \App\Service\Magento\MagentoInfo
   */
  protected $magentoInfo;

  /**
   * Drupal service.
   *
   * @var \App\Service\Drupal\Drupal
   */
  protected $drupal;

  /**
   * Service for cart interaction.
   *
   * @var \App\Service\Cart
   */
  protected $cart;

  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Service for session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * Current cart session info.
   *
   * @var array
   */
  protected $sessionCartInfo = [];

  /**
   * OrdersController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   RequestStack Object.
   * @param \App\Service\Orders $orders_service
   *   Orders service.
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   * @param \App\Service\Magento\MagentoInfo $magento_info
   *   Magento info service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   Service for session.
   */
  public function __construct(RequestStack $request, Orders $orders_service, Drupal $drupal, MagentoInfo $magento_info, LoggerInterface $logger, SessionInterface $session) {
    $this->request = $request->getCurrentRequest();
    $this->orderService = $orders_service;
    $this->drupal = $drupal;
    $this->magentoInfo = $magento_info;
    $this->logger = $logger;
    $this->session = $session;
  }

  /**
   * Get order data.
   *
   * @param int|string $order_id
   *   Use "last" for last order from session, order id for specific order.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Order response.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getOrder($order_id) {
    if ($order_id === 'last') {
      $order_id = (int) $this->session->get(self::SESSION_STORAGE_KEY);
    }

    if (!is_int($order_id)) {
      throw new NotFoundHttpException();
    }

    $data = $this->orderService->getOrder($order_id);

    // If there is any exception/error, return as is with exception message
    // without processing further.
    if (!empty($data['error'])) {
      $this->logger->error('Error while getting cart:{cart_id} Error:{error}', [
        'order_id' => $order_id,
        'error' => json_encode($data),
      ]);
    }

    return new JsonResponse($data);
  }

}
