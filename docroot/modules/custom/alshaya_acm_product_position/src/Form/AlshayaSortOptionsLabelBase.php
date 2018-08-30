<?php

namespace Drupal\alshaya_acm_product_position\Form;

use Drupal\Core\Form\ConfigFormBase;

/**
 * Base abstract class with helper methods.
 */
abstract class AlshayaSortOptionsLabelBase extends ConfigFormBase {

  /**
   * Generates a string representation of an array of 'allowed values'.
   *
   * This string format is suitable for edition in a textarea.
   *
   * @param array $values
   *   An array of values, where array keys are values and array values are
   *   labels.
   *
   * @return string
   *   The string representation of the $values array:
   *    - Values are separated by a carriage return.
   *    - Each value is in the format "value|label" or "value".
   */
  public function allowedValuesString(array $values) {
    $lines = [];
    foreach ($values as $key => $value) {
      $lines[] = "$key|$value";
    }
    return implode("\n", $lines);
  }

  /**
   * Extracts the allowed values array from the allowed_values element.
   *
   * @param string $string
   *   The raw string to extract values from.
   *
   * @return array|null
   *   The array of extracted key/value pairs, or NULL if the string is invalid.
   *
   * @see \Drupal\options\Plugin\Field\FieldType\ListItemBase::allowedValuesString()
   */
  public static function extractAllowedValues($string) {
    $values = [];

    $list = explode("\n", $string);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');

    $generated_keys = $explicit_keys = FALSE;
    foreach ($list as $text) {
      // Check for an explicit key.
      $matches = [];
      if (preg_match('/(.*)\|(.*)/', $text, $matches)) {
        // Trim key and value to avoid unwanted spaces issues.
        $key = trim($matches[1]);
        $value = trim($matches[2]);
        $explicit_keys = TRUE;
      }
      else {
        return;
      }

      $values[$key] = $value;
    }

    // We generate keys only if the list contains no explicit key at all.
    if ($explicit_keys && $generated_keys) {
      return;
    }

    return $values;
  }

  /**
   * Creates a structured array of allowed values from a key-value array.
   *
   * @param array $values
   *   Allowed values were the array key is the 'value' value, the value is
   *   the 'label' value.
   *
   * @return array
   *   Array of items with a 'value' and 'label' key each for the allowed
   *   values.
   *
   * @see \Drupal\options\Plugin\Field\FieldType\ListItemBase::simplifyAllowedValues()
   */
  public static function structureAllowedValues(array $values) {
    $structured_values = [];
    foreach ($values as $value => $label) {
      if (is_array($label)) {
        $label = static::structureAllowedValues($label);
      }
      $structured_values[] = [
        'value' => static::castAllowedValue($value),
        'label' => $label,
      ];
    }
    return $structured_values;
  }

  /**
   * Simplifies allowed values to a key-value array from the structured array.
   *
   * @param array $structured_values
   *   Array of items with a 'value' and 'label' key each for the allowed
   *   values.
   *
   * @return array
   *   Allowed values were the array key is the 'value' value, the value is
   *   the 'label' value.
   *
   * @see \Drupal\options\Plugin\Field\FieldType\ListItemBase::structureAllowedValues()
   */
  public static function simplifyAllowedValues(array $structured_values) {
    $values = [];
    foreach ($structured_values as $item) {
      if (is_array($item['label'])) {
        // Nested elements are embedded in the label.
        $item['label'] = static::simplifyAllowedValues($item['label']);
      }
      $values[$item['value']] = $item['label'];
    }
    return $values;
  }

  /**
   * Converts a value to the correct type.
   *
   * @param mixed $value
   *   The value to cast.
   *
   * @return mixed
   *   The casted value.
   */
  public static function castAllowedValue($value) {
    return $value;
  }

}
