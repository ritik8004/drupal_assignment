<?php

namespace Drupal\alshaya_product_sync_disable\Commands;

use Drush\Commands\DrushCommands;
use Consolidation\AnnotatedCommand\CommandData;

/**
 * Alshaya Disable Sync Commands class.
 */
class AlshayaDisableSyncCommands extends DrushCommands {

  const OVERRIDE_COMMANDS = [
    "alshaya_acm_product:listing-split-products",
    "alshaya_acm:offline-product-sync",
    "alshaya_acm:sync-products",
    "alshaya_acm:media-missing",
    "alshaya_acm:cleanup-orphan-skus",
    "alshaya_acm_product:listing-aggregate-products",
    "alshaya_acm_product:delete-orphan-product-nodes",
    "alshaya_acm_product:cleanup-node-field-data",
    "alshaya_acm_product:cleanup-sku-field-data",
    "alshaya_acm_product:cleanup-duplicate-skus",
    "alshaya_acm_product:add-media-file-usage",
    "alshaya_acm_product:fix-missing-pathauto",
    "alshaya_acm_product:fix-missing-files",
    "alshaya_acm_product:remove-disabled-products",
    "alshaya_acm_product:sync-single-trnaslation-products",
    "alshaya_acm_product:export-product-data",
    "alshaya:generate:attribute:nodes",
    "alshaya_search_algolia:verify_price",
    "alshaya_search_api:correct-index-data",
    "alshaya_search_api:correct-index-stock-data",
    "alshaya_search_api:index-specified-skus",
    "correct-index-stock-data",
    "alshaya_super_category:product-alias",
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
      throw new \Exception('Use of this command is not allowed in Alshaya V3.');
    }
  }

}
