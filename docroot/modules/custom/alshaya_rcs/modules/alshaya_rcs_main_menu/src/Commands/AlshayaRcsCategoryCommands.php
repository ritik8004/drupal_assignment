<?php

namespace Drupal\alshaya_rcs_main_menu\Commands;

use Drush\Commands\DrushCommands;
use Drupal\alshaya_rcs_main_menu\Service\AlshayaRcsCategoryDataMigration;

/**
 * Alshaya RCS Category Migrate Commands class.
 */
class AlshayaRcsCategoryCommands extends DrushCommands {

  /**
   * AlshayaRcsCategoryCommands constructor.
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
   * @options batch_size
   *   The number of rcs category to migrate per batch.
   *
   * @usage drush arcm --batch_size=30
   *   Create Enriched RCS Category terms from Product Category.
   */
  public function migrateRcsCategory($options = ['batch_size' => 50]) {
    // Set rcs category migrate batch.
    $this->alshayaCategoryMigrate->processProductCategoryMigration($options['batch_size']);
    drush_backend_batch_process();
    $this->logger()->success(dt('RCS Category migration completed.'));
  }

}
