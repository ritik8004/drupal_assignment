<?php

namespace Drupal\alshaya_rcs_super_category\Service;

use Drupal\alshaya_super_category\AlshayaSuperCategoryManager;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\rcs_placeholders\Service\RcsPhPathProcessor;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_config\AlshayaConfigManager;
use Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\rcs_placeholders\Service\RcsPhEnrichmentHelper;

class AlshayaRcsSuperCategoryManager extends AlshayaSuperCategoryManager {

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Enrichment helper.
   *
   * @var \Drupal\rcs_placeholders\Service\RcsPhEnrichmentHelper
   */
  protected $enrichmentHelper;

  /**
   * AlshayaSuperCategoryManager constructor.
   *
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface $product_category_tree
   *   Product category tree.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param \Drupal\alshaya_config\AlshayaConfigManager $alshaya_config_manager
   *   Alshaya config manager.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   * @param \Drupal\rcs_placeholders\Service\RcsPhEnrichmentHelper $enrichment_helper
   *   Enrichment helper.
   */
  public function __construct(
    ProductCategoryTreeInterface $product_category_tree,
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    ModuleHandlerInterface $module_handler,
    AlshayaConfigManager $alshaya_config_manager,
    CurrentPathStack $current_path,
    RcsPhEnrichmentHelper $enrichment_helper
  ) {
    parent::__construct(
      $product_category_tree,
      $config_factory,
      $entity_type_manager,
      $module_handler,
      $alshaya_config_manager
    );
    $this->currentPath = $current_path;
    $this->enrichmentHelper = $enrichment_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultCategoryId() {
    if (!$this->isEnabled()) {
      return NULL;
    }

    $default_category_tid = &drupal_static(__FUNCTION__);

    if (!isset($default_category_tid)) {
      $default_category_tid = 0;

      $status = $this->configFactory->get('alshaya_super_category.settings')->get('status');

      if ($status) {
        $super_categories_terms = $this->productCategoryTree->getCategoryRootTerms();

        if (!empty($super_categories_terms)) {
          $default_category_tid = current($super_categories_terms)['commerce_id'] ?? 0;
        }
      }
    }

    return $default_category_tid;
  }

  /**
   * Get the Super Category Term for current page.
   *
   * @return \Drupal\taxonomy\TermInterface|null
   *   Super Category Term if found.
   */
  public function getCategoryTermFromRoute(): ?TermInterface {
    if (!$this->isEnabled()) {
      return NULL;
    }

    static $term;

    if (isset($term)) {
      return $term;
    }

    $term = $this->productCategoryTree->getCategoryTermFromRoute();

    if (empty($term)) {
      // Get enriched entity by current path.
      // Fallback logic to get first term.
      // If current path matches the term overridden path, we return that term.
      $term = $this->enrichmentHelper->getEnrichedEntityByPath('category', 'internal:'. $this->currentPath->getPath());
     // ksm($term->id());
      if ($term === NULL) {
        ksm("dssdf");
        $categories = $this->productCategoryTree->getCategoryRootTerms();
        $category = reset($categories);
        $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($category['id']);
      }
    }

    return $term;
  }
}
