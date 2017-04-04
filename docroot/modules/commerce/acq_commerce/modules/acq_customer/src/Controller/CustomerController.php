<?php

/**
 * @file:
 * Contains Drupal\acq_customer\Controller\CustomerController;
 */

namespace Drupal\acq_customer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\user\UserInterface;


/**
 * Class CustomerController.
 */
class CustomerController extends ControllerBase {

  /**
   * Returns the build to the orders display page.
   */
  public function ordersPage(UserInterface $user = NULL) {
    $build = [];

    $orders = \Drupal::service('acq_commerce.api')
      ->getCustomerOrders($user->getEmail());


    if (empty($orders)) {
      $build['#markup'] = t('You have no orders.');
      return $build;
    }

    foreach ($orders as $order) {
      $build[] = [
        '#theme' => 'user_order',
        '#order' => $order,
      ];
    }

    return $build;
  }

  /**
   * Checks if user has access to the orders display page.
   */
  public function checkAccess(UserInterface $user = NULL) {
    // Something fishy, it should probably return 404 and not even reach here.
    if (empty($user)) {
      return AccessResult::forbidden();
    }

    // Load the current logged in user details.
    $currentUser = \Drupal::currentUser();

    // By design, only logged in users will be able to access the orders page.
    if ($currentUser->isAnonymous()) {
      return AccessResult::forbidden();
    }

    // If user is trying to access another user's orders, check admin perm.
    if ($currentUser->id() != $user->id()) {
      return AccessResult::allowedIfHasPermission($currentUser, 'access all orders');
    }

    // Check if user has access to view own orders.
    return AccessResult::allowedIfHasPermission($currentUser, 'access own orders');
  }
}
