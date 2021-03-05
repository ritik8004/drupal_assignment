<?php

namespace Drupal\alshaya_acm_checkoutcom\Helper;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Site\Settings;

/**
 * Api helper for checkout.com upapi.
 *
 * @package Drupal\alshaya_acm_checkoutcom\Helper
 */
class AlshayaAcmCheckoutComAPIHelper {

  use LoggerChannelTrait;

  /**
   * Api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Cache backend checkout_com.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * AlshayaAcmCheckoutComAPIHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $account_proxy
   *   Current User.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Api wrapper.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend checkout_com.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $account_proxy,
    AlshayaApiWrapper $api_wrapper,
    CacheBackendInterface $cache
  ) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $account_proxy;
    $this->apiWrapper = $api_wrapper;
    $this->cache = $cache;

    $this->logger = $this->getLogger('AlshayaAcmCheckoutComAPIHelper');
  }

  /**
   * Get checkout.com upapi config.
   *
   * @param bool $reset
   *   Reset cached data and fetch again.
   *
   * @return array|mixed
   *   Return array of keys.
   */
  public function getCheckoutcomUpApiConfig($reset = FALSE) {
    static $configs;

    if (!empty($configs)) {
      return $configs;
    }

    $cache_key = 'alshaya_acm_checkoutcom:api_configs';

    // Cache time in minutes, set 0 to disable caching.
    $cache_time = (int) Settings::get('checkout_com_upapi_config_cache_time', 5);

    // Disable caching if cache time set to 0 or null in settings.
    $reset = empty($cache_time) ? TRUE : $reset;

    $cache = $reset ? NULL : $this->cache->get($cache_key);
    if (is_object($cache) && !empty($cache->data)) {
      $configs = $cache->data;
    }
    else {
      $response = $this->apiWrapper->invokeApi(
        'checkoutcomupapi/config',
        [],
        'GET'
      );

      $configs = Json::decode($response);

      if (empty($configs)) {
        $this->logger->error('Invalid response from checkout.com upapi api, @response', [
          '@response' => Json::encode($configs),
        ]);
      }
      elseif (!empty($configs) && $cache_time > 0) {
        // Cache only if enabled (cache_time set).
        $this->cache->set($cache_key, $configs, strtotime("+${cache_time} minutes"));
      }
    }

    // Try resetting once.
    if (empty($configs) && !($reset)) {
      return $this->getCheckoutcomUpApiConfig(TRUE);
    }

    return $configs;
  }

  /**
   * Gets Apple-pay UPAPI config.
   *
   * @return array|mixed
   *   Apple pay config.
   */
  public function getApplePayConfig() {
    $settings = $this->getCheckoutcomUpApiConfig();
    // Add site info from config.
    $settings += [
      'storeName' => $this->configFactory->get('system.site')->get('name'),
      'countryId' => $this->configFactory->get('system.date')->get('country.default'),
      'currencyCode' => $this->configFactory->get('acq_commerce.currency')->get('iso_currency_code'),
    ];

    return $settings;
  }

  /**
   * Get saved cards for checkout.com upapi method.
   *
   * @return array
   *   Saved cards array if available.
   */
  public function getSavedCards() {
    static $static = [];

    $customer_id = $this->getCustomerId();

    if (empty($customer_id)) {
      return [];
    }

    if (isset($static[$customer_id])) {
      return $static[$customer_id];
    }

    $saved_cards = [];

    $endpoint = 'checkoutcomupapi/getTokenList';
    $data = [
      'customer_id' => $customer_id,
    ];

    $allowed_cards_mapping = Settings::get('checkout_com_upapi_accepted_cards_mapping', []);

    $response = $this->apiWrapper->invokeApi($endpoint, $data, 'GET');
    $response = is_string($response) ? Json::decode($response) : $response;

    $items = $response['items'] ?? [];
    uasort($items, function ($a, $b) {
      return (strtotime($a['created_at']) > strtotime($b['created_at'])) ? -1 : 1;
    });

    foreach ($items as $item) {
      $saved_card = Json::decode($item['token_details']);
      $saved_card['public_hash'] = base64_encode($item['public_hash']);

      // Mape the card type to card type machine name.
      $type = strtolower($saved_card['type']);
      $saved_card['type'] = $allowed_cards_mapping[$type] ?? $saved_card['type'];
      $saved_card['paymentMethod'] = $saved_card['type'];

      $saved_cards[$saved_card['public_hash']] = $saved_card;
    }

    $static[$customer_id] = $saved_cards;
    return $saved_cards;
  }

  /**
   * Delete customer card.
   *
   * @param string $public_hash
   *   Encoded public hash.
   *
   * @return bool
   *   TRUE if card deleted successfully.
   */
  public function deleteCustomerCard(string $public_hash) {
    $cards = $this->getSavedCards();
    if (empty($cards[$public_hash])) {
      return FALSE;
    }

    $hash = base64_decode($public_hash);

    $endpoint = sprintf(
      'checkoutcomupapi/deleteTokenByCustomerIdAndHash/%s/customerId/%d',
      $hash,
      $this->getCustomerId()
    );

    $response = $this->apiWrapper->invokeApi($endpoint, [], 'DELETE');

    // Invalidate cache for the user to ensure fresh data is displayed.
    Cache::invalidateTags(['user:' . $this->currentUser->id()]);

    if ($response === 'true') {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Wrapper function to get Customer ID of the Current User.
   *
   * @return int
   *   Customer ID of the user.
   */
  public function getCustomerId() {
    if ($this->currentUser->isAnonymous()) {
      return 0;
    }

    $user = $this->entityTypeManager
      ->getStorage('user')
      ->load($this->currentUser->id());

    return (int) $user->get('acq_customer_id')->getString();
  }

  /**
   * Get current checkout.com card payment method.
   *
   * @return string
   *   Method code.
   */
  public function getCurrentMethod() {
    $checkout_config = $this->configFactory->get('alshaya_acm_checkout.settings');

    $excluded_methods = $checkout_config->get('exclude_payment_methods');
    $excluded_methods = array_filter($excluded_methods);

    return in_array('checkout_com_upapi', $excluded_methods)
      ? 'checkout_com'
      : 'checkout_com_upapi';
  }

}
