<?php

/**
 * @file
 * Contains utility functions to process orders and provide related data.
 */

use Drupal\acq_checkout\ACQAddressFormatter;
use Drupal\acq_commerce\SKUInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\UserInterface;

/**
 * Returns orders from cache if available.
 *
 * @param string $email
 *   Email address of the user.
 * @param string $search_key
 *   Key to look for in $_GET for searching.
 * @param string $filter_key
 *   Key to look for in $_GET for filtering.
 *
 * @return array
 *   Array of orders.
 */
function alshaya_acm_customer_get_user_orders($email, $search_key = '', $filter_key = '') {
  $orders = [];

  $cid = 'orders_list_' . $email;

  if ($cache = \Drupal::cache()->get($cid)) {
    $orders = $cache->data;
  }
  else {
    $orders = \Drupal::service('acq_commerce.api')->getCustomerOrders($email);

    // Sort them by default by date.
    usort($orders, function ($a, $b) {
      return $b['created_at'] > $a['created_at'];
    });

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
    $orders = array_filter($orders, function ($order) use ($search) {
      // Search by Order ID.
      if (stripos($order['increment_id'], $search) > -1) {
        return TRUE;
      }

      foreach ($order['items'] as $orderItem) {
        // Search by name.
        if (stripos($orderItem['name'], $search) > -1) {
          return TRUE;
        }
        // Search by SKU.
        elseif (stripos(alshaya_acm_customer_clean_sku($orderItem['sku']), alshaya_acm_customer_clean_sku($search)) > -1) {
          return TRUE;
        }
      }

      return FALSE;
    });
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
 * Clean up sku values by removing spaces and dashes.
 *
 * @param string $sku
 *   The sku string.
 *
 * @return string
 *   Return cleaned string without spaces and dashes.
 */
function alshaya_acm_customer_clean_sku($sku) {
  // Convert whitespaces and underscore to dash.
  $string = preg_replace("/[\s_]/", "-", $sku);
  // Clean up multiple dashes or whitespaces.
  $string = preg_replace("/[\s-]+/", "", $string);
  return $string;
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
  if ($order['status'] == 'payment_failed') {
    return [
      'text' => (new TranslatableMarkup('payment failed'))->render(),
      'class' => 'status-returned status-failed',
      'key' => 'failed',
    ];
  }

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
 * @param array $order
 *   Order array from API.
 *
 * @return array
 *   Processed order summary array.
 */
function alshaya_acm_customer_get_processed_order_summary(array $order) {
  \Drupal::moduleHandler()->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
  $orderRow = [];

  $orderRow['orderId'] = $order['increment_id'];
  $orderRow['orderDate'] = \Drupal::service('date.formatter')->format(strtotime($order['created_at']), 'order_date');

  // We will display the name of first order item.
  $item = reset($order['items']);
  $parent_sku = alshaya_acm_product_get_parent_sku_by_sku($item['sku']);
  if (is_object($parent_sku) && $parent_sku instanceof SKUInterface) {
    $item['name'] = $parent_sku->label();
  }

  $orderRow['name'] = Unicode::truncate($item['name'], 25, FALSE, TRUE);

  // Calculate total items in the order.
  $orderRow['quantity'] = alshaya_acm_customer_get_order_total_quantity($order);

  // Format total to have max 3 decimals as per mockup.
  $orderRow['total'] = alshaya_acm_price_format($order['totals']['grand']);

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

  $orderDetails['contact_no'] = $order['shipping']['address']['phone'];

  $address_formatter = new ACQAddressFormatter();
  $orderDetails['delivery_address'] = $address_formatter->render((object) $order['shipping']['address']);
  $orderDetails['billing_address'] = $address_formatter->render((object) $order['billing']);

  \Drupal::moduleHandler()->loadInclude('alshaya_acm_checkout', 'inc', 'alshaya_acm_checkout.shipping');
  $shippingTerm = alshaya_acm_checkout_load_shipping_method($order['shipping']['method']['carrier_code']);
  $orderDetails['delivery_method'] = $shippingTerm->getName();
  $orderDetails['delivery_charge'] = alshaya_acm_price_format($order['shipping']['method']['amount']);

  $orderDetails['payment_method'] = $order['payment']['method_title'];

  $orderDetails['sub_total'] = alshaya_acm_price_format($order['totals']['sub']);
  $orderDetails['order_total'] = alshaya_acm_price_format($order['totals']['grand']);

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
  $orders = alshaya_acm_customer_get_user_orders($user->getEmail());

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

/**
 * Function to get barcode.
 *
 * @param string $order
 *   Order for which barcode needed.
 *
 * @return array
 *   Build array.
 */
function alshaya_acm_customer_get_barcode($order) {
  $build = '';

  if (isset($order['extension'], $order['extension']['barcode'])) {
    $dom = new DOMDocument();
    @$dom->loadHtml($order['extension']['barcode']);
    $svgs = $dom->getElementsByTagName('svg');

    // We do foreach only to avoid empty value notices, we will have only one.
    foreach ($svgs as $svg) {
      $build = $svg->C14N();
      break;
    }
  }

  return $build;
}

/**
 * Helper function to build render array for Order Detail.
 *
 * @param array $order
 *   Order detail array.
 *
 * @return array
 *   Build array.
 */
function alshaya_acm_customer_build_order_detail(array $order) {
  \Drupal::moduleHandler()->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');

  $products = [];
  foreach ($order['items'] as $item) {
    $product = $item;
    $product['price'] = alshaya_acm_price_format($item['price']);
    $product['total'] = alshaya_acm_price_format($item['ordered'] * $item['price']);
    $product['attributes'] = [];

    // Check if we can find a parent SKU for this to get proper name.
    if ($parentSku = alshaya_acm_product_get_parent_sku_by_sku($item['sku'])) {
      // We will use the parent SKU name for display.
      $product['name'] = $parentSku->label();

      // Try to find attributes to display for this product.
      $product['attributes'] = alshaya_acm_product_get_sku_configurable_values($item['sku']);
    }

    $product['image'] = '';

    // Load the first image.
    $product['image'] = alshaya_acm_get_product_display_image($item['sku'], 'checkout_summary_block_thumbnail');

    $products[] = $product;
  }

  $build = [];

  if ($order['status'] == 'payment_failed') {
    $build['#order_error'] = t('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.');
  }

  $build['#order'] = alshaya_acm_customer_get_processed_order_summary($order);
  $build['#order_details'] = alshaya_acm_customer_get_processed_order_details($order);
  $build['#products'] = $products;
  $build['#products_count'] = count($build['#products']);

  \Drupal::moduleHandler()->loadInclude('alshaya_acm_checkout', 'inc', 'alshaya_acm_checkout.shipping');
  $shippingTerm = alshaya_acm_checkout_load_shipping_method($order['shipping']['method']['carrier_code']);
  $build['#delivery_detail_notice'] = $shippingTerm->get('field_shipping_method_desc')->getString();

  return $build;
}
