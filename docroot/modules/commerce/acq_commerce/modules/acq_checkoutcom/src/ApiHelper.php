<?php

namespace Drupal\acq_checkoutcom;

use Drupal\acq_commerce\Conductor\ClientFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\user\UserDataInterface;
use Drupal\user\UserInterface;

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
   * ApiHelper constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\ClientFactory $client_factory
   *   ClientFactory object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Drupal\user\UserDataInterface $user_data
   *   The user data service.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory object.
   */
  public function __construct(
    ClientFactory $client_factory,
    ConfigFactoryInterface $config_factory,
    UserDataInterface $user_data,
    LoggerChannelFactory $logger_factory
  ) {
    $this->clientFactory = $client_factory;
    $this->configFactory = $config_factory;
    $this->userData = $user_data;
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
  public function getSubscriptionInfo(string $type = NULL) {
    $keys = [
      'public_key' => 'pk_test_ed88f0cd-e9b1-41b7-887e-de794963921f',
      'secret_key' => 'sk_test_863d1545-5253-4387-b86b-df6a86797baa',
      'verify_3dsecure' => TRUE,
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
    return $this->userData->get('acq_checkoutcom', $user->id(), 'payment_cards');
  }

  /**
   * Store new card for customer.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   * @param array $new_card
   *   The card data to be stored.
   *
   * @return bool
   *   Return TRUE if card stored, FALSE otherwise.
   */
  public function storeCustomerCard(UserInterface $user, array $new_card) {
    $card_data = $this->getCustomerCards($user);
    $card_data = array_merge(!empty($card_data) ? $card_data : [], [$new_card]);
    $this->userData->set('acq_checkoutcom', $user->id(), 'payment_cards', $card_data);
    return TRUE;
  }

  /**
   * Delete given card for the customer.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   * @param string $card_id
   *   The card id to delete.
   *
   * @return bool
   *   Return TRUE if card deleted, FALSE otherwise.
   */
  public function deleteCustomerCard(UserInterface $user, string $card_id) {
    $card_data = $this->getCustomerCards($user);
    $new_card_data = array_filter($card_data, function ($card) use ($card_id) {
      return ($card['id'] != $card_id);
    });
    if (count($card_data) == count($new_card_data)) {
      return FALSE;
    }
    $this->userData->set('acq_checkoutcom', $user->id(), 'payment_cards', $new_card_data);
    return TRUE;
  }

}
