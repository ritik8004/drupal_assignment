<?php

namespace App\Service\Drupal;

use GuzzleHttp\Client;
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
  public function getDrupalHostUrl() {
    if (strpos($this->getDrupalBaseUrl(), 'factory') !== FALSE) {
      return 'https://' . $this->getDrupalBaseUrl() . '.cdn.cloudflare.net';
    }

    return 'https://' . $this->getDrupalBaseUrl();
  }

  /**
   * Get drupal base url.
   *
   * @return string
   *   Drupal base url.
   */
  public function getDrupalBaseUrl() {
    return $this->request->getHttpHost();
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
    return (new Client([
      // Base URI is used with relative requests.
      'base_uri' => $this->getDrupalHostUrl(),
      'headers' => ['Host' => $this->getDrupalBaseUrl()],
      'verify' => FALSE,
    ]));
  }

}
