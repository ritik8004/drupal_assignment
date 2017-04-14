<?php

namespace Drupal\alshaya_i18n;

/**
 * AlshayaI18nLanguages class.
 *
 * Manage all language actions.
 *
 * @class
 *   AlshayaI18nLanguages.
 */
class AlshayaI18nLanguages {

  private static $languages = [
    'ar_KW' => 'ar',
    'en_US' => 'en',
  ];

  /**
   * Function to get locale from langcode.
   *
   * @param string $langcode
   *   Langcode for which we want the locale.
   *
   * @return bool|mixed
   *   Locale if found, FALSE otherwise.
   */
  public static function getLocale($langcode) {
    $languages = array_flip(self::$languages);
    return isset($languages[$langcode]) ? $languages[$langcode] : FALSE;
  }

  /**
   * Function to get langcode from locale.
   *
   * @param string $locale
   *   Locale for which we want the langcode.
   *
   * @return bool|mixed
   *   Langcode if found, FALSE otherwise.
   */
  public static function getLanguage($locale) {
    return isset(self::$languages[$locale]) ? self::$languages[$locale] : FALSE;
  }

}
