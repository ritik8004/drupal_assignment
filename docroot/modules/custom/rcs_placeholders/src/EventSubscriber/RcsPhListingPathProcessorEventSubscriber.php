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
    $full_path = $data['fullPath'];
    $config = $this->configFactory->get('rcs_placeholders.settings');
    $category_prefix = $config->get('category.path_prefix');
    $placeholder_tid = $config->get('category.placeholder_tid');
    if (!str_starts_with($path, '/' . $category_prefix) || !$placeholder_tid) {
      return;
    }

    $processed_paths = '/taxonomy/term/' . $placeholder_tid;
    $category = $config->get('category.enrichment')
      ? $this->enrichmentHelper->getEnrichedEntity('category', $full_path)
      : NULL;

    $entityData = NULL;
    if (isset($category)) {
      $entityData = $category->toArray();
      $processed_paths = '/taxonomy/term/' . $category->id();
    }

    $event->setData([
      'entityType' => 'category',
      'entityPath' => substr_replace($path, '', 0, strlen($category_prefix) + 1),
      'entityPathPrefix' => $category_prefix,
      'entityFullPath' => $full_path,
      'processedPaths' => $processed_paths,
      'entityData' => $entityData,
    ]);

    $event->stopPropagation();
  }

}
