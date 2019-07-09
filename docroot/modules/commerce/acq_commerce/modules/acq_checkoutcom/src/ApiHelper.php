<?php

namespace Drupal\acq_checkoutcom;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Component\Datetime\Time;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\user\UserDataInterface;
use Drupal\user\UserInterface;

/**
 * Class ApiHelper.
 */
class ApiHelper {

  /**
   * Alshaya API Wrapper service object.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The user data service.
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * LoggerChannelFactory object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Cache backend object.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\Time
   */
  protected $time;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Credit card type map.
   *
   * @var array
   */
  protected $ccTypesMap = [
    'AE' => 'amex',
    'VI' => 'visa',
    'MC' => 'mastercard',
    'DI' => 'discover',
    'JCB' => 'jcb',
    'DN' => 'dinersclub',
  ];

  /**
   * ApiHelper constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Alshaya API Wrapper service object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Drupal\user\UserDataInterface $user_data
   *   The user data service.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory object.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend object.
   * @param \Drupal\Component\Datetime\Time $time
   *   The time service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date Formatter service.
   */
  public function __construct(
    AlshayaApiWrapper $api_wrapper,
    ConfigFactoryInterface $config_factory,
    UserDataInterface $user_data,
    LoggerChannelFactory $logger_factory,
    CacheBackendInterface $cache,
    Time $time,
    DateFormatterInterface $date_formatter
  ) {
    $this->apiWrapper = $api_wrapper;
    $this->configFactory = $config_factory;
    $this->userData = $user_data;
    $this->logger = $logger_factory->get('acq_checkoutcom');
    $this->cache = $cache;
    $this->time = $time;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * Get card type based on code.
   *
   * @param string $type
   *   Card type code.
   *
   * @return string|null
   *   Return card type name or null.
   */
  public function getCardType($type) {
    return $this->ccTypesMap[$type] ?? NULL;
  }

  /**
   * Get the subscription keys for checkout.com.
   *
   * @param string|null $type
   *   Type of key, public_key or secret_key.
   *
   * @return array|mixed
   *   Return array of keys.
   */
  public function getSubscriptionInfo(string $type = NULL) {
    $keys = [
      'public_key' => 'pk_test_ed88f0cd-e9b1-41b7-887e-de794963921f',
      'secret_key' => 'sk_test_863d1545-5253-4387-b86b-df6a86797baa',
      // @todo: Remove config once api is in place.
      'verify_3dsecure' => $this->configFactory->get('acq_checkoutcom.settings')->get('verify_3dsecure'),
    ];

    if (!empty($type)) {
      return $keys[$type];
    }

    return $keys;
  }

  /**
   * Get customer stored card.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   *
   * @return array
   *   Return array of customer cards or empty array.
   */
  public function getCustomerCards(UserInterface $user) {
    $cache_key = 'acq_checkoutcom:payment_cards:' . $user->id();
    $cache = $this->cache->get($cache_key);
    if ($cache) {
      return $cache->data;
    }

    $customer_id = $user->get('acq_customer_id')->getString();
    $response = $this->apiWrapper->invokeApi(
      "checkoutcom/getTokenList/?customer_id=$customer_id",
      [],
      'GET'
    );
    $response = Json::decode($response);

    if (!empty($response) && isset($response['message'])) {
      return strtr($response['message'], $response['parameters'] ?? []);
    }

    $cards = $this->extractCardInfo($response['items']);
    $this->cache->set(
      $cache_key,
      $cards,
      Cache::PERMANENT,
      ['user:' . $user->id()]
    );
    return $cards;
  }

  /**
   * Extract encoded token details of card info.
   *
   * @param array $cards
   *   List of stored cards.
   *
   * @return array
   *   Return process array of card list.
   */
  protected function extractCardInfo(array $cards) {
    if (empty($cards)) {
      return [];
    }

    $time = $this->time->getRequestTime();
    $card_list = [];
    foreach ($cards as $card) {
      $token_details = Json::decode($card['token_details']);
      list($expiryMonth, $expiryYear) = explode('/', $token_details['expirationDate']);

      // Set card is expired or not.
      $current_date = strtotime($this->dateFormatter->format($time, 'custom', 'Y-m'));
      $card_date = strtotime($expiryYear . '-' . $expiryMonth);
      $card['expired'] = ($current_date > $card_date);

      $card['paymentMethod'] = $this->ccTypesMap[$token_details['type']] ?? NULL;
      $card_list[$card['public_hash']] = array_merge($card, $token_details);
    }
    return $card_list;
  }

  /**
   * Store new card for customer.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   * @param string $card_token
   *   The card token to be stored.
   *
   * @return null|string
   *   Return empty array or string.
   */
  public function storeCustomerCard(UserInterface $user, string $card_token) {
    $customer_id = $user->get('acq_customer_id')->getString();
    $response = $this->apiWrapper->invokeApi(
      "checkoutcom/saveCard/$card_token/customerId/$customer_id",
      [],
      'GET'
    );

    $response = Json::decode($response);
    if (!empty($response) && isset($response['message'])) {
      return $response['message'];
    }

    Cache::invalidateTags(['user:' . $user->id()]);
    return NULL;
  }

  /**
   * Delete given card for the customer.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   * @param string $public_hash
   *   The card public hash to delete.
   *
   * @return bool|null
   *   Return TRUE if card deleted, null otherwise.
   */
  public function deleteCustomerCard(UserInterface $user, string $public_hash) {
    $customer_id = $user->get('acq_customer_id')->getString();
    $response = $this->apiWrapper->invokeApi(
      "checkoutcom/deleteTokenByCustomerIdAndHash/$public_hash/customerId/$customer_id",
      [],
      'DELETE'
    );

    return Json::decode($response);
  }

}
