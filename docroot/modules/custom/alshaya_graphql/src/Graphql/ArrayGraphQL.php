<?php

namespace Drupal\alshaya_graphql\Graphql;

/**
 * Converts arrays to graphql.
 *
 * @see https://github.com/XAKEPEHOK/ArrayGraphQL/blob/master/src/ArrayGraphQL.php
 */
class ArrayGraphQL {

  /**
   * Perform character replacement and converts array to graphql query string.
   *
   * @param array $fields
   *   Array to perform the character replacement on.
   *
   * @return string
   *   The string with the replaced characters.
   */
  private static function convertToGraphqlQuery(array $fields) {
    // Convert array to json.
    $fields = json_encode($fields, JSON_PRETTY_PRINT);

    // Remove array indexes.
    $fields = preg_replace('~"\d+":\s~', '', $fields);

    // Remove colons except inside `()`, i.e. filters, variables etc.
    $fields = preg_replace('/:(?!.*?\\))/', '', $fields);

    // Remove [ except inside `()`, i.e. filters, variables etc.
    $fields = preg_replace('/\[(?!.*?\\))/', '{', $fields);

    // Remove ] except inside `()`, i.e. filters, variables etc.
    $fields = preg_replace('/\](?!.*?\\))/', '}', $fields);

    // Remove quotes (Original code doesn't remove commas).
    $fields = str_replace('"', '', $fields);

    // Remove commas except inside `()` (Original code doesn't remove commas).
    $fields = preg_replace('/,(?!.*?\\))/', '', $fields);

    // Compress (Original code doesn't include this).
    $fields = preg_replace("/(\r?\n?\s+)/", ' ', $fields);

    return $fields;
  }

  /**
   * Converts the arrays.
   *
   * @param array $fields
   *   The array of fields.
   *
   * @return array
   *   The formatted string.
   */
  public static function convert(array $fields): array {
    // Recursive remove field duplicates.
    $fields = self::removeDuplicates($fields);

    if (!isset($fields['query'])) {
      return [];
    }

    // Here we convert the array query to a graphql string.
    $fields['query'] = self::convertToGraphqlQuery($fields['query']);
    // We let the variables to be passed as objects so that they can be
    // altered in the javascript side for dynamic parameters that may not be
    // known in the backend.
    $fields['variables'] ??= [];

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
        $array[$key] = $key !== 'variables' ? self::removeDuplicates($value) : $value;
      }
    }
    return $array;
  }

}
