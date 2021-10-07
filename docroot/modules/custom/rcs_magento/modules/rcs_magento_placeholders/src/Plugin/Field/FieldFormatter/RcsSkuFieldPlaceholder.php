<?php

namespace Drupal\rcs_magento_placeholders\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Component\Serialization\Json;

/**
 * Provides RCS placeholder for sku fields.
 *
 * @FieldFormatter(
 *   id = "rcs_sku_field_placeholder",
 *   label = @Translation("RCS Field Placeholder"),
 *   field_types = {
 *     "sku"
 *   }
 * )
 */
class RcsSkuFieldPlaceholder extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    $elements = parent::view($items, $langcode);

    // Use RCS Field Placeholder theme.
    $elements['#theme'] = 'field__rcs_placeholder_block';

    // Add data attributes.
    $data = [];
    foreach ($items as $delta => $item) {
      $data[$delta] = $item->value;
    }
    $elements['#content_attributes']['data-param-skus'] = Json::encode($data);

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $values = $items->getValue();
    if (empty($values)) {
      return [];
    }

    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = [$item->value];
    }

    return $elements;
  }
}
