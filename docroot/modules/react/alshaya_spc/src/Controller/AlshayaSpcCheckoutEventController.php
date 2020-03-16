<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

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
    $order_id = $request->request->get('order_id');
    $cart = $request->request->get('cart');
    $payment_method = $request->get('payment_method');
    if (empty($order_id)) {
      throw new BadRequestHttpException($this->t('Missing required parameters'));
    }

    switch ($action) {
      case 'place order success':
        $session = $request->getSession();
        $previous_order_id = $session->get('last_order_id');
        if ($previous_order_id) {
          Cache::invalidateTags(['order:' . $previous_order_id]);
        }
        $session->set('last_order_id', $order_id);
        if ($this->currentUser->isAnonymous() || !$this->alshayaCustomerIsCustomer($this->currentUser)) {
          // Store the email address of customer in session.
          $email = $cart["customer"]["email"];
          $session->set('email_order_' . $order_id, $email);
        }
        else {
          $email = $this->currentUser->getEmail();
          $current_user_id = $this->currentUser->id();

          // Update user's mobile number if empty.
          $account = $this->entityTypeManager->getStorage('user')->load($current_user_id);

          if (empty($account->get('field_mobile_number')->getString())) {
            $phone_number = $cart["customer"]["email"];
            $account->get('field_mobile_number')->setValue($phone_number);
            $account->save();
          }
          else {
            // Clear user's cache.
            Cache::invalidateTags(['user:' . $current_user_id]);
          }

        }
        // Set selected payment method in session.
        $session->set('selected_payment_method', $payment_method);
        $session->save();
        // Refresh stock for products in cart.
        $this->refreshStockForProductsInCart($cart);

        // Add success message in logs.
        $this->logger->info('Placed order. Cart id: @cart_id. Order id: @order_id. Payment method: @method', [
          '@cart_id' => $cart['cart_id'],
          '@order_id' => $order_id,
          '@method' => $session->get('selected_payment_method'),
        ]);

        // While debugging we log the whole cart object.
        $this->logger->debug('Placed order for cart: @cart', [
          '@cart' => json_encode($cart),
        ]);
        break;
    }

    return new JsonResponse($order_id);
  }

  /**
   * Refresh stock cache and Drupal cache of products in cart.
   */
  public function refreshStockForProductsInCart($cart = NULL) {
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
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   User to check if it is valid customer.
   *
   * @return bool
   *   True if user is customer, false otherwise.
   */
  public function alshayaCustomerIsCustomer(AccountProxyInterface $user) {
    if ($user instanceof UserInterface) {
      // Do nothing, we need this to have else condition.
    }
    elseif ($user instanceof AccountProxyInterface) {
      $user = $this->entityTypeManager->getStorage('user')->load($user->id());
    }
    else {
      return FALSE;
    }

    if (empty($user->get('acq_customer_id')->getString())) {
      return FALSE;
    }

    return TRUE;
  }

}
