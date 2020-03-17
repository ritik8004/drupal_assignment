<?php

namespace App\Service\Cybersource;

use App\Service\Cart;
use App\Service\Config\SystemSettings;
use App\Service\Magento\MagentoApiWrapper;
use App\Service\SessionStorage;
use App\Service\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class CybersourceHelper.
 *
 * @package App\Service\Cybersource
 */
class CybersourceHelper {

  const JS_API_ENDPOINT = '/silent/embedded/token/create';

  /**
   * Map for CC type field. Drupal scope => Magento scope.
   *
   * @var array
   */
  static private $ccTypeMap = [
    'diners_club_carte_blanche' => 'DN',
    'diners_club_international' => 'DN',
    'visa' => 'VI',
    'visa_electron' => 'VI',
    'mastercard' => 'MC',
    'amex' => 'AE',
    'jcb' => 'JCB',
    'maestro' => 'MI',
  ];

  /**
   * RequestStack Object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * System Settings service.
   *
   * @var \App\Service\Config\SystemSettings
   */
  protected $settings;

  /**
   * Magento API Wrapper.
   *
   * @var \App\Service\Magento\MagentoApiWrapper
   */
  protected $magentoApiWrapper;

  /**
   * Cart service.
   *
   * @var \App\Service\Cart
   */
  protected $cart;

  /**
   * Utility.
   *
   * @var \App\Service\Utility
   */
  protected $utility;

  /**
   * Service for session.
   *
   * @var \App\Service\SessionStorage
   */
  protected $session;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * CybersourceHelper constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   RequestStack Object.
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API Wrapper.
   * @param \App\Service\Cart $cart
   *   Service for cart interaction.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   * @param \App\Service\SessionStorage $session
   *   Service for session.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(RequestStack $request,
                              SystemSettings $settings,
                              MagentoApiWrapper $magento_api_wrapper,
                              Cart $cart,
                              Utility $utility,
                              SessionStorage $session,
                              LoggerInterface $logger) {
    $this->request = $request->getCurrentRequest();
    $this->settings = $settings;
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->cart = $cart;
    $this->utility = $utility;
    $this->session = $session;
    $this->logger = $logger;
  }

  /**
   * Get cybersource token.
   *
   * @return array
   *   Token details.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getToken() {
    $data = json_decode($this->request->getContent(), TRUE);
    if (empty($data)) {
      throw new \InvalidArgumentException();
    }

    $settings = $this->settings->getSettings('cybersource');

    // We check if cc type is valid and is allowed.
    if (empty($data['type']) || !in_array($data['type'], $settings['accepted_cards'])) {
      throw new \Exception("We're sorry, the entered credit card is not supported. Please use a different credit card to proceed.");
    }

    // Get the code from value provided by JS.
    $cc_type = isset(self::$ccTypeMap[$data['type']]) ? self::$ccTypeMap[$data['type']] : '';

    // Get the cart object.
    $cart = $this->cart->getCart();
    if (empty($cart)) {
      throw new AccessDeniedHttpException('No cart available to get token');
    }

    try {
      $endpoint = sprintf('carts/%d/getCybersourceToken/%s', $cart['cart']['id'], $cc_type);

      $token_info = $this->magentoApiWrapper->doRequest('GET', $endpoint);
      // Do some cleaning.
      foreach ($token_info as &$info) {
        if (empty($info)) {
          $info = '';
        }
      }

      // Save transaction_uuid in session to compare later for better security.
      $this->session->updateDataInSession('cybersource_transaction_uuid', $token_info['transaction_uuid']);

      $response_data = [];
      $response_data['url'] = $settings['url'][$settings['env']] . self::JS_API_ENDPOINT;
      $response_data['data'] = $token_info;
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while getting cybersource token for cart id: %cart_id and card type: %card_type: %message', [
        '%cart_id' => $cart['cart']['id'],
        '%card_type' => $cc_type,
        '%message' => $e->getMessage(),
      ]);

      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }

    return $response_data;
  }

  /**
   * Process cybersource token and finish order.
   *
   * @return array
   *   Error or order details.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function finalise() {
    $post_data = $this->request->request->all();

    // Sanity check.
    if (empty($post_data) || empty($post_data['signature'])) {
      throw new AccessDeniedHttpException();
    }

    // Get transaction_uuid from session to check if request is secure.
    $transaction_uuid = $this->session->getDataFromSession('cybersource_transaction_uuid');

    // Check if transaction_uuid is not empty.
    if (empty($transaction_uuid)) {
      throw new AccessDeniedHttpException();
    }
    // Check if transaction_uuid in request matches the one in session.
    elseif ($transaction_uuid != $post_data['req_transaction_uuid']) {
      throw new AccessDeniedHttpException();
    }

    // Remove it again to ensure no double calls are made for same token.
    $this->session->updateDataInSession('cybersource_transaction_uuid', '');

    // Anything other then accept is an issue.
    if (strtolower($post_data['decision']) != 'accept') {
      $this->logger->info('Error while processing payment using Cybersource: @message <br> @info', [
        '@message' => $post_data['message'],
        '@info' => print_r($post_data, TRUE),
      ]);

      return $this->utility->getErrorResponse('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.', 400);
    }

    // Get the cart object.
    $cart = $this->cart->getCart();

    if (empty($cart)) {
      throw new AccessDeniedHttpException('No cart available to process token');
    }

    $endpoint = 'cybersourceapi/processToken';
    try {
      $this->magentoApiWrapper->doRequest('POST', $endpoint, ['response' => $post_data]);
    }
    catch (\Exception $e) {
      $this->logger->warning('Invalid response from Magento API while processing token. Cart: @id, Transaction: @uuid, Message: @message', [
        '@id' => $cart['cart']['id'],
        '@uuid' => $transaction_uuid,
        '@message' => $e->getMessage(),
      ]);

      return $this->utility->getErrorResponse('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.', 400);
    }

    try {
      $order = $this->cart->placeOrder([]);

      if (empty($order) || isset($order['error'])) {
        throw new \Exception($order['error_message'] ?? '');
      }

      return [
        'success' => TRUE,
        'redirectUrl' => '/checkout/confirmation?id=' . $order['secure_order_id'],
      ];
    }
    catch (\Exception $e) {
      $this->logger->warning('Place order for cybersource payment failed. Cart: @id, Message: @message', [
        '@id' => $cart['cart']['id'],
        '@message' => $e->getMessage(),
      ]);

      return $this->utility->getErrorResponse('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.', 500);
    }
  }

}
