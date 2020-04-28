<?php

namespace Drupal\alshaya_acm_customer\HelperTrait;

/**
 * Trait Orders.
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
    foreach ($order['items'] as $item) {
      if (isset($items[$item['sku']])) {
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

      // Add all other info.
      $items[$item['sku']] = $processed_item + $item;
    }
    $order['items'] = $items;

    $order['coupon'] = $order['coupon_code'] ?? '';

    // Extension.
    $order['extension'] = $order['extension_attributes'];
    unset($order['extension_attributes']);

    // Shipping.
    $order['shipping'] = $order['extension']['shipping_assignments'][0]['shipping'];
    $order['shipping']['address']['extension'] = $order['shipping']['address']['extension_attributes'];
    unset($order['shipping']['address']['extension_attributes']);

    // Billing.
    $order['billing'] = $order['billing_address'];
    unset($order['billing_address']);
    $order['billing']['extension'] = $order['billing']['extension_attributes'];
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

}
