<?php

namespace Drupal\alshaya_rcs\Commands;

use Drush\Commands\DrushCommands;
use Consolidation\AnnotatedCommand\CommandData;

/**
 * Alshaya Disable Sync Commands class.
 */
class AlshayaDisableSyncCommands extends DrushCommands {

  /**
   * Overidden drush commands.
   */
  public const OVERRIDE_COMMANDS = [
    'alshaya_acm:offline-product-sync',
    'alshaya_acm:sync-products',
    'acq_promotion:sync-promotions',
    'acq_promotion:sync-and-process-promotions',
    'acq_sku:sync-products',
    'acq_sku:sync-categories',
    'acq_sku:sync-products-test',
    'bv_attr_val_algolia:index',
    'alshaya_api:sanity-check',
    'alshaya_api:sanity-check-category-mapping',
    'alshaya_api:sanity-check-price',
    'alshaya_api:sanity-check-sku-diff',
    'alshaya_api:sanity-check-status',
    'alshaya_api:sanity-check-stock',
    'alshaya_api:sanity-check-visibility',
  ];

  /**
   * Disable product sync drush commands.
   *
   * @hook pre-command *
   */
  public function preCommand(CommandData $commandData) {
    // Get the current drush cmd and check if its overidden.
    $command = $commandData->annotationData()->get('command');
    if (in_array($command, self::OVERRIDE_COMMANDS)) {
      throw new \Exception('Use of this command is not allowed in Alshaya V3. Syncs are automatically done from MDC.');
    }
  }

}
