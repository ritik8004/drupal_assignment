<?php
namespace Drupal\acq_promotion\Commands;

use Drush\Commands\DrushCommands;

class AcqPromotionCommands extends DrushCommands {

  /**
   * Run a full synchronization of all commerce promotion records.
   *
   * @command acq_promotion:sync-promotions
   *
   * @option types Type of promotions that need to be synced.
   *
   * @validate-module-enabled acq_promotion
   *
   * @aliases acspm,sync-commerce-promotions,sync-options
   *
   * @usage drush acspm
   *   Run a full synchronization of all available promotions.
   * @usage drush acspm --types=cart
   *   Run a full synchronization of all available cart promotions.
   */
  public function syncProductOptions($options = ['types' => NULL]) {
    if ($types = $options['types']) {
      \Drupal::logger('acq_promotion')->notice('Synchronizing all @types commerce promotions, this usually takes some time...', ['@types' => $types]);
      $types = explode(',', $types);
      $types = array_map('trim', $types);
      \Drupal::service('acq_promotion.promotions_manager')->syncPromotions($types);
    }
    else {
      \Drupal::logger('acq_promotion')->notice('Synchronizing all commerce promotions, this usually takes some time...');
      \Drupal::service('acq_promotion.promotions_manager')->syncPromotions();
    }

    \Drupal::logger('acq_promotion')->notice('Promotion sync completed.');
  }

}