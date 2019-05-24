<?php

namespace Drupal\alshaya_social_facebook\EventSubscriber;

use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_auth\AuthManager\OAuth2ManagerInterface;
use Drupal\social_auth\Event\SocialAuthEvents;
use Drupal\social_auth\Event\SocialAuthUserFieldsEvent;
use Drupal\social_auth\SocialAuthDataHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\alshaya_acm_customer\CustomerHelper;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Event subscriber to fill first and last name for user using facebook.
 */
class AlshayaSocialFacebookSubscriber implements EventSubscriberInterface {

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
   * The Facebook authentication manager.
   *
   * @var \Drupal\social_auth\AuthManager\OAuth2ManagerInterface
   */
  protected $providerAuth;

  /**
   * The customer helper.
   *
   * @var \Drupal\alshaya_acm_customer\CustomerHelper
   */
  protected $customerHelper;

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

  /**
   * AlshayaSocialFacebookSubscriber constructor.
   *
   * @param \Drupal\social_auth\SocialAuthDataHandler $data_handler
   *   Used to manage session variables.
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   An instance of social_auth_facebook network plugin.
   * @param \Drupal\social_auth\AuthManager\OAuth2ManagerInterface $provider_auth
   *   The provider auth manager.
   * @param \Drupal\alshaya_acm_customer\CustomerHelper $customer_helper
   *   The customer helper.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory object.
   */
  public function __construct(
    SocialAuthDataHandler $data_handler,
    NetworkManager $network_manager,
    OAuth2ManagerInterface $provider_auth,
    CustomerHelper $customer_helper,
    LoggerChannelFactory $logger_factory
  ) {
    $this->dataHandler = $data_handler;
    $this->networkManager = $network_manager;
    $this->providerAuth = $provider_auth;
    $this->customerHelper = $customer_helper;
    $this->logger = $logger_factory->get('alshaya_social_facebook');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[SocialAuthEvents::USER_FIELDS][] = ['onUserFields', 100];
    return $events;
  }

  /**
   * Add first name and last name info from facebook profile.
   *
   * @param \Drupal\social_auth\Event\SocialAuthUserFieldsEvent $event
   *   The social auth user fields event object.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function onUserFields(SocialAuthUserFieldsEvent $event) {
    if ($event->getPluginId() !== 'social_auth_facebook' || empty($this->dataHandler->get('access_token'))) {
      return;
    }

    $facebook = $this->networkManager->createInstance($event->getPluginId())->getSdk();
    $this->providerAuth->setClient($facebook)->setAccessToken($this->dataHandler->get('access_token'));

    // Gets user's FB profile from Facebook API.
    if ($fb_profile = $this->providerAuth->getUserInfo()) {
      $fields = $event->getUserFields();
      $fields['field_first_name'] = $fb_profile->getFirstName();
      $fields['field_last_name'] = $fb_profile->getLastName();

      try {
        $customer = $this->customerHelper->updateCustomer(
          NULL,
          $fields['mail'],
          $fields['field_first_name'],
          $fields['field_last_name'],
          $fields['pass']
        );
      }
      catch (\Exception $e) {
        // Do nothing except for downtime exception, we will do other
        // validations after try/catch.
        if (acq_commerce_is_exception_api_down_exception($e)) {
          $this->logger->error('Error occurred during customer registration @message', [
            '@message' => $e->getMessage(),
          ]);
        }
        else {
          $this->logger->error('Error occurred during customer registration @message', [
            '@message' => $e->getMessage(),
          ]);
        }
        return;
      }
      $fields['acq_customer_id'] = $customer['customer_id'];
      $event->setUserFields($fields);
    }
  }

}
