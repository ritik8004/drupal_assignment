<?php

namespace Drupal\rcs_placeholders\EventSubscriber;

use Drupal\rcs_placeholders\Service\RcsPhEnrichmentHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rcs_placeholders\Event\RcsPhPathProcessorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides a path processor subscriber for rcs categories.
 */
class RcsPhListingPathProcessorEventSubscriber implements EventSubscriberInterface {

  /**
   * Enrichment helper.
   *
   * @var \Drupal\rcs_placeholders\Service\RcsPhEnrichmentHelper
   */
  protected $enrichmentHelper;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs an AlshayaRcsPhListingPathProcessorEventSubscriber object.
   */
  public function __construct(
    RcsPhEnrichmentHelper $enrichment_helper,
    ConfigFactoryInterface $config_factory
  ) {
    $this->enrichmentHelper = $enrichment_helper;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      RcsPhPathProcessorEvent::EVENT_NAME => [
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

    if (!str_starts_with($path, '/' . $category_prefix)
    || (isset($data['isDepartmentPage']) && !$data['isDepartmentPage'])) {
      return;
    }

    // Remove the facets params based of the /-- prefix.
    if (stripos($path, '/--', 0) !== FALSE) {
      $path = substr($path, 0, stripos($path, '/--'));
    }

    $category = $config->get('category.enrichment')
      ? $this->enrichmentHelper->getEnrichedEntity('category', $path)
      : NULL;
    $entityData = NULL;
    $processed_paths = '/taxonomy/term/' . $config->get('category.placeholder_tid');

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
