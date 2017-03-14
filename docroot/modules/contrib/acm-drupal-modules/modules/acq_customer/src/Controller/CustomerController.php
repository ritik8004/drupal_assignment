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
  public function checkAccess() {
    return AccessResult::allowed()->cachePerPermissions();
  }
}
