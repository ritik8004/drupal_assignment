<?php

namespace Drupal\alshaya_acm;

use Drupal\Core\Site\Settings;

/**
 * AlshayaAcmConfigCheck.
 */
class AlshayaAcmConfigCheck {

  /**
   * Helper function to check config and reset if required.
   *
   * @param bool $force
   *   Force reset.
   */
  public function checkConfig($force = FALSE) {
    // Do this only after installation is done.
    if (empty(\Drupal::configFactory()->get('alshaya.installed_brand')->get('module'))) {
      return;
    }

    // Get the current env.
    $env = Settings::get('env') ?: 'local';

    // We don't do anything on update envs like 01uatup.
    if (substr($env, -2) === 'up') {
      return;
    }

    // If we want to force reset, we don't check for other conditions.
    // We don't do anything on prod.
    if (!$force && alshaya_is_env_prod()) {
      return;
    }

    $request_time = \Drupal::time()->getRequestTime();

    // Check if reverting of settings is disabled.
    if (!$force && !empty(Settings::get('disable_config_reset'))) {
      return;
    }

    $flag_var = 'alshaya_acm_config_check.' . $env;

    // We store reset time in state, check if variable is set for our ENV.
    $reset_time = \Drupal::state()->get($flag_var);

    if (!$force && !empty($reset_time)) {
      return;
    }

    // Set the first request time in state.
    \Drupal::state()->set($flag_var, $request_time);

    $reset = [
      'acq_commerce.conductor',
      'alshaya_api.settings',
      'acq_cybersource.settings',
      'alshaya_acm_knet.settings',
      'recaptcha.settings',
      'geolocation.settings',
    ];

    // Reset the settings.
    foreach ($reset as $config_key) {
      $config = \Drupal::configFactory()->getEditable($config_key);
      $settings = Settings::get($config_key);

      foreach ($settings as $key => $value) {
        $config->set($key, $value);
      }

      $config->save();
    }

    // Always set GTM id to null on all envs (except prod) first time.
    $config = \Drupal::configFactory()->getEditable('google_tag.settings');
    $config->set('container_id', '');
    $config->save();

    // Reset :to e-mail for contact us page.
    $config = \Drupal::configFactory()->getEditable('webform.webform.alshaya_contact');
    $config->set('handlers.email.settings.to_mail', 'no-reply@acquia.com');
    $config->save();

    // We can code here to support more or different languages later when
    // we encounter those scenarios, keeping it simple and static for now.
    // Reset store id - EN.
    \Drupal::configFactory()->getEditable('acq_commerce.store')
      ->set('store_id', Settings::get('store_id')['en'])
      ->save();

    // Reset store id - AR.
    \Drupal::languageManager()->getLanguageConfigOverride('ar', 'acq_commerce.store')
      ->set('store_id', Settings::get('store_id')['ar'])
      ->save();

    // Reset magento_lang_prefix - EN.
    \Drupal::configFactory()->getEditable('alshaya_api.settings')
      ->set('magento_lang_prefix', Settings::get('magento_lang_prefix')['en'])
      ->save();

    // Reset magento_lang_prefix - AR.
    \Drupal::languageManager()->getLanguageConfigOverride('ar', 'alshaya_api.settings')
      ->set('magento_lang_prefix', Settings::get('magento_lang_prefix')['ar'])
      ->save();
  }

  /**
   * Function to reset country specific settings.
   */
  public function resetCountrySpecificSettings() {
    \Drupal::moduleHandler()->loadInclude('alshaya', 'inc', 'utilities/alshaya.utilities.countries');

    // Get country code for current site.
    $country_code = _alshaya_custom_get_site_level_country_code();

    // Reset currency code - EN.
    \Drupal::configFactory()->getEditable('acq_commerce.currency')
      ->set('currency_code', _alshaya_get_currency_code($country_code, 'en'))
      ->save();

    // Reset currency code - AR.
    \Drupal::languageManager()->getLanguageConfigOverride('ar', 'acq_commerce.currency')
      ->set('currency_code', _alshaya_get_currency_code($country_code, 'ar'))
      ->save();

    // Re-save store finder view to ensure country code is updated.
    \Drupal::configFactory()->getEditable('views.view.stores_finder')->save();
  }

}
