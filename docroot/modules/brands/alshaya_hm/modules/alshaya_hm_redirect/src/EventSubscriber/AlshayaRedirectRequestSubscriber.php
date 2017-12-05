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

    if (strpos($path, '/forwarded/kw') === 0) {
      $langcode = $this->resolveLangcode($event->getRequest());

      // Redirect response will by default be not cached server-side. Still
      // adding a no-cache header to avoid http level caching.
      $response = new RedirectResponse('/' . $langcode . str_replace('/forwarded/kw', '', $path), 302, ['cache-control' => 'no-cache']);

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
    $hmcorp_locale_cookie = $request->cookies->get('HMCORP_locale');

    if (!empty($hmcorp_locale_cookie)) {
      return str_replace('_KW', '', $hmcorp_locale_cookie);
    }
    elseif ($request->cookies->get('alshaya_lang')) {
      return $request->cookies->get('alshaya_lang');
    }
    else {
      return $this->languageManager->getDefaultLanguage()->getId();
    }
  }

}
