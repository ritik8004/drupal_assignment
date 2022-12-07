<?php

namespace Drupal\alshaya_security\Service;

use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\user\UserAuthInterface;
use Drupal\user\UserInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class for providing utility function to Authenticate Drush User.
 *
 * @package Drupal\alshaya_security\Service
 */
class DrushUserAuth {

  use LoggerChannelTrait;

  /**
   * User Auth service.
   *
   * @var \Drupal\user\UserAuthInterface
   */
  private $userAuth;

  /**
   * Drupal Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $drupalLogger;

  /**
   * Constructor for DrushUserAuth.
   *
   * @param \Drupal\user\UserAuthInterface $user_auth
   *   User Auth service.
   */
  public function __construct(UserAuthInterface $user_auth) {
    $this->userAuth = $user_auth;
    $this->drupalLogger = $this->getLogger('DrushUserAuth');
  }

  /**
   * Authenticate Drush User.
   *
   * @param string $command
   *   Command for logs.
   * @param string $email
   *   User Email.
   * @param string $password
   *   User Password.
   */
  public function authenticateDrushUser(string $command, string $email, string $password) {
    $user = user_load_by_mail($email);
    if ($user instanceof UserInterface && $user->isActive()) {
      if ($this->userAuth->authenticate($user->getAccountName(), $password)) {
        $this->drupalLogger->info('User with email @email authenticated successfully for command: @command.', [
          '@email' => $user->getEmail(),
          '@command' => $command,
        ]);

        return;
      }

      $this->drupalLogger->info('User with email @email authentication failed for command: @command.', [
        '@email' => $user->getEmail(),
        '@command' => $command,
      ]);
    }
    else {
      $this->drupalLogger->info('User with email: @email either not found or blocked for command: @command.', [
        '@email' => $email,
        '@command' => $command,
      ]);
    }

    throw new AccessDeniedHttpException('Unable to authenticate.');
  }

}
