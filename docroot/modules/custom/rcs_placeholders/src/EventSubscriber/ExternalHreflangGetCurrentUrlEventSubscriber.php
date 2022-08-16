<?php

namespace Drupal\rcs_placeholders\EventSubscriber;

use Drupal\Core\Url;
use Drupal\external_hreflang\Event\ExternalHreflangGetCurrentUrlEvent;
use Drupal\rcs_placeholders\Service\RcsPhPathProcessor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class External Hreflang GetCurrentUrl EventSubscriber.
 *
 * @package Drupal\rcs_placeholders\EventSubscriber
 */
class ExternalHreflangGetCurrentUrlEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ExternalHreflangGetCurrentUrlEvent::EVENT_NAME][] = [
      'onGetCurrentUrlEvent',
      100,
    ];
    return $events;
  }

  /**
   * Provide proper url for the current page if it is an RCS page.
   *
   * @param \Drupal\external_hreflang\Event\ExternalHreflangGetCurrentUrlEvent $event
   *   Event object.
   */
  public function onGetCurrentUrlEvent(ExternalHreflangGetCurrentUrlEvent $event) {
    // Only proceed if the page is an RCS page.
    if (!RcsPhPathProcessor::$entityType) {
      return;
    }

    // By default, the placeholder entity alias would have been fetched and
    // displayed. For eg. for the rcs product node, the path would have been the
    // path prefix, like /buy.
    // So we use the following method to prevent the Drupal routing system from
    // converting the path to the alias value.
    $url = Url::fromUri('base:' . RcsPhPathProcessor::getFullPath(FALSE));
    $event->setCurrentUrl($url);
    $event->stopPropagation();
  }

}
