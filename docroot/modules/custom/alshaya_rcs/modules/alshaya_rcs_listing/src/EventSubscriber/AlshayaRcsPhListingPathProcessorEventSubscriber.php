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
        ['onPathProcess', 9],
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
    if (!str_starts_with($path, '/' . $category_prefix)) {
      return;
    }

    $processed_paths = '/taxonomy/term/' . $config->get('category.placeholder_tid');
    $event->setData([
      'entityType' => 'category',
      'entityPath' => substr_replace($path, '', 0, strlen($category_prefix) + 1),
      'entityPathPrefix' => $category_prefix,
      'entityFullPath' => $full_path,
      'processedPaths' => $processed_paths,
    ]);

    $event->stopPropagation();
  }

}
