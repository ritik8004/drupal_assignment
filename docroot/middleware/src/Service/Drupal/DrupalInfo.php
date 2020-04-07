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
    // For factory domains we have an issue around DNS resolution, in many
    // systems (mainly linux as per observations) *.factory.alshaya.com domain
    // doesn't resolve properly. As a workaround we use the domain with CDN
    // suffix here. Issue is very old and has been informed to Alshaya as well
    // as TAM of the project, configuration changes are expected in how the
    // domain is configured by Alshaya.
    // To observe the issue try to do curl https://mckw-uat.factory.alshaya.com
    // from inside the UAT server.
    // alshaya@staging-1509:/var/www/html/alshaya.01uat/docroot$ curl -I \
    // > mckw-uat.factory.alshaya.com
    // curl: (6) Could not resolve host: mckw-uat.factory.alshaya.com.
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
    return $this->request->query->get('lang', 'en');
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
