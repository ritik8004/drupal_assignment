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
   * Get Overridden URL.
   *
   * @param string $url
   *   PDP current url.
   * @param string $attribute_code
   *   Attribute to pretty path.
   * @param string $attribute_value
   *   Attribute value to pretty path.
   *
   * @return string
   *   New URL.
   */
  public function overridePdpUrl($url, $attribute_code, $attribute_value) {
    $prefix = '/-' . $attribute_code . '-' . $attribute_value;
    $suffix = '.html';

    return str_replace($suffix, $prefix . $suffix, $url);
  }

}
