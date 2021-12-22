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
    $attribute_values = $object['attr_' . $swatch_attributes[0]];
    if (!empty($attribute_values)) {
      foreach ($object['swatches'] as $key => $value) {
        $object['swatches'][$key]['url'] = $this->preparePrettyPathUrl($object['url'], $swatch_attributes[0], $value['display_label']);
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
