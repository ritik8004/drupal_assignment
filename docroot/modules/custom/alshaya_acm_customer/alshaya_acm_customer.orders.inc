<?php

/**
 * @file
 * Contains utility functions to process orders and provide related data.
 */

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Component\Utility\Unicode;
use Drupal\user\UserInterface;

/**
 * Returns orders from cache if available.
 *
 * @param int $customer_id
 *   Commerce customer id.
 * @param string $search_key
 *   Key to look for in $_GET for searching.
 * @param string $filter_key
 *   Key to look for in $_GET for filtering.
 *
 * @return array
 *   Array of orders.
 */
function alshaya_acm_customer_get_user_orders(int $customer_id, $search_key = '', $filter_key = '') {
  /** @var \Drupal\alshaya_acm_customer\OrdersManager $manager */
  $manager = \Drupal::service('alshaya_acm_customer.orders_manager');
  $orders = $manager->getOrders($customer_id);

  if (empty($orders)) {
    return [];
  }

  // Update order items to have unique records only.
  // For configurable products we get it twice (once for parent and once for
  // selected variant).
  foreach ($orders as &$order) {
    $order_items = [];
    foreach ($order['items'] as $item) {
      if (!isset($order_items[$item['sku']])) {
        $order_items[$item['sku']] = $item;
      }
      $sku_entity = SKU::loadFromSku(alshaya_acm_customer_clean_sku($item['sku']));
      if ($sku_entity instanceof SKUInterface) {
        /** @var \Drupal\acq_sku\ProductInfoHelper $productInfoHelper */
        $productInfoHelper = \Drupal::service('acq_sku.product_info_helper');
        $order_items[$item['sku']]['name'] = $productInfoHelper->getTitle($sku_entity, 'basket');
      }

    }
    $order['items'] = $order_items;
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
      if ($status['text'] == $filter) {
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
 *   Status of order, ensures string can be used directly as class too.
 */
function alshaya_acm_customer_get_order_status(array $order) {
  /** @var \Drupal\alshaya_acm_customer\OrdersManager $manager */
  $manager = \Drupal::service('alshaya_acm_customer.orders_manager');
  return $manager->getOrderStatusDetails($order);
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
  $orderRow['orderDate'] = alshaya_master_get_site_date_from_api_date($order['created_at'], 'order_date');

  // We will display the name of first order item.
  $item = reset($order['items']);

  $orderRow['name'] = Unicode::truncate($item['name'], 25, FALSE, TRUE);

  // Calculate total items in the order.
  $orderRow['quantity'] = alshaya_acm_customer_get_order_total_quantity($order);

  // Format total to have max 3 decimals as per mockup.
  $orderRow['total'] = alshaya_acm_price_format($order['totals']['grand']);

  // Calculate status of order based on status of items.
  $orderRow['status'] = alshaya_acm_customer_get_order_status($order);

  // Get loyalty settings.
  $loyalty_settings = alshaya_loyalty_get_validation_settings();

  // Show loyalty information if enabled.
  if ($loyalty_settings['enable_disable_loyalty']) {
    // Get the loyalty card number from order detail.
    $orderRow['loyaltyCard'] = isset($order['extension']['loyalty_card']) ? alshaya_loyalty_format_card_number($order['extension']['loyalty_card'], TRUE) : '';
  }

  $orderRow['cancelled_items_count'] = $order['cancelled_items_count'];
  $orderRow['refund_text'] = ($order['cancelled_items_count'] > 0)
                               ? \Drupal::service('alshaya_acm_customer.orders_manager')->getRefundText($order['payment']['method'])
                               : NULL;

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
  /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
  $address_book_manager = \Drupal::service('alshaya_addressbook.manager');

  /** @var \Drupal\alshaya_master\Decorator\AlshayaMasterMobileUtilDecorator $mobile_number_util */
  $mobile_number_util = \Drupal::service('mobile_number.util');

  $orderDetails = [];

  /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkoutOptionsManager */
  $checkoutOptionsManager = \Drupal::service('alshaya_acm_checkout.options_manager');

  $shipping_info = explode(' - ', $order['shipping_description']);
  $orderDetails['delivery_method'] = $shipping_info[0];

  $orderDetails['delivery_charge'] = alshaya_acm_price_format($order['totals']['shipping']);
  $orderDetails['contact_no'] = \Drupal::service('mobile_number.util')->getFormattedMobileNumber($order['shipping']['address']['telephone']);

  if ($order['shipping']['method'] == $checkoutOptionsManager->getClickandColectShippingMethod()) {
    $cc_config = \Drupal::config('alshaya_click_collect.settings');

    $orderDetails['type'] = 'cc';

    $store_code = $order['shipping']['extension_attributes']['store_code'];
    $cc_type = $order['shipping']['extension_attributes']['click_and_collect_type'];

    /** @var \Drupal\alshaya_stores_finder_transac\StoresFinderUtility $store_utility */
    $store_utility = \Drupal::service('alshaya_stores_finder_transac.utility');

    $orderDetails['view_on_map_link'] = '';

    // Getting store node object from store code.
    if ($store_node = $store_utility->getTranslatedStoreFromCode($store_code)) {
      $orderDetails['store_name'] = $store_node->label();
      $orderDetails['store_address'] = $store_utility->getStoreAddress($store_node);
      $orderDetails['store_phone'] = $store_node->get('field_store_phone')->getString();
      $orderDetails['store_open_hours'] = $store_node->get('field_store_open_hours')->getValue();

      if ($lat_lng = $store_node->get('field_latitude_longitude')->getValue()) {
        $lat = $lat_lng[0]['lat'];
        $lng = $lat_lng[0]['lng'];
        $orderDetails['view_on_map_link'] = 'http://maps.google.com/?q=' . $lat . ',' . $lng;
      }

      $cc_text = ($cc_type == 'reserve_and_collect') ? $cc_config->get('click_collect_rnc') : $store_node->get('field_store_sts_label')->getString();

      if (!empty($cc_text)) {
        $orderDetails['delivery_method'] = t('@shipping_method_name (@shipping_method_description)', [
          '@shipping_method_name' => $orderDetails['delivery_method'],
          '@shipping_method_description' => $cc_text,
        ]);
      }
    }
  }
  else {
    $orderDetails['type'] = 'hd';

    // Check if we have cart description available, display that in parenthesis.
    if (!empty($shipping_info[1])) {
      $orderDetails['delivery_method'] = t('@shipping_method_name (@shipping_method_description)', [
        '@shipping_method_name' => $shipping_info[0],
        '@shipping_method_description' => $shipping_info[1],
      ]);
    }

    $shipping_address = $order['shipping']['address'];

    // Loading address from address book if customer_address_id is available.
    if (!empty($shipping_address['customer_address_id'])) {
      if ($entity = $address_book_manager->getUserAddressByCommerceId($order['shipping']['address']['customer_address_id'])) {
        $shipping_address = $address_book_manager->getAddressFromEntity($entity);
      }
    }

    $shipping_address_array = $address_book_manager->getAddressArrayFromMagentoAddress($shipping_address);
    $shipping_address_array['telephone'] = $mobile_number_util->getFormattedMobileNumber($shipping_address_array['mobile_number']['value']);

    $delivery_address = [
      '#theme' => 'alshaya_address',
      '#address' => $shipping_address_array,
    ];

    $orderDetails['delivery_address'] = render($delivery_address);
  }

  // Don't show Billing Address for CoD payment method.
  if ($order['payment']['method'] !== 'cashondelivery') {
    $billing_address_array = $address_book_manager->getAddressArrayFromMagentoAddress($order['billing']);
    $billing_address_array['telephone'] = $mobile_number_util->getFormattedMobileNumber($billing_address_array['mobile_number']['value']);

    $billing_address = [
      '#theme' => 'alshaya_address',
      '#address' => $billing_address_array,
    ];

    $orderDetails['billing_address'] = render($billing_address);
  }

  $payment_method_term = $checkoutOptionsManager->loadPaymentMethod($order['payment']['method']);
  $orderDetails['payment_method'] = $payment_method_term->getName();
  $orderDetails['payment_method_code'] = $order['payment']['method'];
  $orderDetails['sub_total'] = alshaya_acm_price_format($order['totals']['sub']);
  $orderDetails['discount'] = (float) $order['totals']['discount'] != 0 ? alshaya_acm_price_format($order['totals']['discount']) : NULL;

  if ($order['totals']['surcharge'] > 0) {
    // If surcharge is enabled, only then show.
    if (\Drupal::service('alshaya_acm_checkout.checkout_helper')->isSurchargeEnabled()) {
      $acm_config = \Drupal::config('alshaya_acm_checkout.settings');
      $surcharge_label = $acm_config->get('cod_surcharge_label');
      $surcharge_tooltip = $acm_config->get('cod_surcharge_tooltip');

      $orderDetails['surcharge_label']['#markup'] = $surcharge_label;
      $orderDetails['surcharge']['#markup'] = alshaya_acm_price_format(
        $order['extension']['surcharge_incl_tax'],
        [],
        $surcharge_tooltip
      );
      $orderDetails['surcharge_print']['#markup'] = alshaya_acm_price_format($order['extension']['surcharge_incl_tax']);
    }
  }

  $orderDetails['order_total'] = alshaya_acm_price_format($order['totals']['grand']);

  // Add transaction & payment details to confirmation data in case of KNET.
  foreach ($order['extension']['payment_additional_info'] ?? [] as $payment_additiona_info) {
    $payment_info[$payment_additiona_info['key']] = $payment_additiona_info['value'];
  }

  switch ($order['payment']['method']) {
    case 'knet':
      $orderDetails['payment']['transactionId'] = $payment_info['transaction_id'];
      $orderDetails['payment']['paymentId'] = $payment_info['payment_id'];
      $orderDetails['payment']['resultCode'] = 'CAPTURED';
      break;

    case 'checkout_com_upapi_knet':
      $orderDetails['payment']['transactionId'] = $payment_info['knet_transaction_id'];
      $orderDetails['payment']['paymentId'] = $payment_info['knet_payment_id'];
      $orderDetails['payment']['resultCode'] = $payment_info['knet_result'];
      break;

    case 'checkout_com_upapi_qpay':
      $orderDetails['payment']['transactionId'] = $payment_info['confirmation_id'];
      $orderDetails['payment']['paymentId'] = $payment_info['pun'] ?? '';
      $orderDetails['payment']['resultCode'] = $payment_info['status'];
      break;

    case 'banktransfer':
      if (!empty(array_filter($order['extension']['bank_transfer_instructions']))) {
        $orderDetails['banktransfer_payment_details'] = [
          '#theme' => 'banktransfer_payment_details',
          '#account_number' => $order['extension']['bank_transfer_instructions']['account_number'],
          '#address' => $order['extension']['bank_transfer_instructions']['address'],
          '#bank_name' => $order['extension']['bank_transfer_instructions']['bank_name'],
          '#beneficiary_name' => $order['extension']['bank_transfer_instructions']['beneficiary_name'],
          '#branch' => $order['extension']['bank_transfer_instructions']['branch'],
          '#iban' => $order['extension']['bank_transfer_instructions']['iban'],
          '#swift_code' => $order['extension']['bank_transfer_instructions']['swift_code'],
          '#purpose' => t('Purchase of Goods - @order_id', ['@order_id' => $order['increment_id']]),
        ];
      }
      break;

    case 'checkout_com_upapi_fawry':
      $orderDetails['payment']['referenceNumber'] = $payment_info['reference_number'];
      $orderDetails['payment']['paymentExpiryTime'] = $payment_info['payment_expiry_time'];

      break;
  }

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

  try {
    // Get all the orders for the user.
    $customer_id = (int) $user->get('acq_customer_id')->getString();
    $orders = alshaya_acm_customer_get_user_orders($customer_id);
  }
  catch (\Exception $e) {
    return [];
  }

  // Loop through all the orders to get status of each.
  foreach ($orders as $order) {
    // Get status of the order.
    $status = alshaya_acm_customer_get_order_status($order);

    // Add the status in array (overriding if already available).
    $all_status[$status['text']] = $status['text'];
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
  $cancelled_products = [];

  foreach ($order['items'] as $item) {
    $item['price'] = acq_commerce_get_clean_price($item['price']);
    $product = $item;
    $product['price'] = alshaya_acm_price_format($item['price']);
    $product['total'] = alshaya_acm_price_format($item['ordered'] * $item['price']);
    $product['attributes'] = [];

    $sku = SKU::loadFromSku($item['sku']);

    // If SKU not found in Drupal, skip it.
    if (!$sku instanceof SKUInterface) {
      $product['name'] = $item['name'];
      $product['image'] = '';
      $products[] = $product;
    }
    else {
      /** @var \Drupal\acq_sku\ProductInfoHelper $productInfoHelper */
      $productInfoHelper = \Drupal::service('acq_sku.product_info_helper');
      $product['name'] = $productInfoHelper->getTitle($sku, 'basket');

      // Check if we can find a parent SKU for this to get proper name.
      if (alshaya_acm_product_get_parent_sku_by_sku($sku)) {
        // Try to find attributes to display for this product.
        $product['attributes'] = alshaya_acm_product_get_sku_configurable_values($item['sku']);
      }

      // If attribute value available for grouping attribute.
      if (isset($item['attributes']) && !empty($item['attributes'])) {
        $product['attributes'] = array_merge($product['attributes'], $item['attributes']);
      }

      // Load the first image.
      $product['image'] = alshaya_acm_get_product_display_image($item['sku'], '291x288', 'cart');
      if ($product['image']) {
        $product['image']['#skip_lazy_loading'] = TRUE;
      }

      // If 'applied_rule_ids' is set and price is 0, set the free gift label.
      if (isset($item['applied_rule_ids']) && (int) $item['price'] == 0) {
        $product['free_gift_label'] = t('Free Gift with Purchase');
        $product['price'] = t('Free');
        $product['total'] = '';
      }

      $products[] = $product;
    }

    if ($item['is_item_cancelled']) {
      $cancelled_item = $product;
      $cancelled_item['total'] = alshaya_acm_price_format($product['refund_amount']);
      $cancelled_item['ordered'] = $product['cancelled_quantity'];

      $cancelled_products[] = $cancelled_item;
    }
  }

  $build = [];

  $build['#order'] = alshaya_acm_customer_get_processed_order_summary($order);
  $build['#order_details'] = alshaya_acm_customer_get_processed_order_details($order);
  $build['#products'] = $products;
  $build['#cancelled_products'] = $cancelled_products;
  $build['#products_count'] = count($build['#products']);

  /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkoutOptionsManager */
  $checkoutOptionsManager = \Drupal::service('alshaya_acm_checkout.options_manager');
  if ($order['shipping']['method'] == $checkoutOptionsManager->getClickandColectShippingMethod()) {
    $build['#delivery_detail_notice'] = t('Your order will be available for pickup at the following store');
  }
  else {
    $build['#delivery_detail_notice'] = t('Your order will be delivered at the following address');
  }

  return $build;
}
