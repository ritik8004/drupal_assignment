<?php

namespace Drupal\alshaya_security\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use Drupal\alshaya_security\Service\DrushUserAuth;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;

/**
 * Class Alshaya Security Commands.
 *
 * @package Drupal\alshaya_security\Commands
 */
class AlshayaSecurityCommands extends DrushCommands {

  /**
   * Drush commands to Authenticate.
   */
  public const AUTHENTICATE_COMMANDS = [
    'alshaya_acm:sync-products',
    'acq_sku:sync-products',
    'alshaya_api:sanity-check',
    'alshaya_api:sanity-check-category-mapping',
    'alshaya_api:sanity-check-price',
    'alshaya_api:sanity-check-sku-diff',
    'alshaya_api:sanity-check-status',
    'alshaya_api:sanity-check-stock',
    'alshaya_api:sanity-check-visibility',
    'pm:enable',
    'pm:uninstall',
    'theme:enable',
    'theme:uninstall',
    'config:set',
    'config:delete',
    'config:edit',
    'user:password',
    'php:cli',
  ];

  /**
   * Drush User Auth service.
   *
   * @var \Drupal\alshaya_security\Service\DrushUserAuth
   */
  private $drushUserAuth;

  /**
   * Constructor for AlshayaSecurityCommands.
   *
   * @param \Drupal\alshaya_security\Service\DrushUserAuth $drush_user_auth
   *   Drush User Auth service.
   */
  public function __construct(DrushUserAuth $drush_user_auth) {
    $this->drushUserAuth = $drush_user_auth;
  }

  /**
   * Throws exception when user:login command is used.
   *
   * @hook post-command user:login
   */
  public function postUserLogin() {
    throw new \Exception('Use of this command is not allowed.');
  }

  /**
   * Ensure user is authenticated for executing security drush commands.
   *
   * @hook pre-command *
   *
   * @throws Exception
   */
  public function preCommandAuthenticate(CommandData $commandData) {
    $email = $this->io()->ask('Please enter your mail');
    $password = $this->io()->askHidden('Please enter your password');

    if (empty($email) || empty($password)) {
      throw new UserAbortException();
    }

    $command = $commandData->annotationData()->get('command');
    if (in_array($command, self::AUTHENTICATE_COMMANDS)) {
      $this->drushUserAuth->authenticateDrushUser($command, $email, $password);
    }
    elseif (str_starts_with($command, 'role:')) {
      $this->drushUserAuth->authenticateDrushUser($command, $email, $password);
    }
  }

}
