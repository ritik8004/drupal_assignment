<?php

namespace Drupal\alshaya_color_split;

/**
 * Class AlshayaColorSplitConfig.
 *
 * This class is here only to provide a static wrapper and reduce calls
 * to get the configuration from different places.
 *
 * As seen in XHPROF config::get call is proving costly when there is
 * translation added for a value in it.
 *
 * @package Drupal\alshaya_color_split
 */
class AlshayaColorSplitConfig {

  /**
   * Config name constant.
   */
  public const CONFIG_NAME = 'alshaya_color_split.settings';

  /**
   * Get value for specific key from config.
   *
   * @param string $key
   *   Config key.
   *
   * @return array|mixed|null
   *   Value.
   */
  public static function get(string $key) {
    return self::getConfig()->get($key) ?? NULL;
  }

  /**
   * Get the config.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   Read-only config object.
   */
  protected static function getConfig() {
    static $config;

    if (empty($config)) {
      $config = \Drupal::config(self::CONFIG_NAME);
    }

    return $config;
  }

}
