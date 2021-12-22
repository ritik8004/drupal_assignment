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
    foreach ($swatch_attributes as $attribute) {
      $attribute_values = $object['attr_' . $attribute];
      if (!empty($attribute_values) && !empty($object['swatches'])) {
        foreach ($object['swatches'] as $key => $value) {
          $object['swatches'][$key]['url'] = $this->preparePrettyPathUrl($object['url'], $attribute, $value['display_label']);
        }
        // Use only first swatch attribute for pretty path.
        break;
      }
    }
  }

  /**
   * Get pretty path url.
   *
   * @param string $url
   *   PDP url.
   * @param string $attribute
   *   Swatch attribute.
   * @param string $value
   *   Swatch.
   *
   * @return string
   *   Return the pretty path for pdp.
   */
  public function preparePrettyPathUrl($url, $attribute, $value) {
    $suffix = '.html';
    $prefix = '/-' . $attribute . '-' . preg_replace('/[\s]/', '_', strtolower($value));

    return str_replace($suffix, $prefix . $suffix, $url);
  }

}
