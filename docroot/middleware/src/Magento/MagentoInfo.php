<?php

namespace AlshayaMiddleware\Magento;

use springimport\magento2\apiv1\ApiFactory;
use springimport\magento2\apiv1\Configuration;

/**
 * Class MagentoInfo.
 */
class MagentoInfo {

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
    // @Todo: Make it dynamic.
    return 'http://staging-api.mothercare.com.kw.c.z3gmkbwmwrl4g.ent.magento.cloud';
  }

  /**
   * Get the magento store code.
   *
   * @return string
   *   Magento store code.
   */
  public function getMagentoStore() {
    // @Todo: Make it dynamic.
    return 'kwt_en';
  }

  /**
   * Get the magento secret info.
   *
   * @return array
   *   Magento secret info.
   */
  public function getMagentoSecretInfo() {
    // @Todo: Make it dynamic.
    return [
      'consumer_key' => '3ewl8lsult7l5mpp1ckv0hw1ftk0u2bc',
      'consumer_secret' => '84avnwtrinkpt2jmda6f61l8vy5cabb1',
      'access_token' => 'yw1bvvwqe1vrab9sqjioepclb044jja2',
      'access_token_secret' => 'bsmp4igrv2bgtn6pk5ojko32qvrrk798',
    ];
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
