<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\acq_sku\Entity\SKU;
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
   */
  public function __construct(
    OrdersManager $orders_manager,
    LoggerChannelFactoryInterface $logger_factory,
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->ordersManager = $orders_manager;
    $this->logger = $logger_factory->get('alshaya_acm_checkout');
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm_customer.orders_manager'),
      $container->get('logger.factory'),
      $container->get('current_user'),
      $container->get('entity_type.manager')
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
    if (empty($action)) {
      throw new BadRequestHttpException($this->t('Missing required parameters'));
    }

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
        $this->refreshStockForProductsInCart($cart);

        $account = $this->alshayaGetCustomerFromSession();
        if ($account) {
          if (empty($account->get('field_mobile_number')->getString())) {
            $account->get('field_mobile_number')->setValue($cart['billing_address']['telephone']);
            $account->save();
          }

          $this->ordersManager->clearOrderCache($account->getEmail(), $account->id());
        }

        // While debugging we log the whole cart object.
        $this->logger->debug('Placed order for cart: @cart', [
          '@cart' => json_encode($cart),
        ]);

        break;
    }

    return new JsonResponse(['success' => TRUE]);
  }

  /**
   * Refresh stock cache and Drupal cache of products in cart.
   */
  protected function refreshStockForProductsInCart($cart = NULL) {
    $processed_parents = [];

    // Still if empty, simply return.
    if (empty($cart)) {
      return;
    }

    foreach ($cart['items'] ?? [] as $item) {
      if ($sku_entity = SKU::loadFromSku($item['sku'])) {
        /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
        $plugin = $sku_entity->getPluginInstance();
        $parent = $plugin->getParentSku($sku_entity);

        // Refresh Current Sku stock.
        $sku_entity->refreshStock();
        // Refresh parent stock once if exists for cart items.
        if ($parent instanceof SKU && !in_array($parent->getSku(), $processed_parents)) {
          $processed_parents[] = $parent->getSku();
          $parent->refreshStock();
        }
      }
    }
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
