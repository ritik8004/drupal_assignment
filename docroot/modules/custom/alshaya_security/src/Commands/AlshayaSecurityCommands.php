<?php

namespace Drupal\alshaya_security\Commands;

use Drush\Commands\DrushCommands;

/**
 * Class Alshaya Security Commands.
 *
 * @package Drupal\alshaya_security\Commands
 */
class AlshayaSecurityCommands extends DrushCommands {

  /**
   * Throws exception when user:login command is used.
   *
   * @hook post-command user:login
   */
  public function postUserLogin() {
    throw new \Exception('Use of this command is not allowed.');
  }

}
