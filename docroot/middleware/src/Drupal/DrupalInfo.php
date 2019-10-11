<?php

namespace AlshayaMiddleware\Drupal;

use springimport\magento2\apiv1\ApiFactory;
use springimport\magento2\apiv1\Configuration;

/**
 * Class DrupalInfo.
 */
class DrupalInfo {

  /**
   * Get drupal url.
   *
   * @return string
   *   Drupal url.
   */
  public function getDrupalUrl() {
    return $this->getDrupalBaseUrl() . $this->getDrupalLangcode();
  }

  /**
   * Get drupal base url.
   *
   * @return string
   *   Drupal base url.
   */
  public function getDrupalBaseUrl() {
    // @Todo: Make it dynamic.
    return 'http://local.alshaya-hmkw.com/';
  }

  /**
   * Get drupal langcode.
   *
   * @return string
   *   Drupal langcode.
   */
  public function getDrupalLangcode() {
    // @Todo: Make it dynamic.
    return 'en';
  }

  /**
   * Get api client for drupal.
   *
   * @return \GuzzleHttp\Client
   *   Api client.
   */
  public function getDrupalApiClient() {
    $configuration = new Configuration();
    $configuration->setBaseUri($this->getDrupalBaseUrl());
    return (new ApiFactory($configuration))->getApiClient();
  }

}
