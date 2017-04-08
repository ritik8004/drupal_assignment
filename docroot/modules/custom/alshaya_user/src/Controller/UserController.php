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

    // Get text from config.
    $text = \Drupal::config('alshaya_user.settings')->get('user_register_complete.value');

    // Use email from session and replace in text.
    $build['#markup'] = str_replace('[email]', $account->getEmail(), $text);

    $build['#cache'] = ['max-age' => 0];

    return $build;
  }

}
