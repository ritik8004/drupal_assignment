<?php

namespace Drupal\alshaya_acm_product_category\EventSubscriber;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Cache\CacheableRedirectResponse;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AlshayaAdvancedPageEventSubscriber.
 */
class ProductCategoryRequestSubscriber implements EventSubscriberInterface {

  /**
   * Route matcher.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * ProductCategoryRequestSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route matcher.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager.
   */
  public function __construct(
    RouteMatchInterface $route_match,
    LanguageManagerInterface $language_manager
  ) {
    $this->routeMatch = $route_match;
    $this->languageManager = $language_manager;
  }

  /**
   * Check for term route and throw exception based on field_commerce_status.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function onRequest(GetResponseEvent $event) {
    $request = $event->getRequest();

    // If we've got an exception, nothing to do here.
    if ($request->get('exception') != NULL
        || $this->routeMatch->getRouteName() !== 'entity.taxonomy_term.canonical') {
      return;
    }

    if (($taxonomy_term = $this->routeMatch->getParameter('taxonomy_term')) && $taxonomy_term instanceof TermInterface) {
      if ($taxonomy_term->bundle() !== 'acq_product_category') {
        return;
      }

      if ($taxonomy_term->get('field_commerce_status')->getString() !== '1') {
        throw new NotFoundHttpException();
      }

      if ($taxonomy_term->get('field_override_target_link')->value == '1') {
        $qs = $request->getQueryString();
        if ($qs) {
          $qs = '?' . $qs;
        }

        $langcode = $this->languageManager->getCurrentLanguage()->getId();
        if ($this->languageManager->getDefaultLanguage()->getId() != $langcode
          && $taxonomy_term->hasTranslation($langcode)
        ) {
          $taxonomy_term = $taxonomy_term->getTranslation($langcode);
        }

        $target_link = $taxonomy_term->get('field_target_link')->getString();
        if (UrlHelper::isExternal($target_link)) {
          $event->setResponse(new TrustedRedirectResponse($target_link));
        }
        else {
          $path = Url::fromUri($target_link)->toString();
          $event->setResponse(new CacheableRedirectResponse($request->getUriForPath($path) . $qs));
        }

      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::REQUEST][] = ['onRequest', 30];
    return $events;
  }

}
