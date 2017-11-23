<?php

namespace Drupal\alshaya_acm;

use Drupal\Core\Site\Settings;

/**
 * AlshayaAcmConfigCheck.
 */
class AlshayaAcmConfigCheck {

  /**
   * Helper function to check config and reset if required.
   */
  public function checkConfig() {
    $env = Settings::get('env') ?: 'local';

    // We don't do anything on prod.
    if (alshaya_is_env_prod()) {
      return;
    }

    $request_time = \Drupal::time()->getRequestTime();
    $interval = (int) \Drupal::config('alshaya_acm.settings')->get('config_check_interval');

    // Check if reverting of settings is disabled.
    if (empty($interval)) {
      return;
    }

    $flag_var = 'alshaya_acm_config_check.' . $env;

    // Very first request time or time at which settings were reset last.
    $first_request = \Drupal::state()->get($flag_var);

    if (empty($first_request)) {
      $first_request = $request_time;

      // Set the first request time in state.
      \Drupal::state()->set($flag_var, $first_request);

      // Always set GTM id to null on all envs (except prod) first time.
      $config = \Drupal::configFactory()->getEditable('google_tag.settings');
      $config->set('container_id', '');
      $config->save();

      // Reset :to e-mail for contact us page.
      $config = \Drupal::configFactory()->getEditable('webform.webform.alshaya_contact');
      $config->set('handlers.email.settings.to_mail', 'no-reply@acquia.com');
      $config->save();
    }
    // The interval time below allows to do temporary overrides on non prod
    // envs for dev/test purpose.
    elseif ($request_time - $first_request < $interval) {
      return;
    }

    // Set the current request time in state.
    // We reset here to calculate interval with reference to this value.
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
  }

}
