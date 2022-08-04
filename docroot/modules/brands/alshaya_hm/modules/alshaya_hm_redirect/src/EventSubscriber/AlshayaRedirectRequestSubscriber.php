<?php

namespace Drupal\alshaya_hm_redirect\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class Alshaya Redirect Request Subscriber.
 */
class AlshayaRedirectRequestSubscriber implements EventSubscriberInterface {

  /**
   * Constant to hold the cookie name set by HM entrance gate.
   */
  public const HMCORP_COOKIE_NAME = 'HMCORP_locale';

  /**
   * Constant to identify redirected urls from HM entrance gate.
   */
  public const HM_REDIRECT_URL_IDENTIFIER = '/^\/forwarded\/(\w*)(\/(\w|\W)*)*$/';

  /**
   * Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Page cache kill service.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Constructs a new AlshayaRedirectRequestSubscriber object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language manager service.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $killSwitch
   *   Page cache kill service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Config factory service.
   */
  public function __construct(LanguageManagerInterface $languageManager,
                              KillSwitch $killSwitch,
                              ConfigFactoryInterface $config) {
    $this->languageManager = $languageManager;
    $this->killSwitch = $killSwitch;
    $this->config = $config->get('alshaya_i18n.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events['kernel.request'] = ['onRequest'];

    return $events;
  }

  /**
   * This method is called whenever the kernel.request event is dispatched.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Response event Object.
   */
  public function onRequest(GetResponseEvent $event) {
    $path = $event->getRequest()->getPathInfo();

    // Check if the url starts with the identifier.
    preg_match(self::HM_REDIRECT_URL_IDENTIFIER, $path, $matches);
    if (!empty($matches)) {
      global $base_url;
      $langcode = $this->resolveLangcode($event->getRequest());

      // We simply build a URL manually instead of using Drupal Url methods as
      // it does not deal properly with edge-cases (trailing slash, language,
      // route, etc). See Git history for previous tentative.
      $new_path = $base_url . '/' . $langcode . preg_replace(self::HM_REDIRECT_URL_IDENTIFIER, '${2}', rtrim($path, '/'));

      // Redirect response will by default be not cached server-side. Still
      // adding a no-cache header to avoid http level caching.
      $response = new RedirectResponse($new_path, 302, ['cache-control' => 'must-revalidate, no-cache, no-store, private']);

      // Avoid page cache for Anonymous requests.
      $this->killSwitch->trigger();
      $response->send();
    }
  }

  /**
   * Helper function to resolve language code based on fallbacks.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request Object.
   *
   * @return string
   *   Language code to be used for redirection.
   */
  public function resolveLangcode(Request $request) {
    $hmcorp_locale_cookie = $request->cookies->get(self::HMCORP_COOKIE_NAME);

    // hmcorp_locale cookie would be of format <langcode>_<country code>.
    if (!empty($hmcorp_locale_cookie)) {
      $hmcorp_locale_cookie_parts = explode('_', $hmcorp_locale_cookie);
      if (isset($hmcorp_locale_cookie_parts[0]) &&
        (in_array($hmcorp_locale_cookie_parts[0], array_keys($this->languageManager->getLanguages())))) {
        return $hmcorp_locale_cookie_parts[0];
      }
    }

    if ($request->cookies->get('alshaya_lang')) {
      return $request->cookies->get('alshaya_lang');
    }
    elseif ($this->config->get('default_langcode')) {
      return $this->config->get('default_langcode');
    }
    else {
      return $this->languageManager->getDefaultLanguage()->getId();
    }
  }

}
