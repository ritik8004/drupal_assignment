<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\alshaya_spc\Helper\AlshayaSpcStockHelper;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class AlshayaSpcCheckoutEventController.
 */
class AlshayaSpcCheckoutEventController extends ControllerBase {

  /**
   * Orders manager.
   *
   * @var \Drupal\alshaya_acm_customer\OrdersManager
   */
  protected $ordersManager;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Current User service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * SPC stock helper.
   *
   * @var \Drupal\alshaya_spc\Helper\AlshayaSpcStockHelper
   */
  protected $spcStockHelper;

  /**
   * AlshayaSpcOrderController constructor.
   *
   * @param \Drupal\alshaya_acm_customer\OrdersManager $orders_manager
   *   Orders Manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current User service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\alshaya_spc\Helper\AlshayaSpcStockHelper $spc_stock_helper
   *   SPC stock helper.
   */
  public function __construct(
    OrdersManager $orders_manager,
    LoggerChannelFactoryInterface $logger_factory,
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager,
    AlshayaSpcStockHelper $spc_stock_helper
  ) {
    $this->ordersManager = $orders_manager;
    $this->logger = $logger_factory->get('alshaya_acm_checkout');
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->spcStockHelper = $spc_stock_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm_customer.orders_manager'),
      $container->get('logger.factory'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('alshaya_spc.stock_helper')
    );
  }

  /**
   * Set Order id in drupal session.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function postCheckoutEvent(Request $request) {
    $action = $request->request->get('action');
    $cart = $request->request->get('cart');
    if (empty($action) || empty($cart)) {
      throw new BadRequestHttpException($this->t('Missing required parameters'));
    }

    $response = [
      'status' => FALSE,
    ];

    switch ($action) {
      case 'place order success':
        $order_id = $request->request->get('order_id');
        $cart = $request->request->get('cart');
        $payment_method = $request->get('payment_method');
        if (empty($order_id)) {
          throw new BadRequestHttpException($this->t('Missing required parameters'));
        }

        // Add success message in logs.
        $this->logger->info('Placed order. Cart id: @cart_id. Order id: @order_id. Payment method: @method', [
          '@cart_id' => $cart['id'],
          '@order_id' => $order_id,
          '@method' => $payment_method,
        ]);

        // Refresh stock for products in cart.
        $this->spcStockHelper->refreshStockForProductsInCart($cart);

        $account = $this->alshayaGetCustomerFromSession();
        if ($account) {
          if (empty($account->get('field_mobile_number')->getString())) {
            $account->get('field_mobile_number')->setValue($cart['billing_address']['telephone']);
            $account->save();
          }

          $customer_id = (int) $account->get('acq_customer_id')->getString();
          $this->ordersManager->clearOrderCache($customer_id, $account->id());
        }

        // While debugging we log the whole cart object.
        $this->logger->debug('Placed order for cart: @cart', [
          '@cart' => json_encode($cart),
        ]);
        $response['status'] = TRUE;

        break;

      case 'validate cart':
        try {
          $data = $this->spcStockHelper->refreshStockForProductsInCart($cart);
          $response = [
            'status' => TRUE,
            'data' => $data,
          ];
        }
        catch (\Exception $e) {
          // Do nothing.
        }
        break;
    }

    return new JsonResponse($response);
  }

  /**
   * Helper function to check if user is valid customer.
   *
   * @return bool|\Drupal\user\UserInterface
   *   User if user is customer, false otherwise.
   */
  protected function alshayaGetCustomerFromSession() {
    if ($this->currentUser->isAnonymous()) {
      return FALSE;
    }

    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    if (empty($user->get('acq_customer_id')->getString())) {
      return FALSE;
    }

    return $user;
  }

}
