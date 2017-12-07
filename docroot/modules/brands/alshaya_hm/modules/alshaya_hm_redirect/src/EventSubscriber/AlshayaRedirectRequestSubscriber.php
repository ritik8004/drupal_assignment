<?php

namespace Drupal\alshaya_hm_redirect\EventSubscriber;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class AlshayaRedirectRequestSubscriber.
 */
class AlshayaRedirectRequestSubscriber implements EventSubscriberInterface {

  /**
   * Constant to hold the cookie name set by HM entrance gate.
   */
  const HMCORP_COOKIE_NAME = 'HMCORP_locale';

  /**
   * Constant to identify redirected urls from HM entrance gate.
   */
  const HM_REDIRECT_URL_IDENTIFIER = '/^\/forwarded\/(\w*)(\/(\w|\W)*)*$/';

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
   * Constructs a new AlshayaRedirectRequestSubscriber object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language manager service.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $killSwitch
   *   Page cache kill service.
   */
  public function __construct(LanguageManagerInterface $languageManager, KillSwitch $killSwitch) {
    $this->languageManager = $languageManager;
    $this->killSwitch = $killSwitch;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['kernel.request'] = ['onRequest'];

    return $events;
  }

  /**
   * This method is called whenever the kernel.request event is dispatched.
   *
   * @param GetResponseEvent $event
   *   Response event Object.
   */
  public function onRequest(GetResponseEvent $event) {
    $path = $event->getRequest()->getPathInfo();

    // Check if the url starts with the identifier.
    preg_match(self::HM_REDIRECT_URL_IDENTIFIER, $path, $matches);
    if (!empty($matches)) {
      $langcode = $this->resolveLangcode($event->getRequest());

      // Redirect response will by default be not cached server-side. Still
      // adding a no-cache header to avoid http level caching.
      $response = new RedirectResponse('/' . $langcode . preg_replace(self::HM_REDIRECT_URL_IDENTIFIER, '${2}', $path), 302, ['cache-control' => 'no-cache']);

      // Avoid page cache for Anonymous requests.
      $this->killSwitch->trigger();
      $response->send();
    }
  }

  /**
   * Helper function to resolve language code based on fallbacks.
   *
   * @param Request $request
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
    else {
      return $this->languageManager->getDefaultLanguage()->getId();
    }
  }

}
