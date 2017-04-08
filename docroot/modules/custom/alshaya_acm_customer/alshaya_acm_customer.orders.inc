<?php

/**
 * @file
 * Contains utility functions to process orders and provide related data.
 */

use Drupal\acq_checkout\ACQAddressFormatter;
use Drupal\user\UserInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Returns orders from cache if available.
 *
 * @param \Drupal\user\UserInterface $user
 *   User object for which the orders are required.
 * @param string $search_key
 *   Key to look for in $_GET for searching.
 * @param string $filter_key
 *   Key to look for in $_GET for filtering.
 *
 * @return array
 *   Array of orders.
 */
function alshaya_acm_customer_get_user_orders(UserInterface $user, $search_key = '', $filter_key = '') {
  $orders = [];

  $cid = 'orders_list_' . $user->id();

  if ($cache = \Drupal::cache()->get($cid)) {
    $orders = $cache->data;
  }
  else {
    $orders = \Drupal::service('acq_commerce.api')->getCustomerOrders($user->getEmail());

    // Get the cache expiration time based on config value.
    $cacheTimeLimit = \Drupal::config('alshaya_acm_customer.orders_config')->get('cache_time_limit');

    // We can disable caching via config by setting it to zero.
    if ($cacheTimeLimit > 0) {
      $expire = strtotime('+' . $cacheTimeLimit . ' seconds');

      // Store in cache.
      \Drupal::cache()->set($cid, $orders, $expire);
    }
  }

  // Search by Order ID, SKU, Name.
  if ($search_key && $search = \Drupal::request()->query->get($search_key)) {
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

  // Filter order by status.
  if ($filter_key && $filter = \Drupal::request()->query->get($filter_key)) {
    $orders = array_filter($orders, function ($order, $orderId) use ($filter) {
      $status = alshaya_acm_customer_get_order_status($order);
      if ($status['key'] == $filter) {
        return TRUE;
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
function alshaya_acm_customer_get_order_status(array $order) {
  // We support only three status as of now.
  $status = ['pending' => 0, 'delivered' => 0, 'returned' => 0];

  // Check for each item status.
  foreach ($order['items'] as $item) {
    $itemStatus = alshaya_acm_customer_get_order_item_status($item);
    $status[$itemStatus]++;
  }

  // @TODO: Add conditions for partial delivery status - not in MVP1.
  // Check MMCPA-145 comments for more details.
  if ($status['returned'] !== 0) {
    return [
      'text' => (new TranslatableMarkup('returned'))->render(),
      'class' => 'status-returned',
      'key' => 'returned',
    ];
  }
  elseif ($status['delivered'] !== 0) {
    return [
      'text' => (new TranslatableMarkup('delivered'))->render(),
      'class' => 'status-delivered',
      'key' => 'delivered',
    ];
  }

  // Finally if it is neither delivered nor returned, it is pending.
  return [
    'text' => (new TranslatableMarkup('pending'))->render(),
    'class' => 'status-pending',
    'key' => 'pending',
  ];
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
function alshaya_acm_customer_get_order_item_status(array $item) {
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
function alshaya_acm_customer_get_order_total_quantity(array $order) {
  $total = 0;

  foreach ($order['items'] as $item) {
    $total += $item['ordered'];
  }

  return $total;
}

/**
 * Helper function to prepare order summary to pass to template.
 *
 * @param mixed $orderId
 *   Order id.
 * @param array $order
 *   Order array from API.
 *
 * @return array
 *   Processed order summary array.
 */
function alshaya_acm_customer_get_processed_order_summary($orderId, array $order) {
  $orderRow = [];

  // @TODO: MMCPA-612.
  $orderRow['orderId'] = $orderId;
  // @TODO: MMCPA-612.
  $orderRow['orderDate'] = '30 Nov. 2016 @ 20h55';

  // We will display the name of first order item.
  $item = reset($order['items']);
  $orderRow['name'] = $item['name'];

  // Calculate total items in the order.
  $orderRow['quantity'] = alshaya_acm_customer_get_order_total_quantity($order);

  // Format total to have max 3 decimals as per mockup.
  $orderRow['total'] = number_format($order['totals']['grand'], 3);

  // Calculate status of order based on status of items.
  $orderRow['status'] = alshaya_acm_customer_get_order_status($order);

  return $orderRow;
}

/**
 * Helper function to prepare order detail to pass to template.
 *
 * @param array $order
 *   Order array from API.
 *
 * @return array
 *   Processed order detail array.
 */
function alshaya_acm_customer_get_processed_order_details(array $order) {
  $orderDetails = [];

  $orderDetails['delivery_to'] = $order['shipping']['address']['firstname'] . ' ' . $order['shipping']['address']['lastname'];

  // @TODO: MMCPA-641.
  $orderDetails['contact_no'] = '+965 12 34 5679';

  $address_formatter = new ACQAddressFormatter();
  $orderDetails['delivery_address'] = $address_formatter->render((object) $order['shipping']['address']);

  // @TODO: MMCPA-641.
  $orderDetails['delivery_method'] = $order['shipping']['method']['carrier_code'];
  $orderDetails['delivery_charge'] = $order['shipping']['method']['amount'];

  $orderDetails['payment_method'] = $order['payment']['method_title'];

  $orderDetails['sub_total'] = $order['totals']['sub'];
  $orderDetails['order_total'] = $order['totals']['grand'];

  return $orderDetails;
}

/**
 * Helper function to prepare array of all available order status for the user.
 *
 * @param \Drupal\user\UserInterface $user
 *   User object for which the orders are required.
 *
 * @return array
 *   Array of all available order status for the user.
 */
function alshaya_acm_customer_get_available_user_order_status(UserInterface $user) {
  $all_status = [];

  // Get all the orders for the user.
  $orders = alshaya_acm_customer_get_user_orders($user);

  // Loop through all the orders to get status of each.
  foreach ($orders as $order) {
    // Get status of the order.
    $status = alshaya_acm_customer_get_order_status($order);

    // Add the status in array (overriding if already available).
    $all_status[$status['key']] = $status['text'];

  }

  // Sort them by ascending order of key to keep it consistent across languages.
  ksort($all_status);

  return $all_status;
}
