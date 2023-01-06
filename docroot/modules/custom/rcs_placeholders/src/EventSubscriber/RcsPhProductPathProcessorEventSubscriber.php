<?php

namespace Drupal\rcs_placeholders\EventSubscriber;

use Drupal\rcs_placeholders\Event\RcsPhPathProcessorEvent;

/**
 * Provides a path processor subscriber for rcs products.
 */
class RcsPhProductPathProcessorEventSubscriber extends RcsPhPathProcessorEventSubscriber {

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
   * Processes product path.
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
    $product_prefix = $config->get('product.path_prefix');

    if (!str_starts_with($path, '/' . $product_prefix)) {
      return;
    }

    $product = $config->get('product.enrichment')
      ? $this->enrichmentHelper->getEnrichedEntity('product', $full_path)
      : NULL;
    $entityData = NULL;
    $processed_paths = '/node/' . $config->get('product.placeholder_nid');

    if (isset($product)) {
      $entityData = $product->toArray();
      $processed_paths = '/node/' . $product->id();
    }

    $event->setData([
      'entityType' => 'product',
      'entityPath' => substr_replace($path, '', 0, strlen($product_prefix) + 1),
      'entityPathPrefix' => $product_prefix,
      'entityFullPath' => $full_path,
      'processedPaths' => $processed_paths,
      'entityData' => $entityData,
    ]);

    $event->stopPropagation();
  }

}
