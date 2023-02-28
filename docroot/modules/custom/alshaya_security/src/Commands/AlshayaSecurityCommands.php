<?php

namespace Drupal\alshaya_security\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use Drupal\alshaya_security\Service\DrushUserAuth;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Site\Settings;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;

/**
 * Class Alshaya Security Commands.
 *
 * @package Drupal\alshaya_security\Commands
 */
class AlshayaSecurityCommands extends DrushCommands {

  use LoggerChannelTrait;

  /**
   * Drush commands to Authenticate.
   *
   * We do it only for some commands which are critical and not supposed to
   * be used in scripts.
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
    'sql:cli',
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
  public function postUserLogin(): never {
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

    // Check if the command is executed via cron.
    // We need to by-pass the security check.
    $ssh_user = shell_exec('who -m');
    if (empty($ssh_user)) {
      return;
    }

    $command = $commandData->annotationData()->get('command');

    $this->getLogger('AlshayaSecurityCommands')->info('Command @command executed by SSH User: @user', [
      '@command' => $command,
      '@user' => shell_exec('who -m'),
    ]);

    // Get if any of command is mentioned in settings.
    // let's include them as part of authentication.
    $extra_commands = Settings::get('alshaya_drush_authenticate_commands', []);
    $commands = array_merge(self::AUTHENTICATE_COMMANDS, $extra_commands);

    // Authenticate drush command on if enabled.
    // We do it only for some commands which are critical and not supposed to
    // be used in scripts.
    if (Settings::get('alshaya_drush_authenticate', FALSE)
      &&  in_array($command, $commands)) {
      $email = $this->io()->ask('Please enter your mail');
      $password = $this->io()->askHidden('Please enter your password');

      if (empty($email) || empty($password)) {
        throw new UserAbortException();
      }

      $this->drushUserAuth->authenticateDrushUser($command, $email, $password);
    }
  }

}
