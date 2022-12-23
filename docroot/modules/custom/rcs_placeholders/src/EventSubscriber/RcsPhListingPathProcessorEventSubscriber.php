<?php

namespace Drupal\rcs_placeholders\EventSubscriber;

use Drupal\rcs_placeholders\Event\RcsPhPathProcessorEvent;

/**
 * Provides a path processor subscriber for rcs categories.
 */
class RcsPhListingPathProcessorEventSubscriber extends RcsPhPathProcessorEventSubscriber {

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      RcsPhPathProcessorEvent::ALTER => [
        ['onPathProcess', 9],
      ],
    ];
  }

  /**
   * Processes list page path.
   *
   * @param \Drupal\rcs_placeholders\Event\RcsPhPathProcessorEvent $event
   *   Event object.
   */
  public function onPathProcess(RcsPhPathProcessorEvent $event): void {
    $data = $event->getData();
    if (empty($data['path']) || empty($data['fullPath'])) {
      return;
    }

    $path = $data['path'];
    $config = $this->configFactory->get('rcs_placeholders.settings');
    $category_prefix = $config->get('category.path_prefix');

    if (!str_starts_with($path, '/' . $category_prefix)) {
      return;
    }

    $category = $config->get('category.enrichment')
      ? $this->enrichmentHelper->getEnrichedEntity('category', $path)
      : NULL;

    if (isset($category)) {
      $entityData = $category->toArray();
      $processed_paths = '/taxonomy/term/' . $category->id();
      $event->addData('processedPaths', $processed_paths);
      $event->addData('entityData', $entityData);
    }

    $event->stopPropagation();
  }

}
