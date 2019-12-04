<?php

namespace App\Service\Magento;

use springimport\magento2\apiv1\ApiFactory;
use springimport\magento2\apiv1\Configuration;

/**
 * Class MagentoInfo.
 */
class MagentoInfo {

  /**
   * Alshaya commerce_third_party settings.
   *
   * @var array
   */
  protected $settings;

  /**
   * MagentoInfo constructor.
   */
  public function __construct() {
    $this->setMagentoCredentials();
  }

  /**
   * Initialize magento credentials.
   */
  protected function setMagentoCredentials() {
    // Get site environment.
    require_once DRUPAL_ROOT . '/../factory-hooks/environments/environments.php';
    $env = alshaya_get_site_environment();

    // Get host_site_code or acsf_site_name based on environment.
    $site_name = NULL;
    if ($env === 'local') {
      // Require local_sites.php file for host site code.
      require DRUPAL_ROOT . '/../factory-hooks/pre-settings-php/local_sites.php';
      global $host_site_code;
      $site_name = $host_site_code;
    }
    else {
      // Require sites.inc and post-sites-php/includes.php for ACSF site_name.
      require DRUPAL_ROOT . '/sites/g/sites.inc';
      $host = rtrim($_SERVER['HTTP_HOST'], '.');
      $data = gardens_site_data_refresh_one($host);
      $GLOBALS['gardens_site_settings'] = $data['gardens_site_settings'];
      require DRUPAL_ROOT . '/../factory-hooks/post-sites-php/includes.php';
      global $_acsf_site_name;
      $site_name = $_acsf_site_name;
    }

    // Include overrides.
    require_once DRUPAL_ROOT . '/../factory-hooks/post-settings-php/zzz_overrides.php';

    // Get magento (commerce_third_party) settings.
    if (!empty($site_name)) {
      $site_country_code = alshaya_get_site_country_code($site_name);
      require DRUPAL_ROOT . '/../factory-hooks/environments/mapping.php';
      $commerce_settings = alshaya_get_commerce_third_party_settings(
        $site_country_code['site_code'],
        $site_country_code['country_code'],
        $env
      );
      $this->settings = $commerce_settings ?? NULL;
    }
  }

  /**
   * Get the magento url for api call.
   *
   * This contains the `MDC url` + `MDC store code` + `MDC api prefix`.
   *
   * @return string
   *   Magento api url.
   */
  public function getMagentoUrl() {
    // Fetch Urls based on the incoming request urls.
    return $this->getMagentoBaseUrl() . '/' . $this->getMagentoStore() . '/' . $this->getMagentoApiPrefix();
  }

  /**
   * Get the magento base url.
   *
   * @return string
   *   Magento base url.
   */
  public function getMagentoBaseUrl() {
    return !empty($this->settings) ? $this->settings['alshaya_api.settings']['magento_host'] : NULL;
  }

  /**
   * Get the magento store code.
   *
   * @return string
   *   Magento store code.
   */
  public function getMagentoStore() {
    // @TODO Get lang dynamically passed from API request.
    $lang = 'en';
    return !empty($this->settings) ? $this->settings['magento_lang_prefix'][$lang] : NULL;
  }

  /**
   * Get the magento secret info.
   *
   * @return array
   *   Magento secret info.
   */
  public function getMagentoSecretInfo() {
    return !empty($this->settings) ? $this->settings['alshaya_api.settings'] : NULL;
  }

  /**
   * Magento api prefix.
   *
   * @return string
   *   Magento api prefix.
   */
  public function getMagentoApiPrefix() {
    return 'rest/V1';
  }

  /**
   * Get api client for magento.
   *
   * @return \GuzzleHttp\Client
   *   HTTP client.
   */
  public function getMagentoApiClient() {
    $configuration = new Configuration();
    $configuration->setBaseUri($this->getMagentoUrl());
    $configuration->setConsumerKey($this->getMagentoSecretInfo()['consumer_key']);
    $configuration->setConsumerSecret($this->getMagentoSecretInfo()['consumer_secret']);
    $configuration->setToken($this->getMagentoSecretInfo()['access_token']);
    $configuration->setTokenSecret($this->getMagentoSecretInfo()['access_token_secret']);

    return (new ApiFactory($configuration))->getApiClient();
  }

}
