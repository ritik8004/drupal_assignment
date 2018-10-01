<?php

namespace Drupal\alshaya_custom;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base abstract class with helper methods.
 */
abstract class AlshayaDynamicConfigValueBase extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function allowedValuesDescription() {
    $description = '<p>' . $this->t('The possible values this field can contain. Enter one value per line, in the format key|label.');
    $description .= '<br/>' . $this->t('The key is the stored value. The label will be used in displayed values.');
    $description .= '</p>';
    return $description;
  }

  /**
   * Callback for #element_validate.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form for the form this element belongs to.
   *
   * @see \Drupal\Core\Render\Element\FormElement::processPattern()
   */
  public static function validateLabelValues(array $element, FormStateInterface $form_state) {
    $values = static::extractKeyLabelValues($element['#value']);

    if (!is_array($values)) {
      $form_state->setError($element, t('Allowed values list: invalid input.'));
    }
    else {
      $form_state->setValueForElement($element, $values);
    }
  }

  /**
   * Generates a string representation of an array values.
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
  public function arrayValuesToString(array $values) {
    $lines = [];
    foreach ($values as $key => $value) {
      $lines[] = "$key|$value";
    }
    return implode("\n", $lines);
  }

  /**
   * Extracts the values array from the string.
   *
   * @param string $string
   *   The raw string to extract values from.
   *
   * @return array|null
   *   The array of extracted key/value pairs, or NULL if the string is invalid.
   *
   * @see \Drupal\options\Plugin\Field\FieldType\ListItemBase::allowedValuesString()
   */
  public static function extractKeyLabelValues($string) {
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
   * Creates structured array as defined in schema from key-value array.
   *
   * @param array $values
   *   Allowed values were the array key is the 'value' value, the value is
   *   the 'label' value.
   *
   * @return array
   *   Array of items with a 'value' and 'label' key each for the allowed
   *   values.
   *
   * @see \Drupal\options\Plugin\Field\FieldType\ListItemBase::structureAllowedValues()
   */
  public static function valuesToSchemaLikeArray(array $values) {
    $structured_values = [];
    foreach ($values as $value => $label) {
      if (is_array($label)) {
        $label = static::valuesToSchemaLikeArray($label);
      }
      $structured_values[] = [
        'value' => $value,
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
   * @see \Drupal\options\Plugin\Field\FieldType\ListItemBase::simplifyAllowedValues()
   */
  public static function schemaArrayToKeyValue(array $structured_values) {
    $values = [];
    foreach ($structured_values as $item) {
      if (is_array($item['label'])) {
        // Nested elements are embedded in the label.
        $item['label'] = static::schemaArrayToKeyValue($item['label']);
      }
      $values[$item['value']] = $item['label'];
    }
    return $values;
  }

}
