<?php

namespace App\Service\Config;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SystemSettings.
 *
 * @package App\Service\Config
 */
class SystemSettings {

  /**
   * Third party settings.
   *
   * @var array
   */
  protected $thirdPartySettings;

  /**
   * RequestStack Object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * SystemSettings constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   RequestStack Object.
   */
  public function __construct(RequestStack $request) {
    $this->request = $request->getCurrentRequest();

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
      require DRUPAL_ROOT . '/../factory-hooks/pre-settings-php/local_sites.php';
    }
    else {
      // Require sites.inc and post-sites-php/includes.php for ACSF site_name.
      require DRUPAL_ROOT . '/sites/g/sites.inc';
      $host = rtrim($_SERVER['HTTP_HOST'], '.');
      $data = gardens_site_data_refresh_one($host);
      $GLOBALS['gardens_site_settings'] = $data['gardens_site_settings'];
      require DRUPAL_ROOT . '/../factory-hooks/post-sites-php/includes.php';
    }

    $site_country_code = alshaya_get_site_country_code($this->getSiteCode());
    require DRUPAL_ROOT . '/../factory-hooks/environments/mapping.php';

    $settings = alshaya_get_commerce_third_party_settings(
      $site_country_code['site_code'],
      $site_country_code['country_code'],
      $env
    );

    // Require more files as and when required.
    // Files should not have any Drupal specific code.
    require_once DRUPAL_ROOT . '/../factory-hooks/post-settings-php/knet.php';

    // Include overrides.
    require_once DRUPAL_ROOT . '/../factory-hooks/post-settings-php/zzz_overrides.php';

    $this->thirdPartySettings = $settings;
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
   * Get current request language.
   *
   * @return string
   *   Requested language.
   */
  public function getRequestLanguage() {
    $lang = $this->request->query->get('lang', 'en');
    return in_array($lang, ['en', 'ar']) ? $lang : 'en';
  }

  /**
   * Get settings from third party.
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
    return $this->thirdPartySettings[$key] ?? $default;
  }

}
