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

  /**
   * Helper function to get all language <> locale mappings from config.
   *
   * @return array
   *   Array of all language <> locale mappings.
   */
  private static function getLanguages() {
    return \Drupal::config('alshaya_i18n.locale_mappings')->get('mappings');
  }

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
    $languages = array_flip(self::getLanguages());
    return $languages[$langcode] ?? FALSE;
  }

  /**
   * Function to get hreflang lang/country code from langcode.
   *
   * @param string $langcode
   *   Langcode for which we want the locale.
   *
   * @return string
   *   Hreflang lang/country if found, empty string otherwise.
   */
  public static function getHrefLangCountry($langcode) {
    if ($hreflangcode = self::getLocale($langcode)) {
      $hreflangcode = strtolower($hreflangcode);
      $hreflangcode = str_replace('_', '-', $hreflangcode);
      return $hreflangcode;
    }

    return '';
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
    $languages = self::getLanguages();
    return $languages[$locale] ?? FALSE;
  }

}
