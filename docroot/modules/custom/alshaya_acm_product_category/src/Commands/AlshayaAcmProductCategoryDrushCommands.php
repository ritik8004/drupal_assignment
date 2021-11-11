<?php

namespace Drupal\alshaya_acm_product_category\Commands;

use Drupal\alshaya_acm_product_category\Service\ProductCategorySyncManager;
use Drush\Commands\DrushCommands;

/**
 * Expose drush commands for alshaya_acm_product_category.
 */
class AlshayaAcmProductCategoryDrushCommands extends DrushCommands {

  /**
   * Product Category Sync manager service.
   *
   * @var \Drupal\alshaya_acm_product_category\Commands\ProductCategorySyncManager
   */
  protected $categorySyncManager;

  /**
   * AlshayaAcmProductCategoryDrushCommands constructor.
   *
   * @param \Drupal\alshaya_acm_product_category\Service\ProductCategorySyncManager $category_sync_manager
   *   Product Category Sync manager service.
   */
  public function __construct(ProductCategorySyncManager $category_sync_manager) {
    parent::__construct();
    $this->categorySyncManager = $category_sync_manager;
  }

  /**
   * Removes the categories no longer available in Commerce system.
   *
   * @command alshaya_acm_product_category:remove-orphan-categories
   *
   * @aliases remove-orphan-cats
   *
   * @usage drush remove-orphan-cats
   *   Removes the categories no longer available in Commerce system.
   */
  public function removeOrphanCategories() {
    $this->categorySyncManager->removeOrphanCategories();
  }

}
