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
   * Returns data from Environment variable for email.
   *
   * @return string
   *   User Email from environment variable.
   */
  private function getUserEmail() {
    return getenv('user_email');
  }

  /**
   * Returns data from Environment variable for password.
   *
   * @return string
   *   Password from environment variable.
   */
  private function getUserPassword() {
    return getenv('user_password');
  }

  /**
   * Authenticate Drush User.
   *
   * @param string $command
   *   Command for logs.
   */
  public function authenticateDrushUser(string $command) {
    $email = $this->getUserEmail();
    $password = $this->getUserPassword();
    if ($email && $password) {
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
    }
    else {
      $this->drupalLogger->info('Environment variables missing for command: @command.', [
        '@command' => $command,
      ]);
    }

    throw new AccessDeniedHttpException('Please set Environment variables with correct values (user_email and password) to authenticate.');
  }

}
