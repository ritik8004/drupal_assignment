<?php

namespace Drupal\alshaya_rcs_listing\EventSubscriber;

use Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\rcs_placeholders\Event\RcsPhPathProcessorEvent;
use Drupal\rcs_placeholders\EventSubscriber\RcsPhPathProcessorEventSubscriber;

/**
 * Provides a path processor subscriber for rcs categories.
 */
class AlshayaRcsDepartmentPagePathProcessorEventSubscriber extends RcsPhPathProcessorEventSubscriber {

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
   * Alias manager interface.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructs an AlshayaRcsDepartmentPagePathProcessorEventSubscriber object.
   *
   * @param \Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper $alshaya_department_page_helper
   *   Department page helper.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   Alias manager.
   */
  public function __construct(
    AlshayaDepartmentPageHelper $alshaya_department_page_helper,
    EntityTypeManagerInterface $entity_type_manager,
    AliasManagerInterface $alias_manager
    ) {
    $this->departmentPageHelper = $alshaya_department_page_helper;
    $this->entityTypeManager = $entity_type_manager;
    $this->aliasManager = $alias_manager;

  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      // This needs to be executed before
      // AlshayaRcsPhListingPathProcessorEventSubscriber which has a priority
      // of 11, so priority 12 is given.
      RcsPhPathProcessorEvent::ALTER => [
        ['onPathProcess', 12],
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

    // Get the actual path alias of the node object.
    $event->addData('path', $this->aliasManager->getAliasByPath('/node/' . $department_node, $data['langcode']));
    $event->stopPropagation();
  }

}
