<?php

namespace Drupal\rcs_placeholders\Graphql;

/**
 * Converts arrays to graphql.
 *
 * @see https://github.com/XAKEPEHOK/ArrayGraphQL/blob/master/src/ArrayGraphQL.php
 */
class ArrayGraphQL {

  /**
   * Converts the arrays.
   *
   * @param array $fields
   *   The array of fields.
   *
   * @return string
   *   The formatted string.
   */
  public static function convert(array $fields): string {
    // Recursive remove field duplicates.
    $fields = self::removeDuplicates($fields);

    // Convert array to json.
    $fields = json_encode($fields, JSON_PRETTY_PRINT);

    // Remove array indexes.
    $fields = preg_replace('~"\d+":\s~', '', $fields);

    // Remove quotes, colons and commas (Original code doesn't remove commas).
    $fields = str_replace(['"', ':', ','], '', $fields);

    // Replace square brackets to curly brackets.
    $fields = str_replace(['[', ']'], ['{', '}'], $fields);

    // Compress (Original code doesn't include this).
    $fields = preg_replace("/(\r?\n?\s+)/", ' ', $fields);

    return $fields;
  }

  /**
   * Removes duplicates.
   *
   * @param array $array
   *   The array of fields.
   *
   * @return array
   *   The cleaned array.
   */
  private static function removeDuplicates(array $array): array {
    $existedKeys = [];
    foreach ($array as $key => $value) {

      $isIndexedKey = preg_match('~^\d+$~', $key);
      $isScalar = is_scalar($value);
      $isArray = is_array($value);
      $isEmpty = empty($value);

      if ($isIndexedKey) {
        if (!$isScalar) {
          // Original code uses a custom exception.
          throw new \Exception('Indexed array values should be scalar', 1);
        }

        if (isset($existedKeys[$value])) {
          unset($array[$key]);
        }
        $existedKeys[$value] = TRUE;
      }
      else {
        if (!$isArray || $isEmpty) {
          // Original code uses a custom exception.
          throw new \Exception('Associative array values should be non-empty arrays', 2);
        }
        $array[$key] = self::removeDuplicates($value);
      }
    }
    return $array;
  }

}
