<?php

/**
 * @file
 * Contains all utility function around arrays.
 */

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
function _alshaya_array_insert(array $array, $target_key, $insert_key, $insert_val = NULL, $insert_after = TRUE, $append_on_fail = FALSE) {
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
