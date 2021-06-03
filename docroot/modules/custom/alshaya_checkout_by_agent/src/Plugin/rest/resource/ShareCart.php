<?php

namespace Drupal\alshaya_checkout_by_agent\Plugin\rest\resource;

use Drupal\alshaya_spc\Helper\SecureText;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\kaleyra\MessageApiAdapter;
use Drupal\kaleyra\WhatsAppApiAdapter;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to cart URL with smart agent info.
 *
 * @RestResource(
 *   id = "share_cart",
 *   label = @Translation("Share Cart"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/share-cart",
 *     "https://www.drupal.org/link-relations/create" = "/rest/v1/share-cart"
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
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    MessageApiAdapter $message_api_adapter,
    WhatsAppApiAdapter $whatsapp_api_adapter,
    Request $current_request,
    MailManagerInterface $mail_manager,
    LanguageManagerInterface $language_manager,
    MobileNumberUtilInterface $mobile_util,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->messageApiAdapter = $message_api_adapter;
    $this->whatsAppApiAdapter = $whatsapp_api_adapter;
    $this->currentRequest = $current_request;
    $this->mailManager = $mail_manager;
    $this->languageManager = $language_manager;
    $this->mobileUtil = $mobile_util;
    $this->configFactory = $config_factory;
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
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('plugin.manager.mail'),
      $container->get('language_manager'),
      $container->get('mobile_number.util'),
      $container->get('config.factory')
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

    // @todo basic validation of cart id.
    $cartId = $data['cartId'];

    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $data = [
      'cart_id' => $cartId,
      'smart_agent' => base64_decode($smart_agent),
      'langcode' => $langcode,
    ];

    $key = Settings::get('alshaya_api.settings');
    $encryptedData = SecureText::encrypt(json_encode($data), $key['consumer_secret']);
    $cart_url = $this->currentRequest->getSchemeAndHttpHost();
    $cart_url .= _alshaya_spc_get_middleware_url();
    $cart_url .= '/cart/smart-agent-cart-resume';
    $cart_url .= '?data=' . $encryptedData;
    $responseData = ['success' => TRUE];

    switch ($context) {
      case 'wa':
        // @todo Validate mobile number.
        $to = $this->getFullMobileNumber($to);

        $template = $this->configFactory->get('alshaya_checkout_by_agent.settings')->get('whatsapp_template');
        $this->whatsAppApiAdapter->sendUsingTemplate($to, $template, [$cart_url], $langcode);
        break;

      case 'sms':
        // @todo Validate mobile number.
        $to = $this->getFullMobileNumber($to);

        // @todo replace message with actual message.
        $message = $this->t('Dear Customer, Complete you order by visiting the link: @link', [
          '@link' => $cart_url,
        ]);

        $this->messageApiAdapter->send($to, $message->render());
        break;

      case 'email':
        // @todo Validate email address.
        // @todo replace message with actual message.
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

    return (new ResourceResponse($responseData));
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

    if (strpos($value, $country_mobile_code) === FALSE) {
      $value = $country_mobile_code . $value;
    }

    return $value;
  }

}
