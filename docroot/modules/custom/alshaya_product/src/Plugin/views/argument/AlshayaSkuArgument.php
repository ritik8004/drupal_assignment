<?php

namespace Drupal\alshaya_product\Plugin\views\argument;

use Drupal\search_api\Plugin\views\argument\SearchApiStandard;

/**
 * Default implementation of the base argument plugin.
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("alshaya_sku_ignore_whitespaces")
 */
class AlshayaSkuArgument extends SearchApiStandard {

  /**
   * {@inheritdoc}
   *
   * @param bool $force_int
   *   Force coversion to ids.
   */
  protected function unpackArgumentValue($force_int = FALSE) {
    $break = self::breakString($this->argument, $force_int);
    $this->value = $break->value;
    $this->operator = $break->operator;
  }

  /**
   * Helper function to break multiple arguments string into an array.
   *
   * @param string $str
   *   Original string argument.
   * @param bool $force_int
   *   Force conversion to ids.
   *
   * @return object
   *   Transformed object.
   */
  public static function breakString($str, $force_int = FALSE) {
    $operator = NULL;
    $value = [];

    // Determine if the string has 'or' operators (plus signs) or 'and'
    // operators (commas) and split the string accordingly.
    if (preg_match('/^([\w0-9-_\.]+[+ ]+)+[\w0-9-_\.]+$/u', $str)) {
      // The '+' character in a query string may be parsed as ' '.
      $operator = 'or';
      $value = preg_split('/[+]/', $str);
    }
    elseif (preg_match('/^([\w0-9-_\.]+[, ]+)*[\w0-9-_\.]+$/u', $str)) {
      $operator = 'and';
      $value = explode(',', $str);
    }

    // Filter any empty matches (Like from '++' in a string) and reset the
    // array keys. 'strlen' is used as the filter callback so we do not lose
    // 0 values (would otherwise evaluate == FALSE).
    $value = array_values(array_filter($value, 'strlen'));

    if ($force_int) {
      $value = array_map('intval', $value);
    }

    return (object) ['value' => $value, 'operator' => $operator];
  }

}
