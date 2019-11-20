<?php

namespace Drupal\acq_cybersource\Controller;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_checkout\Event\AcqCheckoutPaymentFailedEvent;
use Drupal\acq_cybersource\CybersourceAPIWrapper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides the checkout form page.
 */
class CybersourceController implements ContainerInjectionInterface {

  use StringTranslationTrait;

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
   * Logger Channel object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * ACM API Version.
   *
   * @var string
   */
  protected $apiVersion;

  /**
   * Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * Constructs a new CybersourceController object.
   *
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart session.
   * @param \Drupal\acq_cybersource\CybersourceAPIWrapper $api_wrapper
   *   API Wrapper object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger channel factory object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event Dispatcher.
   */
  public function __construct(CartStorageInterface $cart_storage,
                              CybersourceAPIWrapper $api_wrapper,
                              ConfigFactoryInterface $config_factory,
                              LoggerChannelFactoryInterface $logger_factory,
                              ModuleHandlerInterface $module_handler,
                              EventDispatcherInterface $dispatcher) {
    $this->cartStorage = $cart_storage;
    $this->apiWrapper = $api_wrapper;
    $this->config = $config_factory->get('acq_cybersource.settings');
    $this->apiVersion = $config_factory->get('acq_commerce.conductor')->get('api_version');
    $this->logger = $logger_factory->get('acq_cybersource');
    $this->moduleHandler = $module_handler;
    $this->dispatcher = $dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_cart.cart_storage'),
      $container->get('acq_cybersource.api'),
      $container->get('config.factory'),
      $container->get('logger.factory'),
      $container->get('module_handler'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * Page callback to get cybersource token.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Response object.
   */
  public function getToken(Request $request) {
    $response = new Response();
    $response->headers->set('Content-Type', 'application/json');

    $type = $request->request->get('card_type');
    $form_data = $request->request->all();

    // Get the code from value provided by JS.
    $cc_type = isset(self::$ccTypeMap[$type]) ? self::$ccTypeMap[$type] : '';

    // Get the allowed types from config.
    $allowed_cc_types = explode(',', $this->config->get('allowed_cc_types'));

    // We check if cc type is valid and is allowed.
    if (empty($cc_type) || !in_array($cc_type, $allowed_cc_types)) {
      $errors = [];
      $errors['global'] = $this->getGlobalErrorMarkup(
        $this->t("We're sorry, the entered credit card is not supported. Please use a different credit card to proceed.")
      );
      $response->setContent(json_encode(['errors' => $errors]));
      return $response;
    }

    // Get the cart object.
    $cart = $this->cartStorage->getCart(FALSE);

    if (empty($cart)) {
      throw new AccessDeniedHttpException('No cart available to get token');
    }

    // Allow all modules to validate and update cart data before doing getToken.
    $errors = [];
    $this->moduleHandler->alter('acq_cybersource_before_get_token_cart', $cart, $form_data, $errors);

    if ($errors) {
      if (!empty($errors['global'])) {
        $errors['global'] = $this->getGlobalErrorMarkup($errors['global']);
      }
      $response->setContent(json_encode(['errors' => $errors]));
      return $response;
    }

    // Set the payment method.
    $cart->setPaymentMethod('cybersource');

    try {
      // Update the cart.
      $this->cartStorage->updateCart(FALSE);

      $token_info = $this->apiWrapper->cybersourceTokenRequest($cart->id(), $cc_type);

      // Do some cleaning.
      foreach ($token_info as &$info) {
        if (empty($info)) {
          $info = '';
        }
      }

      // Save transaction_uuid in session to compare later for better security.
      $session = $request->getSession();
      $session->set('cybersource_transaction_uuid', $token_info['transaction_uuid']);
      $session->save();

      $cybersource_url = $this->config->get('env') == 'test' ? $this->config->get('test_url') : $this->config->get('prod_url');
      $cybersource_url .= self::JS_API_ENDPOINT;
      $response_data = [];
      $response_data['url'] = $cybersource_url;
      $response_data['data'] = $token_info;
      $response->setContent(json_encode($response_data));
    }
    catch (\Exception $e) {
      $this->logger->info('Error while getting Cybersource token: %message <br> Cart id: %cart_id and Card type: %card_type', [
        '%message' => $e->getMessage(),
        '%cart_id' => $cart->id(),
        '%card_type' => $cc_type,
      ]);

      $response_data['errors']['global'] = $this->getGlobalErrorMarkup(
        $this->t('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.')
      );

      $response->setContent(json_encode($response_data));
    }

    return $response;
  }

  /**
   * Page callback to process cybersource response.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Response object
   */
  public function processToken(Request $request) {
    $post_data = $request->request->all();

    // Sanity check.
    if (empty($post_data) || empty($post_data['signature'])) {
      throw new AccessDeniedHttpException();
    }

    // Get transaction_uuid from session to check if request is secure.
    $session = $request->getSession();
    $transaction_uuid = $session->get('cybersource_transaction_uuid');

    // Check if transaction_uuid is not empty.
    if (empty($transaction_uuid)) {
      throw new AccessDeniedHttpException();
    }
    // Check if transaction_uuid in request matches the one in session.
    elseif ($transaction_uuid != $post_data['req_transaction_uuid']) {
      throw new AccessDeniedHttpException();
    }

    // Remove it again to ensure no double calls are made for same token.
    $session->set('cybersource_transaction_uuid', '');
    $session->save();

    $response = new Response();
    $script = '';

    // Anything other then accept is an issue.
    if (strtolower($post_data['decision']) != 'accept') {
      $message = (string) $this->t('Error while processing payment using Cybersource: %message <br> %info', [
        '%message' => $post_data['message'],
        '%info' => print_r($post_data, TRUE),
      ]);

      $this->logger->info($message);
      // @TODO: Need to check how it is handled in Magento.
      $error = $this->getGlobalErrorMarkup(
        $this->t('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.',
          [],
          ['langcode' => $post_data['req_locale']]
        )
      );

      $event = new AcqCheckoutPaymentFailedEvent('cybersource', $message);
      $this->dispatcher->dispatch(AcqCheckoutPaymentFailedEvent::EVENT_NAME, $event);

      $script = "window.parent.Drupal.cybersourceShowGlobalError('" . $error . "')";
    }
    else {
      // Get the cart object.
      $cart = $this->cartStorage->getCart(FALSE);

      if (empty($cart)) {
        throw new AccessDeniedHttpException('No cart available to process token');
      }

      // Set the payment method.
      $cart->setPaymentMethod('cybersource');

      if ($this->apiVersion !== 'v1') {
        // V2 onwards set the token info into update cart object.
        $cart->setExtension('cybersource_token', $post_data);
      }

      // Get update cart array, we will call the API here directly.
      $cart_update = $cart->getCart();

      if ($this->apiVersion === 'v1') {
        // V1 - Set the token info into update cart object.
        $cart_update->cybersource_token = $post_data;
      }

      try {
        try {
          // Call the API to pass the token info.
          $updated_cart = $this->apiWrapper->updateCart($cart->id(), $cart_update);

          // This is to allow V1 and V2 work together.
          if ($this->apiVersion !== 'v1') {
            $updated_cart['cart'] = $updated_cart;

            // V2 will throw exception if it fails.
            $updated_cart['cybersource_result'] = TRUE;
          }
        }
        catch (\Exception $e) {
          // @TODO: Get exception code and act based on the code.
          throw new \Exception('Invalid response from Magento API while processing token.');
        }

        // Check if we have the result set.
        if ($updated_cart['cybersource_result']) {
          // Update the cart in session.
          $updated_cart['cart'] = (object) $updated_cart['cart'];
          $updated_cart['cart']->cart_id = $cart->id();
          $cart->updateCartObject($updated_cart['cart']);
          $this->cartStorage->addCart($cart);

          // Send the response to place order.
          $script = 'window.parent.Drupal.finishCybersourcePayment();';
        }
        else {
          throw new \Exception('Invalid response from Magento API while processing token.');
        }
      }
      catch (\Exception $e) {
        $this->logger->info('Error while processing Cybersource token: %message <br> %info <br> %response', [
          '%message' => $e->getMessage(),
          '%info' => print_r($post_data, TRUE),
          '%response' => print_r($updated_cart, TRUE),
        ]);

        $error = $this->getGlobalErrorMarkup(
          $this->t('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.',
            [],
            ['langcode' => $post_data['req_locale']]
          )
        );
        $script = "window.parent.Drupal.cybersourceShowGlobalError('" . $error . "')";
      }
    }

    $response->setContent('<script type="text/javascript">' . $script . '</script>');

    return $response;
  }

  /**
   * Utility function to get rendered error message markup.
   *
   * @param string $error
   *   Error message.
   *
   * @return string
   *   Rendered drupal error message markup.
   */
  private function getGlobalErrorMarkup($error) {
    drupal_set_message($error, 'error');

    $messages = [
      '#theme' => 'status_messages',
      '#message_list' => drupal_get_messages(),
    ];

    $error = render($messages);
    $error = str_replace(["\r", "\n"], '', $error);

    return '<div class="cybersource-global-error">' . $error . '</div>';
  }

}
