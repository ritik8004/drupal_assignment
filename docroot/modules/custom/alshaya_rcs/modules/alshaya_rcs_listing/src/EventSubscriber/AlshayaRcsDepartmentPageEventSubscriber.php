<?php

namespace Drupal\alshaya_rcs_listing\EventSubscriber;

use Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rcs_placeholders\Event\RcsPhPathProcessorEvent;
use Drupal\rcs_placeholders\EventSubscriber\RcsPhPathProcessorEventSubscriber;

/**
 * Provides a path processor subscriber for rcs categories.
 */
class AlshayaRcsDepartmentPageEventSubscriber extends RcsPhPathProcessorEventSubscriber {


  /**
   * Department page helper.
   *
   * @var \Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper
   */
  protected $departmentPageHelper;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an AlshayaRcsDepartmentPageEventSubscriber object.
   *
   * @param \Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper $alshaya_department_page_helper
   *   Department page helper.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(
    AlshayaDepartmentPageHelper $alshaya_department_page_helper,
    EntityTypeManagerInterface $entity_type_manager,
    ) {
    $this->departmentPageHelper = $alshaya_department_page_helper;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      RcsPhPathProcessorEvent::ALTER => [
        ['onPathProcess', 50],
      ],
    ];
  }

  /**
   * Removes facet params from the path.
   *
   * @param \Drupal\rcs_placeholders\Event\RcsPhPathProcessorEvent $event
   *   Event object.
   */
  public function onPathProcess(RcsPhPathProcessorEvent $event): void {
    $data = $event->getData();
    if (empty($data['path']) || empty($data['fullPath'])) {
      return;
    }

    $department_node = $this->departmentPageHelper->getDepartmentPageNode($data['request']);
    // Return in case the current page is not a
    // department page.
    if (!$department_node) {
      return;
    }

    $department_node_entity = $this->entityTypeManager->getStorage('node')->load($department_node);
    $event->addData('path', $department_node_entity->toUrl()->toString(TRUE)->getGeneratedUrl());
    $event->stopPropagation();
  }

}
