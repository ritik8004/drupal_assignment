<?php

namespace Drupal\alshaya_social_facebook\EventSubscriber;

use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_auth\Event\SocialAuthEvents;
use Drupal\social_auth\Event\SocialAuthUserFieldsEvent;
use Drupal\social_auth_facebook\FacebookAuthManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\alshaya_acm_customer\CustomerHelper;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Event subscriber to fill first and last name for user using facebook.
 */
class AlshayaSocialFacebookSubscriber implements EventSubscriberInterface {

  /**
   * The network plugin manager.
   *
   * @var \Drupal\social_api\Plugin\NetworkManager
   */
  private $networkManager;

  /**
   * The Facebook authentication manager.
   *
   * @var \Drupal\social_auth_facebook\FacebookAuthManager
   */
  protected $facebookManager;

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
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   Used to get an instance of social_auth_facebook network plugin.
   * @param \Drupal\social_auth_facebook\FacebookAuthManager $facebook_manager
   *   Used to manage authentication methods.
   * @param \Drupal\alshaya_acm_customer\CustomerHelper $customer_helper
   *   The customer helper.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory object.
   */
  public function __construct(
    NetworkManager $network_manager,
    FacebookAuthManager $facebook_manager,
    CustomerHelper $customer_helper,
    LoggerChannelFactory $logger_factory
  ) {
    $this->networkManager = $network_manager;
    $this->facebookManager = $facebook_manager;
    $this->customerHelper = $customer_helper;
    $this->logger = $logger_factory->get('alshaya_social');
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
   */
  public function onUserFields(SocialAuthUserFieldsEvent $event) {
    /* @var \Facebook\Facebook|false $facebook */
    $facebook = $this->networkManager->createInstance('social_auth_facebook')->getSdk();
    $this->facebookManager->setClient($facebook)->authenticate();

    // Gets user's FB profile from Facebook API.
    if ($fb_profile = $this->facebookManager->getUserInfo('id,first_name,last_name,email')) {
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
