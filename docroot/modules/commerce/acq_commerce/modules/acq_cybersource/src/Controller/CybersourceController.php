<?php

namespace Drupal\acq_cybersource\Controller;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_cybersource\CybersourceAPIWrapper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides the checkout form page.
 */
class CybersourceController implements ContainerInjectionInterface {

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
   * The cart session.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * API Wrapper object.
   *
   * @var \Drupal\acq_cybersource\CybersourceAPIWrapper
   */
  protected $apiWrapper;

  /**
   * Config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs a new CybersourceController object.
   *
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart session.
   * @param \Drupal\acq_cybersource\CybersourceAPIWrapper $api_wrapper
   *   API Wrapper object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(CartStorageInterface $cart_storage, CybersourceAPIWrapper $api_wrapper, ConfigFactoryInterface $config_factory) {
    $this->cartStorage = $cart_storage;
    $this->apiWrapper = $api_wrapper;
    $this->config = $config_factory->get('acq_cybersource.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_cart.cart_storage'),
      $container->get('acq_cybersource.api'),
      $container->get('config.factory')
    );
  }

  /**
   * Page callback to get cybersource token.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Response object.
   */
  public function getToken() {
    $response = new Response();
    $response->headers->set('Content-Type', 'application/json');

    $type = \Drupal::request()->request->get('card_type');
    $form_data = \Drupal::request()->request->all();

    // Get the code from value provided by JS.
    $cc_type = isset(self::$ccTypeMap[$type]) ? self::$ccTypeMap[$type] : '';

    // Get the allowed types from config.
    $allowed_cc_types = explode(',', $this->config->get('allowed_cc_types'));

    // We check if cc type is valid and is allowed.
    if (empty($cc_type) || !in_array($cc_type, $allowed_cc_types)) {
      throw new \InvalidArgumentException(srpintf('Invalid credit cart type %s or type not allowed.', $type));
    }

    $cart_id = $this->cartStorage->getCartId(FALSE);

    // We must have a valid cart in session.
    if (empty($cart_id)) {
      throw new AccessDeniedHttpException('No cart available to get token');
    }

    // Get the cart object.
    $cart = $this->cartStorage->getCart(FALSE);

    // Allow all modules to validate and update cart data before doing getToken.
    $errors = [];
    \Drupal::moduleHandler()->alter('acq_cybersource_before_get_token_cart', $cart, $form_data, $errors);

    if ($errors) {
      $response->setContent(json_encode(['errors' => $errors]));
      return $response;
    }

    // Set the payment method.
    $cart->setPaymentMethod('cybersource');

    // Update the cart.
    $this->cartStorage->updateCart(FALSE);

    if ($token_info = $this->apiWrapper->cybersourceTokenRequest($cart_id, $cc_type)) {
      // Do some cleaning.
      foreach ($token_info as &$info) {
        if (empty($info)) {
          $info = '';
        }
      }

      $cybersource_url = $this->config->get('env') == 'test' ? $this->config->get('test_url') : $this->config->get('prod_url');
      $cybersource_url .= self::JS_API_ENDPOINT;
      $response_data = [];
      $response_data['url'] = $cybersource_url;
      $response_data['data'] = $token_info;
      $response->setContent(json_encode($response_data));
    }
    else {
      // @TODO: Handle error state.
    }

    return $response;
  }

  /**
   * Page callback to process cybersource response.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Response object
   */
  public function processToken() {
    $post_data = \Drupal::request()->request->all();

    // Sanity check.
    if (empty($post_data) || empty($post_data['signature'])) {
      throw new AccessDeniedHttpException();
    }

    $response = new Response();

    // Get the cart object.
    $cart = $this->cartStorage->getCart(FALSE);

    // Set the payment method.
    $cart->setPaymentMethod('cybersource');

    // Get update cart array, we will call the API here directly.
    $cart_update = $cart->getCart();

    // Set the token info into update cart object.
    $cart_update->cybersource_token = $post_data;

    // Call the API to pass the token info.
    $updated_cart = $this->apiWrapper->updateCart($cart->id(), $cart_update);

    // Check if we have the result set.
    if ($updated_cart['cybersource_result']) {
      // Update the cart in session.
      $updated_cart['cart'] = (object) $updated_cart['cart'];
      $updated_cart['cart']->cart_id = $cart->id();
      $cart->updateCartObject($updated_cart['cart']);
      $this->cartStorage->addCart($cart);

      // Send the response to place order.
      $response->setContent('<script type="text/javascript">window.parent.Drupal.finishCybersourcePayment();</script>');
    }
    else {
      // @TODO: Handle error here.
      $response->setContent('');
    }

    return $response;
  }

}
