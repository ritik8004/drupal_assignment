<?php

/**
 * Get specific settings for specific site + environment combination.
 *
 * @param string $site_code
 *  Site code for which alshaya specific settings are needed.
 * @param string $country_code
 *  Country code for which alshaya specific settings are needed.
 * @param string $env
 *  Env for which alshaya specific settings are needed.
 *
 * @return array
 *  Array with alshaya specific settings based on the parameters.
 */
function alshaya_get_specific_settings($site_code, $country_code, $env) {
  include_once DRUPAL_ROOT . '/../factory-hooks/environments/mapping.php';
  $third_party_settings = alshaya_get_commerce_third_party_settings($site_code, $country_code, $env);

  include_once DRUPAL_ROOT . '/../factory-hooks/environments/settings.php';
  $additional_settings = alshaya_get_additional_settings($site_code, $country_code, $env);

  return $third_party_settings + $additional_settings;
}
