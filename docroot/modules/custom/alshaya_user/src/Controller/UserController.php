<?php

namespace Drupal\alshaya_user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Customer controller to add/override pages for customer.
 */
class UserController extends ControllerBase {

  /**
   * Returns the build to registration completed page.
   *
   * @return array
   *   Build array.
   */
  public function registerComplete() {
    // Get data from query string.
    $userDataString = \Drupal::request()->query->get('user');

    // Redirect to home if no value in query string.
    if (empty($userDataString)) {
      return new RedirectResponse(Url::fromRoute('<front>')->toString());
    }

    // Decode that data from query string.
    $userData = json_decode(base64_decode($userDataString), TRUE);

    // Redirect to home if value in query string is invalid.
    if (empty($userData)) {
      return new RedirectResponse(Url::fromRoute('<front>')->toString());
    }

    // Load the user.
    $account = User::load($userData['id']);

    // Check if no user found or user is already active.
    if (empty($account) || $account->isActive()) {
      return new RedirectResponse(Url::fromRoute('<front>')->toString());
    }

    // Clear the status messages set by other modules.
    drupal_get_messages('status');

    $build = [];

    // Use email from session.
    $args = [
      '@email' => $account->getEmail(),
    ];

    $build['#markup'] = $this->t("You're almost done.<br>We've sent a verification email to <a href='mailto:@email'>@email</a>.<br>Clicking on the email confirmation link, lets us know the email address is both valid and yours.<br>It is also your final step in the sign up process.", $args);

    $build['#cache'] = ['max-age' => 0];

    return $build;
  }

}
