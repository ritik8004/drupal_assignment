<?php

namespace App\Service\Magento;

use App\Service\Config\SystemSettings;
use App\Controller\PaymentController;
use springimport\magento2\apiv1\ApiFactory;
use springimport\magento2\apiv1\Configuration;

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
    // If langcode is set by the external payment method, use that
    // otherwise use from the request object.
    if (empty($lang = PaymentController::$externalPaymentLangcode)) {
      $lang = $this->systemSettings->getRequestLanguage();
    }
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
    return $this->getMagentoStoreIds()[$lang] ?? NULL;
  }

  /**
   * Get the magento store ids for all languages.
   *
   * @return array
   *   Magento store ids.
   */
  public function getMagentoStoreIds() {
    return $this->systemSettings->getSettings('store_id');
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

  /**
   * Get cancel reservation setting.
   *
   * @return bool
   *   Return TRUE if setting is enabled, FALSE otherwise.
   */
  public function isCancelReservationEnabled() {
    return $this->systemSettings->getSettings('alshaya_checkout_settings')['cancel_reservation_enabled'] ?? FALSE;
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
    return $this->systemSettings->getSettings('alshaya_backend_calls_options')['middleware'][$context]['timeout']
        ?? $this->systemSettings->getSettings('alshaya_backend_calls_options')['middleware']['default']['timeout'];
  }

}
