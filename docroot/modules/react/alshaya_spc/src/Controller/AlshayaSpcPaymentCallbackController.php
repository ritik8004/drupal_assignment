<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\alshaya_spc\Helper\CookieHelper;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\alshaya_spc\Helper\SecureText;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for the callbacks for UPAPI Payments.
 */
class AlshayaSpcPaymentCallbackController extends ControllerBase {

  /**
   * Value to set in cookie when payment is declined.
   */
  public const PAYMENT_DECLINED_VALUE = 'declined';

  /*
   * Magento method, to append for UAPAPI vault (tokenized card) transaction.
   */
  public const CHECKOUT_COM_UPAPI_VAULT_METHOD = 'checkout_com_upapi_vault';

  /**
   * Orders Manager.
   *
   * @var \Drupal\alshaya_acm_customer\OrdersManager
   */
  protected $ordersManager;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AlshayaSpcUpapiPaymentController constructor.
   *
   * @param \Drupal\alshaya_acm_customer\OrdersManager $orders_manager
   *   Orders Manager.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(OrdersManager $orders_manager,
                              LoggerChannelInterface $logger,
                              ConfigFactoryInterface $config_factory) {
    $this->ordersManager = $orders_manager;
    $this->logger = $logger;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm_customer.orders_manager'),
      $container->get('logger.factory')->get('AlshayaSpcUpapiPaymentController'),
      $container->get('config.factory')
    );
  }

  /**
   * Overridden controller for cart page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Response to redirect to cart or confirmation page.
   */
  public function success(Request $request) {
    $order_id = $request->query->get('order_id');
    $encrypted_order_id = $request->query->get('encrypted_order_id');
    // In case of error, we redirect to cart page.
    $redirect = new RedirectResponse(Url::fromRoute('acq_cart.cart')->toString(), 302);
    $redirect->setMaxAge(0);
    $redirect->headers->set('cache-control', 'must-revalidate, no-cache, no-store, private');
    if ($encrypted_order_id) {
      $order_id = SecureText::decrypt(
        $encrypted_order_id,
        Settings::get('alshaya_api.settings')['consumer_secret']
      );
    }
    elseif ($order_id
      && $this->configFactory->get('alshaya_spc.settings')->get('order_id_fallback') === 'disabled') {
      $this->logger->warning('Order id fallback set to disabled. OrderId: @order_id.', [
        '@order_id' => $order_id,
      ]);
      return $redirect;
    }

    // If order id is not available in request.
    if (!$order_id) {
      $this->logger->error('User trying to access success url directly. OrderId is not available in request.');
      return $redirect;
    }

    // Check and get order details from MDC.
    $order = $this->ordersManager->getOrder($order_id);

    // If order not available in magento.
    if (!$order) {
      $this->logger->error('User is trying to access success url directly as order is not available for the orderId: @order_id.', [
        '@order_id' => $order_id,
      ]);
      return $redirect;
    }

    $payment_method = $order['payment']['method'];

    // If Payment-method is not selected by user.
    if (!$payment_method) {
      $this->logger->error('User trying to access success url directly. Payment method is not set on cart. OrderId: @order_id, Order: @order.', [
        '@order_id' => $order_id,
        '@order' => json_encode($order),
      ]);

      return $redirect;
    }

    // If payment is done by saved cards.
    if ($payment_method === 'checkout_com_upapi'
      && !empty($order['payment']['extension_attributes'])
      && !empty($order['payment']['extension_attributes']['vault_payment_token'])) {
      $payment_method = self::CHECKOUT_COM_UPAPI_VAULT_METHOD;
    }

    // Load the email address to use for encryption from Order data.
    $email = trim(strtolower($order['email']));

    try {
      $this->processPostPlaceOrder($order, $payment_method);

      $order['secure_order_id'] = SecureText::encrypt(
        json_encode(['order_id' => $order_id, 'email' => $email]),
        Settings::get('alshaya_api.settings')['consumer_secret']
      );

      // Redirect user to confirmation page.
      $redirect->setTargetUrl(
        Url::fromRoute(
          'alshaya_spc.checkout.confirmation',
          [],
          ['query' => ['id' => $order['secure_order_id']]]
        )->toString()
      );

      $redirect->headers->setCookie(CookieHelper::create('middleware_order_placed', 1, strtotime('+1 year')));
    }
    catch (\Exception) {
      // If any error/exception encountered while order was placed from
      // magento side, we redirect to cart page.
      $this->logger->error('Error while order post processing. Payment Method: @payment_method OrderId: @order_id', [
        '@payment_method' => $payment_method,
        '@order_id' => $order_id,
      ]);
    }

    return $redirect;
  }

  /**
   * Overridden controller for cart page.
   *
   * @param \GuzzleHttp\Psr7\Request $request
   *   Request object.
   * @param string $method
   *   Payment Method used for this callback.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Response to redirect to cart or confirmation page.
   */
  public function error(Request $request, string $method) {
    $this->logger->warning('UPAPI Payment failed for Payment Method: @payment_method, Type: @type.', [
      '@payment_method' => $method,
      '@type' => $request->query->get('type'),
    ]);

    $response = new RedirectResponse(Url::fromRoute('alshaya_spc.checkout')->toString(), 302);
    $response->setMaxAge(0);
    $response->headers->set('cache-control', 'must-revalidate, no-cache, no-store, private');

    $payment_data = [
      'status' => self::PAYMENT_DECLINED_VALUE,
      'payment_method' => $method,
      'message' => $request->query->get('message'),
    ];

    switch ($request->query->get('type')) {
      case 'knet':
        $payment_data['data'] = [
          'transaction_id' => $request->query->get('knet_transaction_id', ''),
          'payment_id' => $request->query->get('knet_payment_id', ''),
          'result_code' => $request->query->get('knet_result', ''),
        ];
        break;

      case 'qpay';
        $payment_data['data'] = [
          'transaction_id' => $request->query->get('confirmation_id', ''),
          'payment_id' => $request->query->get('pun', ''),
          'result_code' => $request->query->get('status_message', $request->query->get('status', '')),
          // @todo Ask Magento to provide the amount in URL.
          'amount' => $request->query->get('amount', 123),
          'date' => $request->query->get('requested_on', ''),
        ];
        break;

    }

    // Using the same way as used by user_cookie_save() in CORE.
    setrawcookie(
      'middleware_payment_error',
      rawurlencode(json_encode($payment_data)), [
        'expires' => strtotime('+1 year'),
        'path' => '/',
      ]);

    return $response;
  }

  /**
   * Process post order is placed.
   *
   * @param array $order
   *   Order array.
   * @param string $payment_method
   *   Processed payment method.
   */
  protected function processPostPlaceOrder(array $order, string $payment_method) {
    $account_id = NULL;
    $customer = user_load_by_mail($order['email']);

    // Add success message in logs.
    $this->logger->info('Placed order. Cart id: @cart_id. Order id: @order_id. Payment method: @method', [
      '@order_id' => $order['order_id'],
      '@method' => $payment_method,
      '@cart_id' => $order['quote_id'],
    ]);

    if ($customer instanceof UserInterface) {
      if (empty($customer->get('field_mobile_number')->getString())) {
        $customer->get('field_mobile_number')->setValue($order['billing']['telephone']);
        $customer->save();
      }
      else {
        // Invalidate the user cache when order is placed to reflect the
        // user specific data changes like saved payment cards.
        Cache::invalidateTags($customer->getCacheTags());
      }

      $customer_id = (int) $customer->get('acq_customer_id')->getString();
      $account_id = $customer->id();
    }

    // Clear the customer order cache.
    if (!empty($customer_id)) {
      $this->ordersManager->clearOrderCache($customer_id, $account_id);
    }

    // Reset stock cache and Drupal cache of products in last order.
    $this->ordersManager->clearLastOrderRelatedProductsCache($order);
  }

}
