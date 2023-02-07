<?php

namespace Drupal\alshaya_rcs_listing\EventSubscriber;

use Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\rcs_placeholders\Event\RcsPhPathProcessorEvent;
use Drupal\rcs_placeholders\EventSubscriber\RcsPhPathProcessorEventSubscriber;
use Drupal\rcs_placeholders\Service\RcsPhEnrichmentHelper;
use Drupal\taxonomy\TermInterface;

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
   * @param \Drupal\rcs_placeholders\Service\RcsPhEnrichmentHelper $enrichment_helper
   *   Enrichment helper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper $alshaya_department_page_helper
   *   Department page helper.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   Alias manager.
   */
  public function __construct(
    RcsPhEnrichmentHelper $enrichment_helper,
    ConfigFactoryInterface $config_factory,
    AlshayaDepartmentPageHelper $alshaya_department_page_helper,
    EntityTypeManagerInterface $entity_type_manager,
    AliasManagerInterface $alias_manager
    ) {
    $this->enrichmentHelper = $enrichment_helper;
    $this->configFactory = $config_factory;
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

    $path_to_process = $data['fullPath'];
    // If we get the internal Drupal taxonomy path, we fetch the corresponding
    // slug field value for that term since we use the slug value to compare
    // with the slug field of the department page and determine if department
    // page exists for the category.
    if (preg_match('/term\/(\d+)/', $data['fullPath'], $matches)) {
      /** @var \Drupal\taxonomy\TermInterface $term  */
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($matches[1]);
      if ($term instanceof TermInterface && $term->bundle() === 'rcs_category') {
        $path_to_process = $term->get('field_category_slug')->getString();
      }
    }

    $department_nid = $this->departmentPageHelper->getDepartmentPageNode($path_to_process);
    // Return in case the current page is not a
    // department page.
    if (!$department_nid) {
      return;
    }

    // For mobile app API call for deeplink, we need info about the advanced
    // page node object. So we stop here push the path value forward.
    // For web, we need the term data for department page so that we can call
    // the MDC API and fetch the category related data to display in the
    // placeholders.
    if (drupal_static('deeplink_api')) {
      $event->addData('path', $this->aliasManager->getAliasByPath('/node/' . $department_nid, $data['langcode']));
      $event->stopPropagation();
      return;
    }

    // Get the tid of the term which has the same slug value as the department
    // page node.
    /** @var \Drupal\node\NodeInterface $department_node */
    $department_node = $this->entityTypeManager->getStorage('node')->load($department_nid);
    $category_slug = $department_node->get('field_category_slug')->getString();
    $term_query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
    $term_query->condition('field_category_slug', $category_slug);
    $tid = $term_query->execute();
    if (empty($tid)) {
      return;
    }

    $tid = array_pop($tid);

    $category = $this->enrichmentHelper->getEnrichedEntity('category', $category_slug);
    $entityData = NULL;

    if (isset($category)) {
      $entityData = $category->toArray();
    }

    $category_prefix = $this->configFactory->get('rcs_placeholders.settings')
      ->get('category.path_prefix');

    $event->setData([
      'entityType' => 'category',
      'entityPath' => substr_replace('/' . $category_slug, '', 0, strlen($category_prefix) + 1),
      'entityPathPrefix' => $category_prefix,
      'entityFullPath' => $category_slug,
      'processedPaths' => '/' . $category_slug,
      'entityData' => $entityData,
    ]);

    $event->stopPropagation();
  }

}
