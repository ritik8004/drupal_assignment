<?php

namespace Drupal\alshaya_rcs_listing\EventSubscriber;

use Drupal\alshaya_rcs\Service\AlshayaRcsEnrichmentHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rcs_placeholders\Event\RcsPhPathProcessorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides a path processor subscriber for rcs categories.
 */
class AlshayaRcsPhListingPathProcessorEventSubscriber implements EventSubscriberInterface {

  /**
   * Enrichment helper.
   *
   * @var \Drupal\alshaya_rcs\Service\AlshayaRcsEnrichmentHelper
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
    AlshayaRcsEnrichmentHelper $enrichment_helper,
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
    if (empty($data['path']) || empty($data['full_path'])) {
      return;
    }

    $path = $data['path'];
    $full_path = $data['full_path'];
    $config = $this->configFactory->get('rcs_placeholders.settings');
    $category_prefix = $config->get('category.path_prefix');

    // if (!str_starts_with($path, '/' . $category_prefix)
    // || (isset($data['is_department_page']) && !$data['is_department_page'])) {
    //   return;
    // }

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
