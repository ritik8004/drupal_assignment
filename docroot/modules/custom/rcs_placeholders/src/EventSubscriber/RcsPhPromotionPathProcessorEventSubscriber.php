<?php

namespace Drupal\rcs_placeholders\EventSubscriber;

use Drupal\rcs_placeholders\Event\RcsPhPathProcessorEvent;

/**
 * Provides a path processor subscriber for rcs promotions.
 */
class RcsPhPromotionPathProcessorEventSubscriber extends RcsPhPathProcessorEventSubscriber {

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      RcsPhPathProcessorEvent::ALTER => [
        ['onPathProcess'],
      ],
    ];
  }

  /**
   * Processes promotion path.
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
    $promotion_prefix = $config->get('promotion.path_prefix');

    if (!str_starts_with($path, '/' . $promotion_prefix)) {
      return;
    }

    $promotion = $config->get('promotion.enrichment')
      ? $this->enrichmentHelper->getEnrichedEntity('promotion', $full_path)
      : NULL;
    $entityData = NULL;
    $processed_paths = '/node/' . $config->get('promotion.placeholder_nid');

    if (isset($promotion)) {
      $entityData = $promotion->toArray();
      $processed_paths = '/node/' . $promotion->id();
    }

    $event->setData([
      'entityType' => 'promotion',
      'entityPath' => substr_replace($path, '', 0, strlen($promotion_prefix) + 1),
      'entityPathPrefix' => $promotion_prefix,
      'entityFullPath' => $full_path,
      'processedPaths' => $processed_paths,
      'entityData' => $entityData,
    ]);

    $event->stopPropagation();
  }

}
