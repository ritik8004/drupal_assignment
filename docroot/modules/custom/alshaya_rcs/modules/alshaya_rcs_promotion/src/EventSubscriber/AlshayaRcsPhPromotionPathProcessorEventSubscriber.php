<?php

namespace Drupal\alshaya_rcs_promotion\EventSubscriber;

use Drupal\rcs_placeholders\Service\RcsPhEnrichmentHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rcs_placeholders\Event\RcsPhPathProcessorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides a path processor subscriber for rcs promotions.
 */
class AlshayaRcsPhPromotionPathProcessorEventSubscriber implements EventSubscriberInterface {

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
   * Constructs an AlshayaRcsPhPromotionPathProcessorEventSubscriber object.
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
      ? $this->enrichmentHelper->getEnrichedEntity('promotion', $path)
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
