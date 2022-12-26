<?php

namespace Drupal\alshaya_rcs_listing\EventSubscriber;

use Drupal\rcs_placeholders\Event\RcsPhPathProcessorEvent;
use Drupal\rcs_placeholders\EventSubscriber\RcsPhPathProcessorEventSubscriber;

/**
 * Provides a path processor subscriber for rcs categories.
 */
class AlshayaRcsPhListingPathProcessorEventSubscriber extends RcsPhPathProcessorEventSubscriber {

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      RcsPhPathProcessorEvent::ALTER => [
        ['removeFacets', 11],
      ],
    ];
  }

  /**
   * Removes facet params from the path.
   *
   * @param \Drupal\rcs_placeholders\Event\RcsPhPathProcessorEvent $event
   *   Event object.
   */
  public function removeFacets(RcsPhPathProcessorEvent $event): void {
    $data = $event->getData();
    if (empty($data['path']) || empty($data['fullPath'])) {
      return;
    }

    $path = $data['path'];
    // Remove the facets params based of the /-- prefix.
    if (stripos($path, '/--', 0) !== FALSE) {
      $path = substr($path, 0, stripos($path, '/--'));
      $event->addData('path', $path);
      $event->addData('fullPath', $path);
    }
  }

}
