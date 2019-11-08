<?php

namespace App\Service\Drupal;

use springimport\magento2\apiv1\ApiFactory;
use springimport\magento2\apiv1\Configuration;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class DrupalInfo.
 */
class DrupalInfo {

  /**
   * RequestStack Object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * DrupalInfo constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   RequestStack Object.
   */
  public function __construct(RequestStack $request) {
    $this->request = $request->getCurrentRequest();
  }

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
    return $this->request->getScheme() . '://' . $this->request->getHttpHost() . '/';
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
