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
    $this->consoleLog(dt('Entered @class', ['@class' => $this::class]));

    $ids = \Drupal::entityQuery('user')
      ->execute();

    foreach ($ids as $id) {
      $user = User::load($id);
      $roles = $user->getRoles();

      $num_roles = is_countable($roles) ? count($roles) : 0;

      // Only if a user has just a single role of authenticated user,
      // we will delete them.
      if (($num_roles == 1) && ($roles[0] == 'authenticated')) {
        $this->consoleLog(dt('Deleting non-administrative user from duplicated site: @id', ['@id' => $id]));
        \Drupal::entityTypeManager()->getStorage('user')->load($id)->delete();
      }
    }
  }

}
