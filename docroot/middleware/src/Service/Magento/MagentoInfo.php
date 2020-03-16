<?php

namespace App\Service\Magento;

use App\Service\Config\SystemSettings;
use springimport\magento2\apiv1\ApiFactory;
use springimport\magento2\apiv1\Configuration;

/**
 * Class MagentoInfo.
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
   * Get the magento store id.
   *
   * @return int
   *   Magento store id.
   */
  public function getMagentoStoreId() {
    $lang = $this->systemSettings->getRequestLanguage();
    return $this->systemSettings->getSettings('store_id')[$lang] ?? NULL;
  }

  /**
   * Get the magento secret info.
   *
   * @return array
   *   Magento secret info.
   */
  public function getMagentoSecretInfo() {
    return $this->systemSettings->getSettings('alshaya_api.settings') ?? NULL;
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
