<?php

namespace Drupal\alshaya_i18n\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
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
   * Page cache kill service.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

  /**
   * Constructs a AlshayaI18nRequestSubscriber object.
   *
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $kill_switch
   *   Page cache kill service.
   */
  public function __construct(UrlGeneratorInterface $url_generator,
                              LanguageManagerInterface $language_manager,
                              ConfigFactoryInterface $config,
                              KillSwitch $kill_switch) {
    $this->urlGenerator = $url_generator;
    $this->languageManager = $language_manager;
    $this->config = $config->get('alshaya_i18n.settings');
    $this->killSwitch = $kill_switch;
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

      // Check if we have cookie set and check if value is proper.
      $preferred_lang = $request->cookies->get('alshaya_lang');

      if (!empty($preferred_lang) && isset($languages[$preferred_lang])) {
        $options['language'] = $languages[$preferred_lang];
      }
      else {
        // By default we redirect to the language set in config.
        // Not the site default language.
        $options['language'] = $languages[$this->config->get('default_langcode')];
      }

      $redirect_uri = $this->urlGenerator->generateFromRoute('<front>', [], $options);

      $response = new RedirectResponse($redirect_uri, 302);
      $response->headers->set('cache-control', 'must-revalidate, no-cache, no-store, private');
      $event->setResponse($response);

      // Disable page cache, we want to change the redirect based on cookie.
      $this->killSwitch->trigger();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::REQUEST][] = ['onKernelRequestRedirect', 100];
    return $events;
  }

}
