<?php

namespace Drupal\alshaya_master\Helper;

/**
 * Class Sort Utility.
 *
 * @package Drupal\alshaya_master\Helper
 */
class SortUtility {

  /**
   * Sort an array based on a specific key.
   *
   * @param array $array
   *   Array to sort.
   * @param string $key
   *   Key of the array or object to sort with.
   * @param string $order
   *   Sorting order asc/desc.
   * @param string $key2
   *   Optional secondary key for sorting when first key has equal value.
   * @param string $order2
   *   Sorting order asc/desc.
   */
  public static function sortByMultipleKey(array &$array, $key, $order = 'desc', $key2 = NULL, $order2 = 'asc') {
    usort($array, function ($item_1, $item_2) use ($key, $order, $key2) {
      $val1 = is_array($item_1) ? $item_1[$key] : $item_1->$key;
      $val2 = is_array($item_2) ? $item_2[$key] : $item_2->$key;

      $order = self::sortCheckOrders($val1, $val2, $order);

      // Add check for secondary key.
      if ($key2 && $order === 0) {
        $val3 = is_array($item_1) ? $item_1[$key2] : $item_1->$key2;
        $val4 = is_array($item_2) ? $item_2[$key2] : $item_2->$key2;
        return self::sortCheckOrders($val3, $val4, $order);
      }

      return $order;
    });
  }

  /**
   * Helper function to get order between two values.
   *
   * @param mixed $val1
   *   First value.
   * @param mixed $val2
   *   Second value.
   * @param string $order
   *   Ascending or descending.
   *
   * @return int
   *   Returns the order value - 0, 1 or -1.
   */
  public static function sortCheckOrders($val1, $val2, $order) {
    if ($val1 == $val2) {
      return 0;
    }
    if ($order == 'asc') {
      return ($val1 < $val2) ? -1 : 1;
    }
    elseif ($order == 'desc') {
      return ($val1 > $val2) ? -1 : 1;
    }
  }

}
