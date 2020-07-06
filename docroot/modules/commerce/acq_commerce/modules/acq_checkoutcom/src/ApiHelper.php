<?php

namespace Drupal\acq_checkoutcom;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Component\Datetime\Time;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    AlshayaApiWrapper $api_wrapper,
    ConfigFactoryInterface $config_factory,
    UserDataInterface $user_data,
    LoggerChannelFactory $logger_factory,
    CacheBackendInterface $cache,
    Time $time,
    DateFormatterInterface $date_formatter,
    EntityTypeManagerInterface $entityTypeManager,
    MessengerInterface $messenger
  ) {
    $this->apiWrapper = $api_wrapper;
    $this->configFactory = $config_factory;
    $this->userData = $user_data;
    $this->logger = $logger_factory->get('acq_checkoutcom');
    $this->cache = $cache;
    $this->time = $time;
    $this->dateFormatter = $date_formatter;
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
  }

  /**
   * Get the API Cache Time from config.
   *
   * @return int
   *   API Cache Time.
   */
  protected function getCacheTime() {
    return (int) $this->getConfig()->get('api_cache_time') ?? 0;
  }

  /**
   * Get checkout.com settings.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   Checkout.com settings.
   */
  protected function getConfig() {
    return $this->configFactory->get('acq_checkoutcom.settings');
  }

  /**
   * Get config to know Apple Pay is allowed in.
   *
   * @return string
   *   Apple Pay Allowed IN - all / mobile / none.
   */
  public function getApplePayAllowedIn() {
    $allowed_in = $this->getConfig()->get('apple_pay_allowed_in') ?? 'all';
    $possible_values = ['all', 'mobile', 'none'];
    if (!in_array($allowed_in, $possible_values)) {
      $allowed_in = 'all';
    }
    return $allowed_in;
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
  public function getCardType($type): ?string {
    return $this->ccTypesMap[$type] ?? NULL;
  }

  /**
   * Get the subscription keys for checkout.com.
   *
   * @param string|null $type
   *   Type of key, public_key or secret_key.
   * @param bool $reset
   *   Reset cached data and fetch again.
   *
   * @return array|mixed
   *   Return array of keys.
   */
  public function getCheckoutcomConfig(?string $type, $reset = FALSE) {
    $cache_key = 'acq_checkoutcom:api_configs';

    $cache = $reset ? NULL : $this->cache->get($cache_key);

    if (empty($cache) || empty($cache->data)) {
      $response = $this->apiWrapper->invokeApi(
        'checkoutcom/getConfig',
        [],
        'GET'
      );
      $configs = Json::decode($response);

      if (!empty($configs)) {
        $this->cache->set($cache_key, $configs);
      }
    }
    else {
      $configs = $cache->data;
    }

    if (empty($configs)) {
      if ($reset) {
        $this->logger->error('Invalid response from checkout.com api, @response', [
          '@response' => Json::encode($configs),
        ]);

        return NULL;
      }

      // Try resetting once.
      return $this->getCheckoutcomConfig($type, TRUE);
    }

    return $type ? $configs[$type] : $configs;
  }

  /**
   * Get customer stored card.
   *
   * @param \Drupal\user\UserInterface|\Drupal\Core\Session\AccountProxy|string $user
   *   The user object.
   *
   * @return array
   *   Return array of customer cards or empty array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getCustomerCards($user) {
    if (!$user instanceof UserInterface && $user instanceof AccountProxyInterface) {
      $user = $this->entityTypeManager->getStorage('user')->load($user->id());
    }

    if (!alshaya_acm_customer_is_customer($user)) {
      return [];
    }

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
      $this->logger->error(strtr($response['message'], $response['parameters'] ?? []));
      $this->messenger->addError(acq_commerce_api_down_global_error_message());
      return [];
    }

    $cards = !empty($response['items'])
      ? $this->extractCardInfo($response['items'])
      : [];

    // Sort cards by last saved first.
    $cards = $this->sortCardsByDate($cards);

    $this->cache->set(
      $cache_key,
      $cards,
      $this->time->getRequestTime() + $this->getCacheTime(),
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
  protected function extractCardInfo(array $cards): array {
    if (empty($cards)) {
      return [];
    }

    $card_list = [];
    foreach ($cards as $card) {
      $token_details = Json::decode($card['token_details']);

      $card['paymentMethod'] = $this->getCardType($token_details['type']);
      // @todo: Remove if we are already receiving mada:true/false.
      $token_details['mada'] = isset($token_details['mada']) && $token_details['mada'] == 'Y';
      // Encode public hash.
      // https://github.com/acquia-pso/alshaya/pull/13267#discussion_r311886591.
      $card['public_hash'] = $this->encodePublicHash($card['public_hash']);
      $card_list[$card['public_hash']] = array_merge($card, $token_details);
    }
    return $card_list;
  }

  /**
   * Encode tokenised card's public hash.
   *
   * @param string $public_hash
   *   The public hash to encode.
   *
   * @return string
   *   The base64_encode public hash.
   */
  public function encodePublicHash(string $public_hash) {
    return base64_encode($public_hash);
  }

  /**
   * Decode public hash to get original public hash.
   *
   * @param string $public_hash
   *   The base64_encoded public hash.
   *
   * @return string
   *   The base64_decoded public hash.
   */
  public function deocodePublicHash(string $public_hash) {
    return base64_decode($public_hash);
  }

  /**
   * Sort cards by last saved dates first.
   *
   * @param array $cards
   *   The array of saved cards.
   *
   * @return array
   *   Return sorted array of cards.
   */
  protected function sortCardsByDate(array $cards): array {
    uasort($cards, function ($a, $b) {
      return (strtotime($a['created_at']) > strtotime($b['created_at'])) ? -1 : 1;
    });
    return $cards;
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
    $public_hash = $this->deocodePublicHash($public_hash);
    $response = $this->apiWrapper->invokeApi(
      "checkoutcom/deleteTokenByCustomerIdAndHash/$public_hash/customerId/$customer_id",
      [],
      'DELETE'
    );

    return Json::decode($response);
  }

}
