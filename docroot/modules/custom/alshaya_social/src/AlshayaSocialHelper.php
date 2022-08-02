<?php

namespace Drupal\alshaya_social;

use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_auth\AuthManager\OAuth2ManagerInterface;
use Drupal\social_auth\Event\UserFieldsEvent;
use Drupal\social_auth\SocialAuthDataHandler;

/**
 * Class Alshaya Social Helper.
 *
 * @package Drupal\alshaya_social
 */
class AlshayaSocialHelper {
  /**
   * Config Storage service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * API Helper object.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * The data handler.
   *
   * @var \Drupal\social_auth\SocialAuthDataHandler
   */
  private $dataHandler;

  /**
   * The network plugin manager.
   *
   * @var \Drupal\social_api\Plugin\NetworkManager
   */
  private $networkManager;

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

  /**
   * AlshayaSocialHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config storage object.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   ApiWrapper object.
   * @param \Drupal\social_auth\SocialAuthDataHandler $data_handler
   *   Used to manage session variables.
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   An instance of social_auth_facebook network plugin.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    AlshayaApiWrapper $api_wrapper,
    SocialAuthDataHandler $data_handler,
    NetworkManager $network_manager,
    LoggerChannelFactory $logger_factory
  ) {
    $this->configFactory = $config_factory;
    $this->apiWrapper = $api_wrapper;
    $this->dataHandler = $data_handler;
    $this->networkManager = $network_manager;
    $this->logger = $logger_factory->get('alshaya_social');
  }

  /**
   * Return status of social_login.
   *
   * @return bool
   *   Return true when social login is enabled and atleast one network is
   *   available.
   */
  public function getStatus() {
    if (!$this->configFactory->get('alshaya_social.settings')->get('social_login')) {
      return FALSE;
    }

    return !empty($this->getEnabledNetworks());
  }

  /**
   * Get all social auth networks that are avaialble to display.
   *
   * @return array
   *   Return array of all available social auth to display on frontend.
   */
  public function getSocialNetworks() {
    $auth = $this->configFactory->get('social_auth.settings')->get('auth');
    $enable_networks = array_keys($this->getEnabledNetworks());
    return array_filter($auth, fn($key) => in_array($key, $enable_networks), ARRAY_FILTER_USE_KEY);
  }

  /**
   * Get all enabled social auth networks.
   *
   * @return array
   *   Return array of plugin_id, for all enabled networks.
   */
  protected function getEnabledNetworks() {
    return array_filter($this->configFactory->get('alshaya_social.settings')->get('social_networks'));
  }

  /**
   * Set user profile fields from provider.
   *
   * @param \Drupal\social_auth\AuthManager\OAuth2ManagerInterface $providerAuth
   *   The auth provider.
   * @param \Drupal\social_auth\Event\UserFieldsEvent $event
   *   The event object.
   *
   * @return null|array
   *   Retun array of updated fields with value.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function socialAuthUserFields(OAuth2ManagerInterface $providerAuth, UserFieldsEvent $event) {
    if (empty($this->dataHandler->get('access_token'))) {
      return NULL;
    }

    $provider = $this->networkManager->createInstance($event->getPluginId())->getSdk();
    $providerAuth->setClient($provider)->setAccessToken($this->dataHandler->get('access_token'));

    // Gets user's profile from social auth provider.
    if ($user_info = $providerAuth->getUserInfo()) {
      $fields = $event->getUserFields();
      $fields['field_first_name'] = trim($user_info->getFirstName());
      $fields['field_last_name'] = trim($user_info->getLastName());

      if (empty($fields['field_first_name']) && empty($fields['field_last_name'])) {
        $this->logger->warning('First and last name both are empty for social user, rejecting. Data @data', [
          '@data' => json_encode($fields),
        ]);

        throw new \UnexpectedValueException('We need at-least one name to create the account');
      }

      if (empty($fields['field_first_name'])) {
        $fields['field_first_name'] = AlshayaAddressBookManager::INVISIBLE_CHARACTER;
      }
      if (empty($fields['field_last_name'])) {
        $fields['field_last_name'] = AlshayaAddressBookManager::INVISIBLE_CHARACTER;
      }

      try {
        // Get the customer id for existing user.
        $existing_customer = $this->apiWrapper->getCustomer($fields['mail']);

        $customer_array = [
          'customer_id' => $existing_customer['customer_id'] ?? NULL,
          'firstname' => $fields['field_first_name'],
          'lastname' => $fields['field_last_name'],
          'email' => $fields['mail'],
        ];

        $customer = $this->apiWrapper->updateCustomer($customer_array, []);

        if (!empty($customer['customer_id']) && !empty($fields['pass'])) {
          $this->apiWrapper->updateCustomerPass($customer, $fields['pass']);
        }

        if (empty($customer)) {
          throw new \Exception('Create or update customer failed.');
        }
      }
      catch (\Exception $e) {
        $this->logger->error('Error occurred during customer registration @message', [
          '@message' => $e->getMessage(),
        ]);

        throw $e;
      }
      $fields['acq_customer_id'] = $customer['customer_id'];
      return $fields;
    }
    return NULL;
  }

}
