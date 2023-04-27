<?php

/**
 * @file
 * Contains utility functions to process orders and provide related data.
 */

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuImagesHelper;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Component\Utility\Unicode;
use Drupal\user\UserInterface;

/**
 * Clean up sku values by removing spaces and dashes.
 *
 * @param string $sku
 *   The sku string.
 *
 * @return string
 *   Return cleaned string without spaces and dashes.
 */
function alshaya_acm_customer_clean_sku(string $sku): string {
  // Convert whitespaces and underscore to dash.
  $sku = preg_replace("/[\s_]/", "-", $sku);
  // Clean up multiple dashes or whitespaces.
  return preg_replace("/[\s-]+/", "", $sku);
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
 * Get order state that can be processing, canceled, closed, complete.
 *
 * @param array $order
 *   Item array.
 *
 * @return string
 *   State of the order.
 */
function alshaya_acm_customer_get_order_state(array $order) {
  return $order['state'] ?? NULL;
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

  // Fetching order currency code if it is available to format the price.
  $order_currency_code = '';
  if (!empty($order['order_currency_code'])) {
    $order_currency_code = $order['order_currency_code'];
  }

  // Format total to have max 3 decimals as per mockup.
  $orderRow['total'] = alshaya_acm_price_format($order['totals']['grand'], [], '', $order_currency_code);

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

  // Invoke the alter hook to allow all modules to modify order details.
  \Drupal::moduleHandler()->alter('alshaya_acm_customer_update_order_summary', $order, $orderRow);

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
  /** @var \Drupal\alshaya_acm_product\DeliveryOptionsHelper $deliveryOptionsHelper */
  $deliveryOptionsHelper = \Drupal::service('alshaya_acm_product.delivery_options_helper');
  $shipping_info = $deliveryOptionsHelper->ifSddEdFeatureEnabled() ? explode(' - ', $order['shipping_description'], 2) : explode(' - ', $order['shipping_description']);
  $orderDetails['delivery_method'] = $shipping_info[0];
  $pudo_pickup = !empty($order['shipping']['extension_attributes']['click_and_collect_type']) ? $order['shipping']['extension_attributes']['click_and_collect_type'] : '';
  $orderDetails['is_pudo_pickup'] = $pudo_pickup === 'pudo_pickup';

  // Fetching order currency code if it is available to format the price.
  $order_currency_code = '';
  if (!empty($order['order_currency_code'])) {
    $order_currency_code = $order['order_currency_code'];
  }

  if ($orderDetails['is_pudo_pickup']) {
    // We get `price_amount` i.e collection charge values like "5" or "Free"
    // so the below check to display accordingly.
    $orderDetails['delivery_charge'] = alshaya_acm_price_format($order['shipping']['extension_attributes']['price_amount'], [], '', $order_currency_code);
  }
  else {
    $orderDetails['delivery_charge'] = alshaya_acm_price_format($order['totals']['shipping'], [], '', $order_currency_code);
  }

  $orderDetails['contact_no'] = \Drupal::service('mobile_number.util')->getFormattedMobileNumber($order['shipping']['address']['telephone']);

  $order['shipping']['method'] = !empty($order['shipping']['method']) ? $order['shipping']['method'] : '';
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

    $shipping_address_array = $address_book_manager->getAddressArrayFromMagentoAddress($shipping_address);
    $shipping_address_array['telephone'] = $mobile_number_util->getFormattedMobileNumber($shipping_address_array['mobile_number']['value']);

    // Add delivery address raw data for return request page.
    $orderDetails['delivery_address_raw'] = $shipping_address_array;
    $delivery_address = [
      '#theme' => 'alshaya_address',
      '#address' => $shipping_address_array,
    ];

    $orderDetails['delivery_address'] = !$order['is_virtual'] ? render($delivery_address) : NULL;
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
  $orderDetails['sub_total'] = alshaya_acm_price_format($order['totals']['sub'], [], '', $order_currency_code);
  $orderDetails['discount'] = (float) $order['totals']['discount'] != 0 ? alshaya_acm_price_format($order['totals']['discount'], [], '', $order_currency_code) : NULL;

  // Add hello member information if available.
  if (isset($order['voucher_discount']) && isset($order['voucher_codes_count'])) {
    // Pass voucher discount amount is available.
    $orderDetails['voucher_discount'] = (float) $order['voucher_discount'] != 0 ? alshaya_acm_price_format($order['voucher_discount'], [], '', $order_currency_code) : NULL;
    // Prepare and pass the voucher discount label.
    $orderDetails['voucher_discount_label'] = t('@count Bonus Voucher', ['@count' => $order['voucher_codes_count']], ['context' => 'hello_member']);
  }

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
        $surcharge_tooltip,
        $order_currency_code
      );
      $orderDetails['surcharge_print']['#markup'] = alshaya_acm_price_format($order['extension']['surcharge_incl_tax'], [], '', $order_currency_code);
    }
  }
  $orderDetails['order_total'] = alshaya_acm_price_format($order['totals']['grand'], [], '', $order_currency_code);

  // Add transaction & payment details to confirmation data in case of KNET.
  $payment_info = [];
  foreach ($order['extension']['payment_additional_info'] ?? [] as $payment_additiona_info) {
    $payment_info[$payment_additiona_info['key']] = $payment_additiona_info['value'];
  }

  // Store payment details.
  $payment_method = $order['payment']['method'];
  $orderDetails['paymentDetails'][$payment_method] = [
    'card_type' => $orderDetails['payment_method'],
    'card_number' => '',
    'payment_type' => $payment_method,
    'weight' => 0,
  ];

  switch ($payment_method) {
    case 'knet':
      $orderDetails['payment']['transactionId'] = $payment_info['transaction_id'];
      $orderDetails['payment']['paymentId'] = $payment_info['payment_id'];
      $orderDetails['payment']['resultCode'] = 'CAPTURED';
      break;

    case 'checkout_com_upapi_knet':
      $orderDetails['payment']['transactionId'] = $payment_info['knet_transaction_id'];
      $orderDetails['payment']['paymentId'] = $payment_info['knet_payment_id'];
      $orderDetails['payment']['resultCode'] = $payment_info['knet_result'];
      // Override payment_type.
      $orderDetails['paymentDetails'][$payment_method]['payment_type'] = 'knet';
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

    case 'checkout_com_upapi':
      // Preparing card details for checkout_com_upapi method.
      // @todo To prepare data for all payment methods.
      // Refer DIG-3519 for more info.
      // Override payment details.
      $card_type = t('@card_type Card', ['@card_type' => $payment_info['card_type']], ['context' => 'online_returns']);
      $orderDetails['paymentDetails'][$payment_method]['card_type'] = $card_type;
      $orderDetails['paymentDetails'][$payment_method]['card_number'] = $payment_info['last4'] ?: '';
      $orderDetails['paymentDetails'][$payment_method]['payment_type'] = $payment_info['scheme'] ?: '';
      break;
  }

  // Add the first time order transaction flag if available.
  if (array_key_exists('order_first_time_transaction', $order['extension'])) {
    $orderDetails['orderFirstTimeTransaction'] = $order['extension']['order_first_time_transaction'];
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
    // Get the required orders for the user.
    $orders = \Drupal::service('alshaya_acm_customer.orders_manager')->getOrders($customer_id, 0);
  }
  catch (\Exception) {
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

  // Fetching order currency code if it is available to format the price.
  $order_currency_code = '';
  if (!empty($order['order_currency_code'])) {
    $order_currency_code = $order['order_currency_code'];
  }

  foreach ($order['items'] as $item) {
    $item['price'] = acq_commerce_get_clean_price($item['price']);
    $product = $item;
    $product['price'] = alshaya_acm_price_format($item['price'], [], '', $order_currency_code);
    $product['total'] = alshaya_acm_price_format($item['ordered'] * $item['price'], [], '', $order_currency_code);
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
        $product['attributes'] = \Drupal::service('alshaya_acm_product.skumanager')->getConfigurableValues($sku);
      }

      // If attribute value available for grouping attribute.
      if (isset($item['attributes']) && !empty($item['attributes'])) {
        $product['attributes'] = array_merge($product['attributes'], $item['attributes']);
      }

      // Load the first image.
      $product['image'] = alshaya_acm_get_product_display_image($item['sku'], SkuImagesHelper::STYLE_PRODUCT_TEASER, 'cart');

      if ($product['image']) {
        $product['image']['#skip_lazy_loading'] = TRUE;
      }

      // If 'applied_rule_ids' is set and price is 0 or 0.01,
      // set the free gift label.
      if (isset($item['applied_rule_ids']) && ($item['price'] == 0 || $item['price'] == SkuManager::FREE_GIFT_PRICE)) {
        $product['free_gift_label'] = t('Free Gift with Purchase');
        $product['price'] = t('Free');
        $product['total'] = '';
      }

      $products[] = $product;
    }

    if ($item['is_item_cancelled']) {
      $cancelled_item = $product;
      $cancelled_item['total'] = alshaya_acm_price_format($product['refund_amount'], [], '', $order_currency_code);
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
  $order['shipping']['method'] = !empty($order['shipping']['method']) ? $order['shipping']['method'] : '';
  if ($order['shipping']['method'] == $checkoutOptionsManager->getClickandColectShippingMethod()) {
    $build['#delivery_detail_notice'] = t('Your order will be available for pickup at the following store');

    // If collection point feature is enabled, update order message.
    if (\Drupal::config('alshaya_spc.collection_points')->get('click_collect_collection_points_enabled')
    && $order['shipping']['extension_attributes']['click_and_collect_type'] === 'pudo_pickup') {
      $build['#delivery_detail_notice'] = t('Your order will be available for pickup at the following collection point');
    }
  }
  else {
    $build['#delivery_detail_notice'] = !$order['is_virtual'] ? t('Your order will be delivered at the following address') : NULL;

    // Prepare online booking information block for my-account order details
    // page.
    // Process online booking information if available and if the order is
    // eligible for the online booking.
    if (isset($order['online_booking_information'])
      && isset($order['online_booking_information']['is_hfd_booking_order'])
      && $order['online_booking_information']['is_hfd_booking_order']
      && alshaya_acm_customer_get_order_state($order) === 'processing') {
      // Check if the appointment is booked with order or failed. To confirm
      // this we will check if the 'appointment_date' and 'start_time' are
      // available of not. If 'appointment_date' and 'start_time' values are
      // available, we will process to display booking details on FE.
      if (!empty($order['online_booking_information']['appointment_date'])
        && !empty($order['online_booking_information']['start_time'])) {
        // For the translations of AM and PM in start and end time of booking.
        // We assume time will always be in format of '10:00 AM' or '11:00 PM',
        // having a space as delimitor. We will translate the AM/PM from the
        // exploded array and implode it again with translated string.
        $start_time = $order['online_booking_information']['start_time'];
        $end_time = $order['online_booking_information']['end_time'];

        // If the current language is not the english, we will fetch and update
        // translations.
        $current_language = \Drupal::languageManager()->getCurrentLanguage()->getId();
        if ($current_language !== 'en') {
          // We can't use the variables in t function so need to fetch the AM/PM
          // translations from the storage directly.
          $storage = \Drupal::service('locale.storage');

          // Translation for AM/PM in start time.
          $start_time_array = explode(' ', $start_time);
          if (count($start_time_array) > 1) {
            $string = $storage->getTranslations([
              'source' => $start_time_array[1],
              'language' => $current_language,
              'context' => 'online_booking',
            ]);
            $start_time_array[1] = (!empty($string)) ? $string[0]->getString() : $start_time_array[1];
            $start_time = implode(' ', $start_time_array);
          }
          // Translation for AM/PM in end time.
          $end_time_array = explode(' ', $end_time);
          if (count($end_time_array) > 1) {
            $string = $storage->getTranslations([
              'source' => $end_time_array[1],
              'language' => $current_language,
              'context' => 'online_booking',
            ]);
            $end_time_array[1] = (!empty($string)) ? $string[0]->getString() : $end_time_array[1];
            $end_time = implode(' ', $end_time_array);
          }
        }

        $build['#online_booking_notice']['booking_info'] = t('Home Delivery: Your order is scheduled on @date between @from_time - @to_time to the below delivery address.', [
          '@date' => \Drupal::service('date.formatter')->format(
            strtotime($order['online_booking_information']['appointment_date']),
            'online_booking',
            'd-M-Y',
          ),
          '@from_time' => $start_time,
          '@to_time' => $end_time,
        ],
        [
          'context' => 'online_booking',
        ]
        );

        // Add a new line separator for contact customer care message to change
        // the schedule.
        $build['#online_booking_notice']['customer_care_info'] = t('Contact customer care (+965 55667788) to change the delivery schedule.', [], ['context' => 'online_booking']);

        // Add a new line separator for another message to inform the time limit
        // of changing the delivery schedule.
        $build['#online_booking_notice']['update_booking_info'] = t('Changes are not allowed three days before the selected schedule.', [], ['context' => 'online_booking']);
      }
      else {
        // If the order is HFD order, i.e. 'is_hfd_booking_order' is set to
        // true and 'appointment_date' isn't available, it means there were
        // some errors in appointment booking during the order placement. We
        // need to show an error message to customer in this case.
        $build['#online_booking_notice']['booking_error'] = t('There was some error while booking. Please contact to customer care.', [], ['context' => 'online_booking']);
      }
    }
  }

  // Inter country transfer.
  // Check if the delivery date is available in extension_attributes
  // pass it for further processing.
  if (!empty($order['extension']['oms_lead_time'])) {
    $build['#order_details']['ict']['date'] = \Drupal::service('date.formatter')->format(
      strtotime($order['extension']['oms_lead_time']),
      'ict',
      'dS M Y',
    );
    // Format labels to display.
    if ($build['#order_details']['type'] === 'cc') {
      $build['#order_details']['ict']['label'] = t('Available in store from', [], ['context' => 'ict']);
    }
    else {
      $build['#order_details']['ict']['label'] = t('Expected Delivery by', [], ['context' => 'ict']);
    }
    $build['#attached']['library'][] = 'alshaya_white_label/inter-country-transfer';
  }

  return $build;
}
