<?php

namespace Drupal\alshaya_master\Event;

use Drupal\acsf\Event\AcsfEventHandler;
use Drupal\user\Entity\User;

/**
 * Handles Alshaya-specific scrubbing events performed after site duplication.
 */
class AlshayaAcsfDuplicationScrubUsersHandler extends AcsfEventHandler {

  /**
   * Implements AcsfEventHandler::handle().
   */
  public function handle() {
    drush_print(dt('Entered @class', ['@class' => get_class($this)]));

    $ids = \Drupal::entityQuery('user')
      ->execute();

    foreach ($ids as $id) {
      $user = User::load($id);
      $roles = $user->getRoles();

      $num_roles = count($roles);

      // Only if a user has just a single role of authenticated user,
      // we will delete them.
      if (($num_roles == 1) && ($roles[0] == 'authenticated')) {
        drush_print(dt('Deleting non-administrative user from duplicated site: @id', ['@id' => $id]));
        user_delete($id);
      }
    }
  }

}
