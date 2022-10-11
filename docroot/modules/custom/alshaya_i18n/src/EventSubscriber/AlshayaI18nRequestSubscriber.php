<?php

namespace Drupal\alshaya_i18n\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

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
   * The renderer object.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a AlshayaI18nRequestSubscriber object.
   *
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(UrlGeneratorInterface $url_generator,
                              LanguageManagerInterface $language_manager,
                              ConfigFactoryInterface $config,
                              RendererInterface $renderer) {
    $this->urlGenerator = $url_generator;
    $this->languageManager = $language_manager;
    $this->config = $config->get('alshaya_i18n.settings');
    $this->renderer = $renderer;
  }

  /**
   * Performs a redirect if the / page is requested.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function onKernelRequestRedirect(GetResponseEvent $event) {
    $request = $event->getRequest();
    $languages = json_encode(array_keys($this->languageManager->getLanguages()));

    if ($request->getRequestUri() === '/') {
      // Prepare a markup to redirect user based on the lang cookie or the
      // default langcode.
      $js = "window.getCookie = function(name) {
          var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
          if (match) return match[2];
        }
        var languages = $languages;
        var default_lang = '" . $this->config->get('default_langcode') . "';
        var preferred_lang = window.getCookie('alshaya_lang');
        try {
          if (!languages.includes(preferred_lang)) {
            preferred_lang = default_lang;
          }
        } catch (err) {
          preferred_lang = default_lang;
        }
        window.location = '/' + preferred_lang + '/';";

      $redirect_script = [
        '#type' => 'html_tag',
        '#tag' => 'script',
        '#value' => $js,
      ];

      $response = new Response($this->renderer->renderPlain($redirect_script));
      $event->setResponse($response);
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
