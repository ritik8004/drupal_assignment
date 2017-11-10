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

    // @TODO: Find a better way to check if env is prod.
    $prod_envs = [
      '01live',
      '01update',
    ];

    // We don't do anything on prod.
    if (in_array($env, $prod_envs)) {
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
  }

}
