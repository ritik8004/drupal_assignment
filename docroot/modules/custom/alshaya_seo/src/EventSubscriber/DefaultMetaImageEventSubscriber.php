<?php

namespace Drupal\alshaya_seo\EventSubscriber;

use Drupal\alshaya_seo\Event\MetaImageRenderEvent;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Url;

/**
 * Class DefaultMetaImageEventSubscriber.
 *
 * @package Drupal\alshaya_seo\EventSubscriber
 */
class DefaultMetaImageEventSubscriber implements EventSubscriberInterface {

  /**
   * Route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * DefaultMetaImageEventSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route Match Object.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MetaImageRenderEvent::EVENT_NAME][] = ['setDefaultMetaImage', 100];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\alshaya_seo\MetaImageRenderEvent $event
   *   The dispatch event.
   */
  public function setDefaultMetaImage(MetaImageRenderEvent $event) {
    if (!$event->getMetaImage()) {
      $logo = Url::fromUserInput(theme_get_setting('logo.url'), ['absolute' => TRUE])->toString();
      $event->setMetaImage($logo);
    }
  }

}
