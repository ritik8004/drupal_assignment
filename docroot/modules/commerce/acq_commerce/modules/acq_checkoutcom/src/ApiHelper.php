<?php

namespace Drupal\acq_checkoutcom;

use Drupal\acq_commerce\Conductor\ClientFactory;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Class ApiHelper.
 */
class ApiHelper {

  /**
   * ClientFactory object.
   *
   * @var \Drupal\acq_commerce\Conductor\ClientFactory
   */
  protected $clientFactory;

  /**
   * Config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * LoggerChannelFactory object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\ClientFactory $client_factory
   *   ClientFactory object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory object.
   */
  public function __construct(
    ClientFactory $client_factory,
    ConfigFactoryInterface $config_factory,
    LoggerChannelFactory $logger_factory
  ) {
    $this->clientFactory = $client_factory;
    $this->configFactory = $config_factory;
    $this->logger = $logger_factory->get('acq_checkoutcom');
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
  public function getSubscriptionKeys(string $type = NULL) {
    $keys = [
      'public_key' => 'pk_test_ed88f0cd-e9b1-41b7-887e-de794963921f',
      'secret_key' => 'sk_test_863d1545-5253-4387-b86b-df6a86797baa',
    ];

    if (!empty($type)) {
      return $keys[$type];
    }

    return $keys;
  }

  /**
   * Get customer stored card.
   *
   * @param string $customer_id
   *   The customer id.
   *
   * @return array
   *   Return array of customer cards or empty array.
   */
  public function getCustomerCards(string $customer_id): array {
    $file = drupal_get_path('module', 'acq_checkoutcom') . '/saved_card_new.json';
    $data = file_get_contents($file);
    $existing_cards = !empty($data) ? json_decode($data) : [];
    return $existing_cards;
  }

  /**
   * Store new card for customer.
   *
   * @param string $customer_id
   *   The customer id.
   * @param array $card_data
   *   The card data to be stored.
   *
   * @return bool
   *   Return TRUE if card stored, FALSE otherwise.
   */
  public function storeCustomerCard(string $customer_id, array $card_data) {
    $file = drupal_get_path('module', 'acq_checkoutcom') . '/saved_card_new.json';
    $data = file_get_contents($file);
    $data = array_merge(!empty($data) ? Json::decode($data) : [], [$card_data]);
    file_put_contents($file, Json::encode($data));
    return TRUE;
  }

  /**
   * Delete given card for the customer.
   *
   * @param string $customer_id
   *   The customer id.
   * @param string $card_id
   *   The card id to delete.
   *
   * @return bool
   *   Return TRUE if card delete, FALSE otherwise.
   */
  public function deleteCustomerCard(string $customer_id, string $card_id) {
    return TRUE;
  }

}
