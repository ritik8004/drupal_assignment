<?php

namespace Drupal\acq_customer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\user\UserInterface;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Customer Controller.
 */
class CustomerController extends ControllerBase {

  /**
   * The api wrapper.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * CustomerController constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   The api wrapper.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(APIWrapper $api_wrapper, AccountInterface $current_user) {
    $this->apiWrapper = $api_wrapper;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_commerce.api'),
      $container->get('current_user')
    );
  }

  /**
   * Returns the build to the orders display page.
   */
  public function ordersPage(UserInterface $user = NULL) {
    $build = [];

    $orders = $this->apiWrapper->getCustomerOrders($user->getEmail());

    if (empty($orders)) {
      $build['#markup'] = $this->t('You have no orders.');
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

    // By design, only logged in users will be able to access the orders page.
    if ($this->currentUser->isAnonymous()) {
      return AccessResult::forbidden();
    }

    // If user is trying to access another user's orders, check admin perm.
    if ($this->currentUser->id() != $user->id()) {
      return AccessResult::allowedIfHasPermission($this->currentUser, 'access all orders');
    }

    // Check if user has access to view own orders.
    return AccessResult::allowedIfHasPermission($this->currentUser, 'access own orders');
  }

}
