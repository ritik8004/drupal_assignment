<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Create the entire ACM configure for a given site and environment.
 */

const DRUPAL_ROOT = __DIR__ . '/../../';

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/variables.php';
require_once __DIR__ . '/../../../factory-hooks/environments/mapping.php';

$countries = [
  'kw' => 'Kuwait',
  'sa' => 'Saudi Arabia',
  'ae' => 'United Arab Emirates',
];

$languages = [
  'en' => 'English',
  'ar' => 'Arabic',
];

foreach ($countries as $country_code => $country_name) {
  $magento_data = alshaya_get_commerce_third_party_settings(strtolower($brand_code), $country_code, $stack . strtolower($env));
  if (empty($magento_data)) {
    echo "\nNothing for: " . $brand_name . ' ' . $country_name . ' - ' . $env;
    continue;
  }

  // Create new site for $country_code.
  $site = create_site(
    $brand_name . ' ' . $country_name . ' - ' . $env,
    'Alshaya ' . $brand_name . ' ' . $country_name . ' - ' . $env
  );

  echo "\n" . $site->id;
  echo "\n" . $site->hmac_key;
  echo "\n" . $site->hmac_secret;

  // Create new Magento Auth for $country_code.
  $magento_auth = create_auth(
    'Magento auth - ' . strtolower($brand_code) . strtolower($country_code) . strtolower($env),
    'Magento auth details for ' . ucfirst(strtolower($brand_name)) . ' ' . strtoupper($country_code) . ' - ' . ucfirst(strtolower($env)),
    $site->id,
    $mdc_client_id,
    $mdc_client_secret,
    $mdc_token,
    $mdc_token_secret
  );

  echo "\n" . $magento_auth->id;

  // Create new Drupal Auth for $country_code.
  $drupal_auth = create_auth(
    'Drupal auth - ' . strtolower($brand_code) . strtolower($country_code) . strtolower($env),
    'Drupal auth details for ' . ucfirst(strtolower($brand_name)) . ' ' . strtoupper($country_code) . ' - ' . ucfirst(strtolower($env)),
    $site->id,
    $drupal_client_id,
    $drupal_client_secret
  );

  echo "\n" . $drupal_auth->id;

  foreach ($languages as $lang_code => $lang_name) {
    // Create Magento system for $country_code + $lang_code.
    $magento_system = create_system(
      'Magento system - ' . strtolower($brand_code) . strtolower($country_code) . strtolower($lang_code) . strtolower($env),
      'Magento system details for ' . ucfirst(strtolower($brand_name)) . ' ' . strtoupper($country_code) . ' ' . ucfirst(strtolower($lang_name)) . ' - ' . ucfirst(strtolower($env)),
      $site->id,
      'magento',
      $magento_data['alshaya_api.settings']['magento_host'] . '/' . $magento_data['magento_lang_prefix'][$lang_code] . '/rest/V1/',
      $magento_data['store_id'][$lang_code],
      $magento_auth->id
    );

    echo "\n" . $magento_system->id;

    // Create Drupal system for $country_code + $lang_code.
    $drupal_system = create_system(
      'Drupal system - ' . strtolower($brand_code) . strtolower($country_code) . strtolower($lang_code) . strtolower($env),
      'Drupal system details for ' . ucfirst(strtolower($brand_name)) . ' ' . strtoupper($country_code) . ' ' . ucfirst(strtolower($lang_name)) . ' - ' . ucfirst(strtolower($env)),
      $site->id,
      'drupal',
      $env != 'live' ? 'https://' . $brand_code . $country_code . '-' . $env . '.factory.alshaya.com/' . $lang_code : 'https://' . $brand_code . $country_code . '.factory.alshaya.com/' . $lang_code,
      $magento_data['store_id'][$lang_code],
      $drupal_auth->id
    );

    echo "\n" . $drupal_system->id;

    // Create mapping for MDC <> Drupal $country_code + $lang_code.
    $mapping = create_mapping(
      'Mapping - ' . strtolower($brand_code) . strtolower($country_code) . strtolower($lang_code) . strtolower($env),
      'Mapping details for ' . ucfirst(strtolower($brand_name)) . ' ' . strtoupper($country_code) . ' ' . ucfirst(strtolower($lang_name)) . ' - ' . ucfirst(strtolower($env)),
      $site->id,
      $magento_system->id,
      $drupal_system->id
    );

    echo "\n" . $mapping->id;
  }
}

echo "\n";
