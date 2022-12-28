<?php

namespace Drupal\alshaya_rcs_super_category\EventSubscriber;

use Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper;
use Drupal\rcs_placeholders\Service\RcsPhEnrichmentHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rcs_placeholders\Event\RcsPhPathProcessorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;

/**
 * Provides a path processor subscriber for rcs super_category.
 */
class AlshayaRcsPhSuperCategoryPathProcessorEventSubscriber implements EventSubscriberInterface {

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
   * Department page helper.
   *
   * @var \Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper
   */
  protected $departmentPageHelper;

  /**
   * Product Category Tree.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  private $productCategoryTree;

  /**
   * Constructs an AlshayaRcsPhSuperCategoryPathProcessorEventSubscriber object.
   *
   * @param \Drupal\rcs_placeholders\Service\RcsPhEnrichmentHelper $enrichment_helper
   *   Enrichment helper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper $alshaya_department_page_helper
   *   Department page helper.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product category tree service.
   */
  public function __construct(
    RcsPhEnrichmentHelper $enrichment_helper,
    ConfigFactoryInterface $config_factory,
    AlshayaDepartmentPageHelper $alshaya_department_page_helper,
    ProductCategoryTree $product_category_tree
    ) {
    $this->enrichmentHelper = $enrichment_helper;
    $this->configFactory = $config_factory;
    $this->departmentPageHelper = $alshaya_department_page_helper;
    $this->productCategoryTree = $product_category_tree;
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      RcsPhPathProcessorEvent::ALTER => [
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
    if (empty($data['path']) || empty($data['fullPath'])) {
      return;
    }

    $part = explode('/', $data['path']);
    $term = $this->productCategoryTree->getTermByPath($part[1]);

    if ($term instanceof TermInterface) {
      $path_parts = explode('/', $data['path']);

      // Remove the super category from path.
      $slug = trim($term->get('field_category_slug')->getString(), '/');
      foreach ($path_parts as $index => $path_part) {
        if ($path_part === $slug) {
          unset($path_parts[$index]);
        }
      }
      $path = implode('/', array_values($path_parts));
      $event->addData('path', $path);
    }

    $department_node = $this->departmentPageHelper->getDepartmentPageNode();
    // Return in case the current page is not a
    // department page.
    if ($department_node) {
      $event->addData('isDepartmentPage', TRUE);
      $event->stopPropagation();
    }
  }

}
