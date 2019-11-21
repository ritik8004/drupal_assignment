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
  /** @var \Drupal\alshaya_acm_customer\OrdersManager $manager */
  $manager = \Drupal::service('alshaya_acm_customer.orders_manager');
  $orders = $manager->getOrders($email);

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
  $shipping_method_code = $checkoutOptionsManager->getCleanShippingMethodCode($order['shipping']['method']['carrier_code']);
  $shipping_method_name = isset($order['shipping']['method']['description']) ? $order['shipping']['method']['description'] : '';

  $shippingTerm = $checkoutOptionsManager->loadShippingMethod($shipping_method_code, $shipping_method_name);

  $shipping_amount = $order['shipping']['method']['amount_with_tax'] ?? $order['shipping']['method']['amount'];
  $orderDetails['delivery_charge'] = alshaya_acm_price_format($shipping_amount);

  $orderDetails['delivery_method'] = $shippingTerm->getName();

  // Check if taxonomy term doesn't have proper name and we have description
  // available in API response.
  if (!empty($shipping_method_name) && $shippingTerm->getName() == $shipping_method_code) {
    $orderDetails['delivery_method'] = $shipping_method_name;
  }

  $orderDetails['contact_no'] = \Drupal::service('mobile_number.util')->getFormattedMobileNumber($order['shipping']['address']['telephone']);

  if ($shipping_method_code == $checkoutOptionsManager->getClickandColectShippingMethod()) {
    $cc_config = \Drupal::config('alshaya_click_collect.settings');

    $orderDetails['type'] = 'cc';

    $shipping_assignment = reset($order['extension']['shipping_assignments']);
    $store_code = $shipping_assignment['shipping']['extension_attributes']['store_code'];

    $cc_type = $shipping_assignment['shipping']['extension_attributes']['click_and_collect_type'];

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
    if ($cart_desc = $shippingTerm->get('field_shipping_method_cart_desc')->getString()) {
      $orderDetails['delivery_method'] = t('@shipping_method_name (@shipping_method_description)', [
        '@shipping_method_name' => $orderDetails['delivery_method'],
        '@shipping_method_description' => $cart_desc,
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
  if ($order['payment']['method_code'] !== 'cashondelivery') {
    $billing_address_array = $address_book_manager->getAddressArrayFromMagentoAddress($order['billing']);
    $billing_address_array['telephone'] = $mobile_number_util->getFormattedMobileNumber($billing_address_array['mobile_number']['value']);

    $billing_address = [
      '#theme' => 'alshaya_address',
      '#address' => $billing_address_array,
    ];

    $orderDetails['billing_address'] = render($billing_address);
  }

  if ($order['payment']['method_code'] == 'banktransfer'
    && !empty(array_filter($order['extension']['bank_transfer_instructions']))) {
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

  $payment_method_term = $checkoutOptionsManager->loadPaymentMethod($order['payment']['method_code']);
  $orderDetails['payment_method'] = $payment_method_term->getName();
  $orderDetails['payment_method_code'] = $order['payment']['method_code'];
  $orderDetails['sub_total'] = alshaya_acm_price_format($order['totals']['sub']);
  $orderDetails['discount'] = (float) $order['totals']['discount'] != 0 ? alshaya_acm_price_format($order['totals']['discount']) : NULL;

  if (isset($order['extension']['surcharge_incl_tax']) && $order['extension']['surcharge_incl_tax'] > 0) {
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
  if (!empty($order['payment']) &&
    ($order['payment']['method_code'] === 'knet')) {
    $state = \Drupal::service('tempstore.shared');
    $knet_transac_data = $state->get('knet')->get('knet:' . md5($order['increment_id']));
    $orderDetails['transaction_id'] = $knet_transac_data['transaction_id'];
    $orderDetails['payment_id'] = $knet_transac_data['payment_id'];
    $orderDetails['result_code'] = $knet_transac_data['result_code'];
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
    $orders = alshaya_acm_customer_get_user_orders($user->getEmail());
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

      $product['image'] = '';

      // Load the first image.
      $product['image'] = alshaya_acm_get_product_display_image($item['sku'], '291x288', 'cart');

      $products[] = $product;
    }
  }

  $build = [];

  $build['#order'] = alshaya_acm_customer_get_processed_order_summary($order);
  $build['#order_details'] = alshaya_acm_customer_get_processed_order_details($order);
  $build['#products'] = $products;
  $build['#products_count'] = count($build['#products']);

  /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkoutOptionsManager */
  $checkoutOptionsManager = \Drupal::service('alshaya_acm_checkout.options_manager');
  $shippingTerm = $checkoutOptionsManager->loadShippingMethod($order['shipping']['method']['carrier_code']);
  $build['#delivery_detail_notice'] = $shippingTerm->get('field_shipping_method_desc')->getString();

  return $build;
}
