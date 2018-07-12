<?php

namespace Drupal\alshaya;

/**
 * Class AlshayaArrayUtils.
 *
 * @package Drupal\alshaya
 */
class AlshayaArrayUtils {

  /**
   * Insert data at position given the target key.
   *
   * @param array $array
   *   Array to process.
   * @param mixed $target_key
   *   Target key to check.
   * @param mixed $insert_key
   *   Key for the new value.
   * @param mixed $insert_val
   *   New value to insert.
   * @param bool $insert_after
   *   Flag to specify if we want to insert after or before.
   * @param bool $append_on_fail
   *   Append if not able to find target key.
   *
   * @return array
   *   Updated array.
   */
  public static function arrayInsert(array $array, $target_key, $insert_key, $insert_val = NULL, $insert_after = TRUE, $append_on_fail = FALSE) {
    $out = [];

    foreach ($array as $key => $value) {
      if ($insert_after) {
        $out[$key] = $value;
      }
      if ($key == $target_key) {
        $out[$insert_key] = $insert_val;
      }
      if (!$insert_after) {
        $out[$key] = $value;
      }
    }

    if (!isset($array[$target_key]) && $append_on_fail) {
      $out[$insert_key] = $insert_val;
    }

    return $out;
  }

  /**
   * Array unique for multi-dimensional array.
   *
   * @param array $array
   *   Multi-dimensional array to make it unique.
   */
  public static function arrayUnique(array &$array) {
    // Make indexed arrays unique.
    $array = (array_values($array) === $array)
      ? array_unique($array)
      : $array;

    foreach ($array as $value) {
      if (is_array($value)) {
        self::arrayUnique($value);
      }
    }
  }

}