<?php

namespace App\Service\Config;

/**
 * Class SystemSettings.
 *
 * @package App\Service\Config
 */
class SystemSettings {

  /**
   * Appointment settings.
   *
   * @var array
   */
  protected $appointmentSettings;

  /**
   * SystemSettings constructor.
   */
  public function __construct() {
    // We need this always.
    // Do it here once.
    $this->readSettingsFromCode();
  }

  /**
   * Read the settings from code and store in object.
   */
  protected function readSettingsFromCode() {
    // Get site environment.
    require_once DRUPAL_ROOT . '/../factory-hooks/environments/environments.php';

    $env = $this->getEnvironment();

    // Get host_site_code or acsf_site_name based on environment.
    if ($env === 'local') {
      // Require local_sites.php file for host site code.
      require_once DRUPAL_ROOT . '/../factory-hooks/pre-settings-php/local_sites.php';
    }
    else {
      // Require sites.inc and post-sites-php/includes.php for ACSF site_name.
      require_once DRUPAL_ROOT . '/sites/g/sites.inc';
      $host = rtrim($_SERVER['HTTP_HOST'], '.');
      $data = gardens_site_data_refresh_one($host);
      $GLOBALS['gardens_site_settings'] = $data['gardens_site_settings'];
      require_once DRUPAL_ROOT . '/../factory-hooks/post-sites-php/includes.php';
    }

    $site_country_code = alshaya_get_site_country_code($this->getSiteCode());

    require_once DRUPAL_ROOT . '/../factory-hooks/post-settings-php/x_appointment.php';
    require_once DRUPAL_ROOT . '/../factory-hooks/post-settings-php/middleware_auth.php';

    // Include overrides.
    require_once DRUPAL_ROOT . '/../factory-hooks/post-settings-php/zzz_overrides.php';

    $this->appointmentSettings = $settings;
  }

  /**
   * Wrapper function to get site code.
   *
   * @return string|null
   *   Site code if available.
   */
  public function getSiteCode() {
    // @codingStandardsIgnoreLine
    global $host_site_code, $_acsf_site_name;

    // Get host_site_code or acsf_site_name based on environment.
    return ($this->getEnvironment() === 'local')
      ? $host_site_code
      : $_acsf_site_name;
  }

  /**
   * Get current environment code.
   *
   * Removes the numerical prefix.
   *
   * @return string
   *   Environment code.
   */
  public function getEnvironment() {
    $env = alshaya_get_site_environment();

    // This is to remove `01/02` etc from env name.
    if (substr($env, 0, 1) == '0') {
      $env = substr($env, 2);
    }

    return $env;
  }

  /**
   * Get appointment settings.
   *
   * @param string $key
   *   Settings key.
   * @param mixed $default
   *   Used mainly for setting default return type.
   *
   * @return array|mixed
   *   Settings if found.
   */
  public function getSettings(string $key, $default = NULL) {
    return $this->appointmentSettings[$key] ?? $default;
  }

}
