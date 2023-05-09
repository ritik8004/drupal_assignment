<?php

namespace Drupal\alshaya_checkout_by_agent\Plugin\rest\resource;

use Drupal\alshaya_spc\Helper\SecureText;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\kaleyra\MessageApiAdapter;
use Drupal\kaleyra\ShortenUrlApiAdapter;
use Drupal\kaleyra\WhatsAppApiAdapter;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\token\TokenInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Component\Datetime\Time;
use Drupal\Core\Flood\FloodInterface;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Provides a resource to cart URL with smart agent info.
 *
 * @RestResource(
 *   id = "share_cart",
 *   label = @Translation("Share Cart"),
 *   uri_paths = {
 *     "create" = "/rest/v1/share-cart"
 *   }
 * )
 */
class ShareCart extends ResourceBase {
  use StringTranslationTrait;

  /**
   * Message API Adapter.
   *
   * @var \Drupal\kaleyra\MessageApiAdapter
   */
  protected $messageApiAdapter;

  /**
   * WhatsApp API Adapter.
   *
   * @var \Drupal\kaleyra\WhatsAppApiAdapter
   */
  protected $whatsAppApiAdapter;

  /**
   * Shorten URL API Adapter.
   *
   * @var \Drupal\kaleyra\ShortenUrlApiAdapter
   */
  protected $shortenUrlApiAdapter;

  /**
   * Current Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Mobile utility.
   *
   * @var \Drupal\mobile_number\MobileNumberUtilInterface
   */
  protected $mobileUtil;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The DateTime Object.
   *
   * @var \Drupal\Component\Datetime\Time
   */
  protected $time;

  /**
   * Token service.
   *
   * @var \Drupal\token\TokenInterface
   */
  protected $token;

  /**
   * The flood service.
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;

  /**
   * ShareCart constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param array $serializer_formats
   *   Serializer formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   * @param \Drupal\kaleyra\MessageApiAdapter $message_api_adapter
   *   Message API Adapter.
   * @param \Drupal\kaleyra\WhatsAppApiAdapter $whatsapp_api_adapter
   *   WhatsApp API Adapter.
   * @param \Drupal\kaleyra\ShortenUrlApiAdapter $shorten_url_api_adapter
   *   Shorten Url API Adapter.
   * @param Symfony\Component\HttpFoundation\Request $current_request
   *   The current request.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   Mail manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   Mobile utility.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Component\Datetime\Time $time
   *   Injecting time service.
   * @param \Drupal\token\TokenInterface $token
   *   Token service.
   * @param \Drupal\Core\Flood\FloodInterface $flood
   *   The flood service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    MessageApiAdapter $message_api_adapter,
    WhatsAppApiAdapter $whatsapp_api_adapter,
    ShortenUrlApiAdapter $shorten_url_api_adapter,
    Request $current_request,
    MailManagerInterface $mail_manager,
    LanguageManagerInterface $language_manager,
    MobileNumberUtilInterface $mobile_util,
    ConfigFactoryInterface $config_factory,
    Time $time,
    TokenInterface $token,
    FloodInterface $flood
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->messageApiAdapter = $message_api_adapter;
    $this->whatsAppApiAdapter = $whatsapp_api_adapter;
    $this->shortenUrlApiAdapter = $shorten_url_api_adapter;
    $this->currentRequest = $current_request;
    $this->mailManager = $mail_manager;
    $this->languageManager = $language_manager;
    $this->mobileUtil = $mobile_util;
    $this->configFactory = $config_factory;
    $this->time = $time;
    $this->token = $token;
    $this->flood = $flood;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('alshaya_checkout_by_agent'),
      $container->get('kaleyra.sms_api_adapter'),
      $container->get('kaleyra.whatsapp_api_adapter'),
      $container->get('kaleyra.shorten_url_api_adapter'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('plugin.manager.mail'),
      $container->get('language_manager'),
      $container->get('mobile_number.util'),
      $container->get('config.factory'),
      $container->get('datetime.time'),
      $container->get('token'),
      $container->get('flood'),
    );
  }

  /**
   * Responds to Post requests.
   *
   * Shares cart url through email, sms, whatsapp.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing stock data.
   */
  public function post(array $data) {
    $smart_agent = $this->currentRequest->cookies->get('smart_agent_cookie');
    if (empty($smart_agent)) {
      throw new AccessDeniedHttpException();
    }

    $smart_agent_details_array = json_decode(base64_decode($smart_agent), TRUE);
    /** @var \Drupal\user\Entity\User $user */
    $user = user_load_by_mail($smart_agent_details_array['email']);
    if (!$user || $user->isBlocked() || !$user->hasRole('smartagent')) {
      $error_message = 'Agent account is blocked or does not exist.';
      return $this->prepareResourceResponse($error_message);
    }

    $settings = $this->configFactory->get('alshaya_checkout_by_agent.settings');
    // Check if request is a flood request.
    if ($this->isFloodRequest($user->id())) {
      // Block the request and show the error message.
      $error_message = sprintf('The request is blocked because you have crossed %s request per min API call limit. Please try after some time.', $settings->get('api_request_limit'));
      throw new AccessDeniedHttpException($error_message, NULL, ResourceResponse::HTTP_TOO_MANY_REQUESTS);
    }

    // Check request has required parameters.
    if (empty($data['type']) || empty($data['value']) || empty($data['cartId'])) {
      $responseData = [
        'success' => FALSE,
        'error_message' => 'Required parameters missing',
      ];

      $response = new ResourceResponse($responseData);
      return $response;
    }

    $context = $data['type'];

    // @todo validate the mobile number of mail.
    $to = $data['value'];

    // Validating the SmartAgent info with the user object info.
    $storeCode = $user->get('field_agent_store_id')->getString();
    if (empty($storeCode)) {
      $error_message = 'The store that is assigned is missing or deleted.';
      return $this->prepareResourceResponse($error_message);
    }
    // Flag value to track update status.
    $updated = FALSE;
    if (!empty($storeCode) && $smart_agent_details_array['storeCode'] != $storeCode) {
      $smart_agent_details_array['storeCode'] = $storeCode;
      $updated = TRUE;
    }
    $user_name = $user->get('field_first_name')->getString() . ' ' . $user->get('field_last_name')->getString();
    if (!empty($user_name) && $smart_agent_details_array['name'] != $user_name) {
      $smart_agent_details_array['name'] = $user_name;
      $updated = TRUE;
    }
    // @todo basic validation of cart id.
    $cartId = $data['cartId'];

    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    // Add sharing channel and time with agent details.
    $smart_agent_details_array['shared_via'] = $context;
    $smart_agent_details_array['shared_on'] = date('Y-m-d H:i:s', $this->time->getRequestTime());
    $smart_agent_details_array['shared_to'] = $to;

    $data = [
      'cart_id' => $cartId,
      'smart_agent' => $smart_agent_details_array,
      'langcode' => $langcode,
    ];

    $key = $this->configFactory->get('alshaya_api.settings');
    $encryptedData = SecureText::encrypt(json_encode($data), $key->get('consumer_secret'));

    $cart_url = Url::fromRoute('alshaya_checkout_by_agent.resume', [], ['absolute' => TRUE])->toString();

    // Add the encrypted data in query string.
    $cart_url .= '?data=' . $encryptedData;

    $responseData = ['success' => TRUE];

    switch ($context) {
      case 'wa':
        // @todo Validate mobile number.
        $to = $this->getFullMobileNumber($to);
        $template = $settings->get('whatsapp_template');
        $whatsapp_mode = $settings->get('whatsapp_mode') ?? 'text';

        switch ($whatsapp_mode) {
          case 'text':
            $params = [
              $this->shortenUrlApiAdapter->getShortUrl($cart_url),
              $this->configFactory->get('system.site')->get('name'),
            ];

            $this->whatsAppApiAdapter->sendUsingTemplate($to, $template, $params);
            break;

          case 'button':
            $params = [
              $this->configFactory->get('system.site')->get('name'),
              Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString(TRUE)->getGeneratedUrl(),
            ];

            // For WhatsApp Button we have to send relative URL.
            $cart_url = str_replace($this->currentRequest->getSchemeAndHttpHost() . '/', '', $cart_url);

            $this->whatsAppApiAdapter->sendUsingTemplate($to, $template, $params, $cart_url);
            break;
        }

        break;

      case 'sms':
        // @todo Validate mobile number.
        $to = $this->getFullMobileNumber($to);

        // Shorten and replace the cart url in template.
        $short_url = $this->shortenUrlApiAdapter->getShortUrl($cart_url);
        $message = str_replace('@link', $short_url, $settings->get('sms_template'));

        // Replace dynamic tokens in template.
        $message = $this->token->replace($message);

        $this->messageApiAdapter->send($to, htmlspecialchars_decode($message));
        break;

      case 'email':
        // @todo Validate email address.
        $params['cart_url'] = $cart_url;
        $params['title'] = 'Smart Agent link';

        $result = $this->mailManager->mail('alshaya_checkout_by_agent', 'share_cart', $to, $langcode, $params, NULL, TRUE);
        if ($result['result'] != TRUE) {
          $message = $this->t('There was a problem sending your email notification to @email.', ['@email' => $to]);
          $this->logger->error($message);
          $responseData = [
            'success' => FALSE,
            'error_message' => 'Error occurred while sending email.',
          ];
        }
        break;
    }

    // Log the details.
    $this->logger->notice('Basket is shared by smart agent with the customer. Sharing type: @sharing_type. Value: @value. Agent Details: @smart_agent.', [
      '@sharing_type' => $context,
      '@value' => $to,
      '@smart_agent' => json_encode($data),
    ]);

    $json_response = new JsonResponse($responseData);
    // Update cookie only if the data is outdated.
    if ($updated) {
      $cookie = new Cookie(
        'smart_agent_cookie',
        base64_encode(json_encode($smart_agent_details_array)), 0, '/', NULL, TRUE, FALSE);
      $json_response->headers->setCookie($cookie);
    }

    return $json_response;
  }

  /**
   * Wrapper function to get mobile number with country code.
   *
   * Checks first if country is not available.
   *
   * @param string $value
   *   Mobile number with or without country code.
   *
   * @return string
   *   Mobile number with country code.
   */
  protected function getFullMobileNumber(string $value) {
    $country_code = _alshaya_custom_get_site_level_country_code();
    $country_mobile_code = '+' . $this->mobileUtil->getCountryCode($country_code);

    if (!str_contains($value, $country_mobile_code)) {
      $value = $country_mobile_code . $value;
    }

    return $value;
  }

  /**
   * Check if the request is a flood request or not.
   *
   * @param string $user_id
   *   The user id.
   *
   * @return bool
   *   TRUE if flood request else FALSE.
   */
  protected function isFloodRequest(string $user_id) {
    $api_request_limit = $this->configFactory->get('alshaya_checkout_by_agent.settings')->get('api_request_limit');
    if (!$this->flood->isAllowed('alshaya_checkout_by_agent.requested_api', $api_request_limit, 60, $this->getIdentifier($user_id))) {
      return TRUE;
    }
    // Register the Request.
    $this->registerFlood($user_id);

    return FALSE;
  }

  /**
   * Register request for flood control.
   *
   * @param string $user_id
   *   The user id.
   */
  protected function registerFlood(string $user_id) {
    // Register the API call request.
    $this->flood->register('alshaya_checkout_by_agent.requested_api', 60, $this->getIdentifier($user_id));
  }

  /**
   * Log and Prepare ResourceResponse object.
   *
   * @param string $error_message
   *   Error message to log and return in response.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Return ResourceRequest object.
   */
  protected function prepareResourceResponse(string $error_message) {
    $responseData = [
      'success' => FALSE,
      'error_message' => $error_message,
    ];
    // Log the error message.
    $this->logger->error($error_message);
    return new ResourceResponse($responseData);
  }

  /**
   * Provides identifier for smart agent user.
   *
   * @param string $user_id
   *   The user id.
   *
   * @return string
   *   A unique identifier for the smart agent.
   */
  protected function getIdentifier(string $user_id) {
    return 'smart_agent_' . $user_id;
  }

}
