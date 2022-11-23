<?php

namespace Drupal\alshaya_rcs_super_category\EventSubscriber;

use Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper;
use Drupal\alshaya_rcs\Service\AlshayaRcsEnrichmentHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rcs_placeholders\Event\RcsPhPathProcessorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides a path processor subscriber for rcs super_category.
 */
class AlshayaRcsPhSuperCategoryPathProcessorEventSubscriber implements EventSubscriberInterface {

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
   * Department page helper.
   *
   * @var \Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper
   */
  protected $departmentPageHelper;

  /**
   * Constructs an AlshayaRcsPhSuperCategoryPathProcessorEventSubscriber object.
   *
   * @param \Drupal\alshaya_rcs\Service\AlshayaRcsEnrichmentHelper $enrichment_helper
   *   Enrichment helper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper $department_page_helper
   *   Department page helper.
   */
  public function __construct(
    AlshayaRcsEnrichmentHelper $enrichment_helper,
    ConfigFactoryInterface $config_factory,
    AlshayaDepartmentPageHelper $alshaya_department_page_helper
    ) {
    $this->enrichmentHelper = $enrichment_helper;
    $this->configFactory = $config_factory;
    $this->departmentPageHelper = $alshaya_department_page_helper;
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      RcsPhPathProcessorEvent::EVENT_NAME => [
        ['onPathProcess', 10],
      ],
    ];
  }

  /**
   * Processes super_category path.
   *
   * @param \Drupal\rcs_placeholders\Event\RcsPhPathProcessorEvent $event
   *   Event object.
   */
  public function onPathProcess(RcsPhPathProcessorEvent $event): void {
    $data = $event->getData();
    if (empty($data['path']) || empty($data['full_path'])) {
      return;
    }

    $department_node = $this->departmentPageHelper->getDepartmentPageNode();
    // Return in case the current page is not a
    // department page.
    if ($department_node) {
      $event->addData('is_department_page', TRUE);
    }
  }

}
