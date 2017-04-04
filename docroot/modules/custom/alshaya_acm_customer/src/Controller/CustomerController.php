<?php

namespace Drupal\alshaya_acm_customer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\UserInterface;

/**
 * Customer controller to add/override pages for customer.
 */
class CustomerController extends ControllerBase {

  /**
   * Returns the build to the orders list page.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the orders list page is being viewed.
   *
   * @return array
   *   Build array.
   */
  public function listOrders(UserInterface $user = NULL) {
    $build = [];

    // Get account details.
    $account = [];
    $account['first_name'] = $user->get('field_first_name')->getString();
    $account['last_name'] = $user->get('field_last_name')->getString();

    // Get the currency code and position.
    $currencyCode = \Drupal::config('acq_commerce.currency')->get('currency_code');
    $currencyCodePosition = \Drupal::config('acq_commerce.currency')->get('currency_code_position');

    // Get the search form.
    $searchForm = \Drupal::formBuilder()->getForm('Drupal\alshaya_acm_customer\Form\OrderSearchForm');
    $searchForm['form_id']['#printed'] = TRUE;
    $searchForm['form_build_id']['#printed'] = TRUE;

    // Get the orders to display for current user and filter applied.
    $orders = $this->getUserOrders($user);

    // Initialising order details array to array.
    $orderDetails = [];

    $noOrdersFoundMessage = ['#markup' => ''];

    if (empty($orders)) {
      // @TODO: Check the empty result message.
      if ($search = \Drupal::request()->query->get('search')) {
        $noOrdersFoundMessage['#markup'] = $this->t('Your search yielded no results, please try different text in search.');
      }
      else {
        $noOrdersFoundMessage['#markup'] = $this->t('You have no orders.');
      }
    }
    else {
      // Loop through each order and prepare the array for template.
      foreach ($orders as $orderId => $order) {
        $orderRow = [];

        // @TODO: MMCPA-612.
        $orderRow['orderId'] = $orderId;
        // @TODO: MMCPA-612.
        $orderRow['orderDate'] = '30 Nov. 2016 @ 20h55';

        // We will display the name of first order item.
        $item = reset($order['items']);
        $orderRow['name'] = $item['name'];

        // Calculate total items in the order.
        $orderRow['quantity'] = $this->getOrderTotalQuantity($order);

        // Format total to have max 3 decimals as per mockup.
        $orderRow['total'] = number_format($order['totals']['grand'], 3);

        // Calculate status of order based on status of items.
        $orderRow['status'] = $this->getOrderStatus($order);

        $orderDetails[] = $orderRow;
      }
    }

    $build[] = [
      '#theme' => 'user_order_list',
      '#search_form' => $searchForm,
      '#order_details' => $orderDetails,
      '#order_not_found' => $noOrdersFoundMessage,
      '#account' => $account,
      '#currency_code' => $currencyCode,
      '#currency_code_position' => $currencyCodePosition,
      // @TODO: We may want to set it to cache time limit of API call.
      '#cache' => ['max-age' => 0],
    ];

    return $build;
  }

  /**
   * Returns orders from cache if available.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the orders are required.
   *
   * @return array
   *   Array of orders.
   */
  protected function getUserOrders(UserInterface $user) {
    $orders = [];

    $cacheTimeLimit = \Drupal::config('alshaya_acm_customer.orders_config')->get('cache_time_limit');
    $expire = strtotime('+' . $cacheTimeLimit . ' minutes');

    $cid = 'orders_list_' . $user->id();

    if ($cache = \Drupal::cache()->get($cid)) {
      $orders = $cache->data;
    }
    else {
      $orders = \Drupal::service('acq_commerce.api')->getCustomerOrders($user->getEmail());
      \Drupal::cache()->set($cid, $orders, $expire);
    }

    // Search by Order ID, SKU, Name.
    if ($search = \Drupal::request()->query->get('search')) {
      $orders = array_filter($orders, function ($order, $orderId) use ($search) {
        // Search by Order ID.
        if (stripos($orderId, $search) > -1) {
          return TRUE;
        }

        foreach ($order['items'] as $orderItem) {
          // Search by name.
          if (stripos($orderItem['name'], $search) > -1) {
            return TRUE;
          }
          // Search by SKU.
          elseif (stripos($orderItem['sku'], $search) > -1) {
            return TRUE;
          }
        }

        return FALSE;
      }, ARRAY_FILTER_USE_BOTH);
    }

    return $orders;
  }

  /**
   * Apply conditions and get order status.
   *
   * @param array $order
   *   Item array.
   *
   * @return string
   *   Status of order, ensure string can be used directly as class too.
   */
  private function getOrderStatus(array $order) {
    // We support only three status as of now.
    $status = ['pending' => 0, 'delivered' => 0, 'returned' => 0];

    // Check for each item status.
    foreach ($order['items'] as $item) {
      $itemStatus = $this->getOrderItemStatus($item);
      $status[$itemStatus]++;
    }

    // @TODO: Add conditions for partial delivery status - not in MVP1.
    // Check MMCPA-145 comments for more details.
    if ($status['returned'] !== 0) {
      return 'returned';
    }
    elseif ($status['delivered'] !== 0) {
      return 'delivered';
    }

    // Finally if it is neither delivered nor returned, it is pending.
    return 'pending';
  }

  /**
   * Apply conditions and get order item status.
   *
   * @param array $item
   *   Item array.
   *
   * @return string
   *   Status of item, ensure string can be used directly as class too.
   */
  private function getOrderItemStatus(array $item) {
    if (empty($item['shipped']) && empty($item['refunded'])) {
      return 'pending';
    }

    if (empty($item['refunded']) && $item['shipped'] === $item['ordered']) {
      return 'delivered';
    }

    if (empty($item['shipped']) && $item['refunded'] === $item['ordered']) {
      return 'returned';
    }

    // @TODO: Check condition for partial delivery, partial pending.
    // @TODO: Check condition for partial delivery, partial returned.
    return 'pending';
  }

  /**
   * Get total number of items in order.
   *
   * @param array $order
   *   Item array.
   *
   * @return int
   *   Number of total items in the order.
   */
  private function getOrderTotalQuantity(array $order) {
    $total = 0;

    foreach ($order['items'] as $item) {
      $total += $item['ordered'];
    }

    return $total;
  }

}
