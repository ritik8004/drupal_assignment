<?php

namespace Drupal\alshaya_user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
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
    // Redirect to home is no value in session for mail.
    if (empty($_SESSION['alshaya_user']) || empty($_SESSION['alshaya_user']['last_registered_mail'])) {
      return new RedirectResponse(Url::fromRoute('<front>')->toString());
    }

    // Clear the status messages set by other modules.
    drupal_get_messages('status');

    $build = [];

    // Use email from session.
    $args = [
      '@email' => $_SESSION['alshaya_user']['last_registered_mail'],
    ];

    // Clear session now.
    unset($_SESSION['alshaya_user']['last_registered_mail']);

    $build['#markup'] = $this->t("You're almost done.<br>We've sent a verification email to <a href='mailto:@email'>@email</a>.<br>Clicking on the email confirmation link, lets us know the email address is both valid and yours.<br>It is also your final step in the sign up process.", $args);

    $build['#cache'] = ['max-age' => 0];

    return $build;
  }

}
