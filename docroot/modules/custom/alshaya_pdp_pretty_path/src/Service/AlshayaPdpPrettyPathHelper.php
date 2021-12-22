<?php

namespace Drupal\alshaya_pdp_pretty_path\Service;

/**
 * Class AlshayaPdpPrettyPathHelper.
 *
 * Pdp pretty path helper.
 *
 * @package Drupal\alshaya_pdp_pretty_path\Helper
 */
class AlshayaPdpPrettyPathHelper {

  /**
   * Get swatch URLs.
   *
   * @param array $object
   *   Algolia object.
   * @param array $swatch_attributes
   *   List of swatch attributes.
   */
  public function prepareAndIndexSwatchUrls(array &$object, array $swatch_attributes) {
    $swatch_urls = [];
    $suffix = '.html';
    foreach ($swatch_attributes as $attribute) {
      $attribute_values = $object['attr_' . $attribute];
      if (!empty($attribute_values) && !empty($object['swatches'])) {
        foreach ($object['swatches'] as $key => $value) {
          $prefix = '/-' . $attribute . '-' . preg_replace('/[\s]/', '_', strtolower($value['display_label']));
          $object['swatches'][$key]['url'] = str_replace($suffix, $prefix . $suffix, $object['url']);
        }
      }
    }
  }

}
