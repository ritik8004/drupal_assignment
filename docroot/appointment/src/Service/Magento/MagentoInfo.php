<?php

namespace App\Service\Magento;

use App\Service\Config\SystemSettings;
use GuzzleHttp\Client;

/**
 * Mainly provides Magento connection information.
 */
class MagentoInfo {

  /**
   * System Settings.
   *
   * @var \App\Service\Config\SystemSettings
   */
  protected $systemSettings;

  /**
   * MagentoInfo constructor.
   *
   * @param \App\Service\Config\SystemSettings $system_settings
   *   System Settings.
   */
  public function __construct(SystemSettings $system_settings) {
    $this->systemSettings = $system_settings;
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
    return $this->systemSettings->getSettings('alshaya_api.settings')['magento_host'] ?? NULL;
  }

  /**
   * Get the magento store code.
   *
   * @return string
   *   Magento store code.
   */
  public function getMagentoStore() {
    $lang = $this->systemSettings->getRequestLanguage();
    return $this->systemSettings->getSettings('magento_lang_prefix')[$lang] ?? NULL;
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
    $client = new Client([
      'base_uri' => $this->getMagentoUrl(),
      'headers' => [
        'Content-Type' => 'application/json',
      ],
    ]);

    return $client;
  }

  /**
   * Returns the PHP timeout value for the given context.
   *
   * @param string $context
   *   The context in which the timeout is required.
   *
   * @return int
   *   The timeout time in seconds.
   */
  public function getPhpTimeout(string $context) {
    return $this->systemSettings->getSettings('alshaya_backend_calls_options')['appointment_booking'][$context]['timeout']
        ?? $this->systemSettings->getSettings('alshaya_backend_calls_options')['appointment_booking']['default']['timeout'];
  }

}
