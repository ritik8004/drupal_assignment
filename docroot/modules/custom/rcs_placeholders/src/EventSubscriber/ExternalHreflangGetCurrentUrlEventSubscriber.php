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

    // The route object will have the alias of the placeholder node which is the
    // prefix value, eg. "buy/".
    // So here we fetch the path of the entity to create the url object.
    $url = Url::fromUserInput('/' . RcsPhPathProcessor::$entityPath);
    $event->setCurrentUrl($url);
    $event->stopPropagation();
  }

}
