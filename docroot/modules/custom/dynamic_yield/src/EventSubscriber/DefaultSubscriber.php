<?php

namespace Drupal\dynamic_yield\EventSubscriber;

use Drupal\Core\Path\PathMatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\dynamic_yield\Event\DyPageType;

/**
 * Class DefaultSubscriber.
 */
class DefaultSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\Core\Path\PathMatcherInterface definition.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * Constructs a new DefaultSubscriber object.
   */
  public function __construct(PathMatcherInterface $pathMatcher) {
    $this->pathMatcher = $pathMatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[DyPageType::DY_SET_CONTEXT][] = ['setContextHomepage', 300];
    $events[DyPageType::DY_SET_CONTEXT][] = ['setContextOthers', 100];
    return $events;
  }

  /**
   * This method is called when the DyPageType.DY_SET_CONTEXT is dispatched.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   */
  public function setContextHomepage(Event $event) {
    if ($this->pathMatcher->isFrontPage()) {
      $event->setDyContext('HOMEPAGE');
    }
  }

  /**
   * This method is called when the DyPageType.DY_SET_CONTEXT is dispatched.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   */
  public function setContextOthers(Event $event) {
    $event->setDyContext('OTHER');
  }

}
