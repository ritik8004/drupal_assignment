<?php

namespace Drupal\alshaya_i18n\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Normalizes GET requests performing a redirect if required based on cookie.
 *
 * Only for front page, redirect to user's preferred language instead of
 * default language.
 */
class AlshayaI18nRequestSubscriber implements EventSubscriberInterface {

  /**
   * The URL generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Language manager object.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs a AlshayaI18nRequestSubscriber object.
   *
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config.
   */
  public function __construct(UrlGeneratorInterface $url_generator,
                              LanguageManagerInterface $language_manager,
                              ConfigFactoryInterface $config) {
    $this->urlGenerator = $url_generator;
    $this->languageManager = $language_manager;
    $this->config = $config->get('alshaya_i18n.settings');
  }

  /**
   * Performs a redirect if the / page is requested.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function onKernelRequestRedirect(GetResponseEvent $event) {
    $request = $event->getRequest();

    if ($request->getRequestUri() === '/') {
      $languages = $this->languageManager->getLanguages();

      $options = [];

      // By default we redirect to the language set in config.
      // Not the site default language.
      $options['language'] = $languages[$this->config->get('default_langcode')];

      // Check if we have cookie set.
      $preferred_lang = $request->cookies->get('alshaya_lang');

      if (!empty($preferred_lang)) {
        $langcodes = array_keys($languages);

        // Sanity check, cookies can be edited by anyone.
        if (in_array($preferred_lang, $langcodes)) {
          $options['language'] = $languages[$preferred_lang];
        }
      }

      $redirect_uri = $this->urlGenerator->generateFromRoute('<front>', [], $options);

      // Strip off query parameters added by the route such as a CSRF token.
      if (strpos($redirect_uri, '?') !== FALSE) {
        $redirect_uri = strtok($redirect_uri, '?');
      }

      // Append back the request query string from $_SERVER.
      $query_string = $request->server->get('QUERY_STRING');
      if ($query_string) {
        $redirect_uri .= '?' . $query_string;
      }

      $response = new RedirectResponse($redirect_uri, 302);
      $response->headers->set('cache-control', 'must-revalidate, no-cache, no-store, private');
      $event->setResponse($response);
      // Disable page cache for redirects as that results in unpredictable
      // behavior, e.g. when a trailing ? without query parameters is
      // involved.
      // @todo Remove when https://www.drupal.org/node/2761639 is fixed in
      //   Drupal core.
      \Drupal::service('page_cache_kill_switch')->trigger();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onKernelRequestRedirect', 100];
    return $events;
  }

}
