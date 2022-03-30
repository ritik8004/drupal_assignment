<?php

namespace Drupal\alshaya_acm_customer\HelperTrait;

/**
 * Trait Orders methods.
 *
 * @package Drupal\alshaya_acm_customer\HelperTrait
 */
trait Orders {

  /**
   * Wrapper function to get orders query.
   *
   * @param string $field
   *   Field to filter by.
   * @param mixed $value
   *   Value for the filter.
   *
   * @return array
   *   Orders query.
   */
  private function getOrdersQuery(string $field, $value) {
    return [
      'searchCriteria' => [
        'filterGroups' => [
          [
            'filters' => [
              [
                'field' => $field,
                'value' => $value,
                'condition_type' => 'eq',
              ],
            ],
          ],
        ],
        'sortOrders' => [
          ['field' => 'created_at', 'direction' => 'DESC'],
        ],
      ],
    ];
  }

  /**
   * Cleanup order array as expected by Drupal.
   *
   * @param array $order
   *   Order array.
   *
   * @return array
   *   Cleaned up order array.
   */
  private function cleanupOrder(array $order) {
    $order['order_id'] = $order['entity_id'];

    // Customer info.
    $order['firstname'] = $order['customer_firstname'];
    $order['lastname'] = $order['customer_lastname'];
    $order['email'] = $order['customer_email'];

    $items = [];
    $total_cancelled_quantity = 0;

    foreach ($order['items'] as $item) {
      // For virtual products use item_id instead of sku as key.
      if (isset($items[$item['sku']]) && !$item['is_virtual']) {
        continue;
      }

      $processed_item = [
        'type' => (string) ($item['product_type'] ?? ''),
        'price' => ($item['price_incl_tax'] ?? 0),
        'price_without_tax' => ($item['price'] ?? 0),
        'ordered' => (int) ($item['qty_ordered'] ?? 0),
        'shipped' => (int) ($item['qty_shipped'] ?? 0),
        'refunded' => (int) ($item['qty_refunded'] ?? 0),
      ];

      $cancelled_quantity = $this->getCancelledItemsQuantity($item);
      if ($cancelled_quantity) {
        $processed_item['is_item_cancelled'] = TRUE;
        $processed_item['cancelled_quantity'] = $cancelled_quantity;
        $processed_item['refund_amount'] = $item['extension_attributes']['oms_amount_refunded'] ?? 0.0;
        $total_cancelled_quantity += $cancelled_quantity;
      }
      else {
        $processed_item['is_item_cancelled'] = FALSE;
      }

      // If attribute value available for grouping attribute.
      if (isset($item['attributes']) && !empty($item['attributes'])) {
        $processed_item['attributes'] = $item['attributes'];
      }

      // Set key as item_id if product is virtual.
      $item_key = $item['is_virtual'] ? $item['item_id'] : $item['sku'];
      // Add all other info.
      $items[$item_key] = $processed_item + $item;
    }
    $order['items'] = $items;

    $order['coupon'] = $order['coupon_code'] ?? '';

    $order['cancelled_items_count'] = $total_cancelled_quantity;

    // Extension.
    $order['extension'] = $order['extension_attributes'];
    unset($order['extension_attributes']);

    // Shipping.
    $order['shipping'] = $order['extension']['shipping_assignments'][0]['shipping'];
    $order['shipping']['address']['customer_id'] = $order['customer_id'] ?? '';
    unset($order['shipping']['address']['entity_id']);
    unset($order['shipping']['address']['parent_id']);

    $order['shipping']['commerce_address'] = $order['shipping']['address'];
    $order['shipping']['address']['extension'] = $order['shipping']['address']['extension_attributes'] ?? [];
    unset($order['shipping']['address']['extension_attributes']);

    // HFD Online Booking Information. Pass information further if the order is
    // eligible for order booking. These details are used in order helper to
    // process data and display information on front-end.
    if (isset($order['extension']['hfd_booking_information'])
      && isset($order['extension']['hfd_booking_information']['is_hfd_booking_order'])
      && $order['extension']['hfd_booking_information']['is_hfd_booking_order']) {
      $order['hfd_booking_information'] = $order['extension']['hfd_booking_information'];
    }

    // Billing.
    $order['billing'] = $order['billing_address'];
    $order['billing']['customer_id'] = $order['customer_id'] ?? '';
    unset($order['billing']['entity_id']);
    unset($order['billing']['parent_id']);

    $order['billing_commerce_address'] = $order['billing'];
    unset($order['billing_address']);
    $order['billing']['extension'] = $order['billing']['extension_attributes'] ?? [];
    unset($order['billing']['extension_attributes']);

    $order['totals'] = [
      'sub' => ($order['subtotal_incl_tax'] ?? 0),
      'tax' => ($order['tax_amount'] ?? 0),
      'discount' => ($order['discount_amount'] ?? 0),
      'shipping' => ($order['shipping_incl_tax'] ?? 0),
      'surcharge' => ($order['extension']['surcharge_incl_tax'] ?? 0),
      'grand' => ($order['grand_total'] ?? 0),
    ];

    return $order;
  }

  /**
   * Get the cancelled quantity for an item in an order.
   *
   * @param array $item
   *   The item array from the order.
   *
   * @return int
   *   The cancelled quantity.
   */
  private function getCancelledItemsQuantity(array $item) {
    if (isset($item['extension_attributes']['qty_adjustments'])) {
      $adjustments = json_decode($item['extension_attributes']['qty_adjustments'], TRUE);
      return isset($adjustments['qty_stock_shortage']) ? (int) $adjustments['qty_stock_shortage'] : 0;
    }

    return 0;
  }

}
