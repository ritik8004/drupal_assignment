<?php

namespace Drupal\alshaya_rcs_main_menu\Commands;

use Drush\Commands\DrushCommands;
use Drupal\alshaya_rcs_main_menu\Service\AlshayaRcsCategoryDataMigration;

/**
 * Alshaya RCS Category Migrate Commands class.
 */
class AlshayaRcsCategoryMigrate extends DrushCommands {

  /**
   * AlshayaRcsCategoryMigrate constructor.
   *
   * @param \Drupal\alshaya_rcs_main_menu\Service\AlshayaRcsCategoryDataMigration $alshaya_category_migrate
   *   RCS Category Migrate Service.
   */
  public function __construct(AlshayaRcsCategoryDataMigration $alshaya_category_migrate) {
    $this->alshayaCategoryMigrate = $alshaya_category_migrate;
  }

  /**
   * Migrate RCS Category terms.
   *
   * @command alshaya-rcs-category:migrate
   *
   * @aliases arcm,arc-migrate
   *
   * @usage drush arcm
   *   Create Enriched RCS Category terms from Product Category.
   */
  public function migrateRcsCategory() {
    // Set rcs category migrate batch.
    $this->alshayaCategoryMigrate->processProductCategoryMigration();
    drush_backend_batch_process();
    $this->logger()->success(dt('RCS Category migration completed.'));
  }

}
