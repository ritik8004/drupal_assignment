<?php

namespace Drupal\alshaya_stores_finder\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\text\Plugin\Field\FieldFormatter\TextDefaultFormatter;

/**
 * Plugin implementation of the 'key_value' formatter.
 *
 * @FieldFormatter(
 *   id = "alshaya_stores_key_value",
 *   label = @Translation("Alshaya Stores - Key Value"),
 *   field_types = {
 *     "key_value",
 *     "key_value_long",
 *   },
 *   quickedit = {
 *     "editor" = "plain_text"
 *   }
 * )
 */
class AlshayaStoresKeyValueFormatter extends TextDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Buffer the return value.
    $elements = [];

    // Loop through all items.
    foreach ($items as $delta => $item) {
      // Add the key element to the render array as is.
      $elements[$delta]['key'] = [
        '#markup' => '<span class="key-value-key">' . $item->key . '</span>',
      ];

      // Add the value to the render array.
      $elements[$delta]['value'] = [
        '#markup' => '<span class="key-value-value">' . $item->value . '</span>',
      ];
    }

    return $elements;
  }

}
