<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\alshaya_spc\Helper\CookieHelper;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\alshaya_spc\Helper\SecureText;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
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
  const PAYMENT_DECLINED_VALUE = 'declined';

  /*
   * Magento method, to append for UAPAPI vault (tokenized card) transaction.
   */
  const CHECKOUT_COM_UPAPI_VAULT_METHOD = 'checkout_com_upapi_vault';

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
   * AlshayaSpcUpapiPaymentController constructor.
   *
   * @param \Drupal\alshaya_acm_customer\OrdersManager $orders_manager
   *   Orders Manager.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger.
   */
  public function __construct(OrdersManager $orders_manager,
                              LoggerChannelInterface $logger) {
    $this->ordersManager = $orders_manager;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_spc.order_helper'),
      $container->get('logger.factory')->get('AlshayaSpcUpapiPaymentController')
    );
  }

  /**
   * Overridden controller for cart page.
   *
   * @param \GuzzleHttp\Psr7\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Response to redirect to cart or confirmation page.
   */
  public function success(Request $request) {
    $order_id = $request->query->get('order_id');

    // In case of error, we redirect to cart page.
    $redirect = new RedirectResponse(Url::fromRoute('acq_cart.cart')->toString(), 302);

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
      // @todo post processing on success which involves cleaning
      // cache and session.
      $order['secure_order_id'] = SecureText::encrypt(
        json_encode(['order_id' => $order_id, 'email' => $email]),
        Settings::get('alshaya_api.settings')['consumer_secret']
      );

      // Redirect user to confirmation page.
      $redirect->setTargetUrl(Url::fromRoute(
        'alshaya_spc.checkout.confirmation',
        [],
        ['query' => ['id' => $order['secure_order_id']]]
      ));

      $redirect->headers->setCookie(CookieHelper::create('middleware_order_placed', 1, strtotime('+1 year')));

      $this->logger->notice('Order placed successfully. Payment Method: @payment_method OrderId: @order_id', [
        '@payment_method' => $payment_method,
        '@order_id' => $order_id,
      ]);
    }
    catch (\Exception $e) {
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
   * @param string $payment_method
   *   Payment Method used for this callback.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Response to redirect to cart or confirmation page.
   */
  public function error(Request $request, string $payment_method) {
    $this->logger->error('UPAPI Payment failed for Payment Method: @payment_method, Type: @type.', [
      '@payment_method' => $payment_method,
      '@type' => $request->query->get('type'),
    ]);

    $response = new RedirectResponse(Url::fromRoute('alshaya_spc.checkout')->toString(), 302);

    $payment_data = [
      'status' => self::PAYMENT_DECLINED_VALUE,
      'payment_method' => $payment_method,
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

    $response->headers->setCookie(CookieHelper::create(
      'middleware_payment_error',
      json_encode($payment_data),
      strtotime('+1 year')
    ));

    return $response;

  }

}
