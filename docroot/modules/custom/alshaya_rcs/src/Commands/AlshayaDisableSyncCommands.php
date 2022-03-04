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
  const OVERRIDE_COMMANDS = [
    "alshaya_acm_product:listing-split-products",
    "alshaya_acm:offline-product-sync",
    "alshaya_acm:sync-products",
    "alshaya_acm_product:listing-aggregate-products",
    "alshaya:generate:attribute:nodes",
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
      throw new \Exception('Use of this command is not allowed in Alshaya V3. Product Sync are automatically done from MDC');
    }
  }

}
