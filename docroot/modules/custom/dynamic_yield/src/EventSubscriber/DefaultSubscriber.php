<?php

namespace Drupal\dynamic_yield\EventSubscriber;

use Drupal\Core\Path\PathMatcherInterface;
use Drupal\dynamic_yield\DynamicYieldService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\dynamic_yield\Event\DyPageType;

/**
 * Class DefaultSubscriber.
 */
class DefaultSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\dynamic_yield\DynamicYieldService definition.
   *
   * @var \Drupal\dynamic_yield\DynamicYieldService
   */
  protected $dyservice;

  /**
   * Drupal\Core\Path\PathMatcherInterface definition.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * Constructs a new DefaultSubscriber object.
   */
  public function __construct(PathMatcherInterface $pathMatcher, DynamicYieldService $dynamicYieldService) {
    $this->pathMatcher = $pathMatcher;
    $this->dyservice = $dynamicYieldService;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[DyPageType::DY_SET_CONTEXT][] = ['setContextHomepage', 100];
    $events[DyPageType::DY_SET_CONTEXT][] = ['setContextOthers', 300];
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
      $this->dyservice->setDyContext('HOMEPAGE');
    }
  }

  /**
   * This method is called when the DyPageType.DY_SET_CONTEXT is dispatched.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   */
  public function setContextOthers(Event $event) {
    $this->dyservice->setDyContext('OTHER');
  }

}
