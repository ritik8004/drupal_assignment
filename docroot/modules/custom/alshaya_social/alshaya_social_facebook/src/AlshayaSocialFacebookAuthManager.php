<?php

namespace Drupal\alshaya_social_facebook;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\social_auth_facebook\FacebookAuthManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\social_auth_facebook\FacebookAuthPersistentDataHandler;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Class AlshayaSocialFacebookAuthManager.
 *
 * @package Drupal\alshaya_social_facebook
 */
class AlshayaSocialFacebookAuthManager extends FacebookAuthManager {

  /**
   * The facebook auth manager channel.
   *
   * @var \Drupal\social_auth_facebook\FacebookAuthManager
   */
  protected $facebookAuthManager;

  /**
   * The request stack object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The language manager object.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * AlshayaSocialFacebookAuthManager constructor.
   *
   * @param \Drupal\social_auth_facebook\FacebookAuthManager $facebook_auth_manager
   *   The Facebook authentication manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Used for logging errors.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Used for dispatching events to other modules.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Used for accessing Drupal user picture preferences.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   Used for generating absolute URLs.
   * @param \Drupal\social_auth_facebook\FacebookAuthPersistentDataHandler $persistent_data_handler
   *   Used for reading data from and writing data to session.
   */
  public function __construct(
    FacebookAuthManager $facebook_auth_manager,
    RequestStack $request_stack,
    LanguageManagerInterface $language_manager,
    LoggerChannelFactoryInterface $logger_factory,
    EventDispatcherInterface $event_dispatcher,
    EntityFieldManagerInterface $entity_field_manager,
    UrlGeneratorInterface $url_generator,
    FacebookAuthPersistentDataHandler $persistent_data_handler
  ) {
    $this->facebookAuthManager = $facebook_auth_manager;
    $this->request = $request_stack->getCurrentRequest();
    $this->languageManager = $language_manager;
    parent::__construct($logger_factory, $event_dispatcher, $entity_field_manager, $url_generator, $persistent_data_handler);
  }

  /**
   * Gets the user's access token from Facebook.
   *
   * This method can only be called from route
   * social_auth_facebook.return_from_fb since RedirectLoginHelper will use the
   * URL parameters set by Facebook.
   *
   * @return \Facebook\Authentication\AccessToken|null
   *   User's Facebook access token, if it could be read from Facebook.
   *   Null, otherwise.
   *
   * @see FacebookAuthManager::getAccessToken()
   */
  public function getAccessToken() {
    if (!$this->accessToken) {
      $helper = $this->client->getRedirectLoginHelper();

      $destination = ['target' => $this->request->query->get('target')];
      // URL where Facebook returned the user.
      $return_url = $this->urlGenerator->generateFromRoute(
        'social_auth_facebook.return_from_fb', [], ['absolute' => TRUE, 'query' => $destination]);

      try {
        $access_token = $helper->getAccessToken($return_url);
      }

      catch (FacebookResponseException $ex) {
        // Graph API returned an error.
        $this->loggerFactory
          ->get('social_auth_facebook')
          ->error('Could not get Facebook access token. FacebookResponseException: @message', ['@message' => json_encode($ex->getMessage())]);
        return NULL;
      }

      catch (FacebookSDKException $ex) {
        // Validation failed or other local issues.
        $this->loggerFactory
          ->get('social_auth_facebook')
          ->error('Could not get Facebook access token. Exception: @message', ['@message' => ($ex->getMessage())]);
        return NULL;
      }

      // If login was OK on Facebook, we now have user's access token.
      if (isset($access_token)) {
        $this->accessToken = $access_token;
      }
      else {
        // If we're still here, user denied the login request on Facebook.
        $this->loggerFactory
          ->get('social_auth_facebook')
          ->error('Could not get Facebook access token. User cancelled the dialog in Facebook or return URL was not valid.');
        return NULL;
      }
    }

    return $this->accessToken;
  }

  /**
   * Get the http_referer of current request.
   *
   * @return mixed
   *   Return only path without hostname to use it with facebook.
   */
  protected function getDestinationUrl() {
    $base_url = $this->request->getSchemeAndHttpHost() . $this->request->getBasePath() . '/' . $this->languageManager->getCurrentLanguage()->getId();
    return str_replace($base_url, '', $this->request->server->get('HTTP_REFERER'));
  }

  /**
   * Returns the Facebook login URL where user will be redirected.
   *
   * @return string
   *   Absolute Facebook login URL where user will be redirected
   *
   * @see FacebookAuthManager::getFbLoginUrl()
   */
  public function getFbLoginUrl() {
    $login_helper = $this->client->getRedirectLoginHelper();
    $params = ['absolute' => TRUE];

    // If user have tried login during checkout process, add "target" to
    // redirect user, back to checkout delivery page to continue checkout.
    if (strpos($this->getDestinationUrl(), '/cart/checkout/login') !== FALSE) {
      $params['query'] = ['target' => '/cart/checkout/delivery'];
    }

    // Define the URL where Facebook should return the user.
    $return_url = $this->urlGenerator->generateFromRoute(
      'social_auth_facebook.return_from_fb', [], $params);

    // Define the initial array of Facebook permissions.
    $scope = ['public_profile', 'email'];

    // Dispatch an event so that other modules can modify the permission scope.
    // Set the scope twice on the event: as the main subject but also in the
    // list of arguments.
    $e = new GenericEvent($scope, ['scope' => $scope]);
    $event = $this->eventDispatcher->dispatch('social_auth_facebook.scope', $e);
    $final_scope = $event->getArgument('scope');

    // Generate and return the URL where we should redirect the user.
    return $login_helper->getLoginUrl($return_url, $final_scope);
  }

}
