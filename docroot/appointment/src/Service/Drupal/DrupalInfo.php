<?php

namespace App\Service\Drupal;

use App\Service\Config\SystemSettings;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Mainly provides information about the related Drupal site.
 */
class DrupalInfo {

  /**
   * RequestStack Object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * System Settings service.
   *
   * @var \App\Service\Config\SystemSettings
   */
  protected $systemSettings;

  /**
   * DrupalInfo constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   RequestStack Object.
   * @param \App\Service\Config\SystemSettings $system_settings
   *   System Settings.
   */
  public function __construct(
    RequestStack $request,
    SystemSettings $system_settings
  ) {
    $this->request = $request->getCurrentRequest();
    $this->systemSettings = $system_settings;
  }

  /**
   * Get drupal url.
   *
   * @return string
   *   Drupal url.
   */
  public function getDrupalHostUrl() {
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
    $host = $this->getDrupalBaseUrl();

    $options = [
      // Base URI is used with relative requests.
      'base_uri' => $this->getDrupalHostUrl(),
      'headers' => ['Host' => $host],
      'verify' => FALSE,
    ];

    // For factory domains we have an issue around DNS resolution, in many
    // systems (mainly linux as per observations) *.factory.alshaya.com domain
    // doesn't resolve properly. As a workaround we hardcode the IP here.
    // Issue is very old and has been informed to Alshaya as well as TAM of
    // the project, configuration changes are expected in how the
    // domain is configured by Alshaya.
    // To observe the issue try to do curl https://mckw-uat.factory.alshaya.com
    // from inside the UAT server.
    // alshaya@staging-1509:/var/www/html/alshaya.01uat/docroot$ curl -I \
    // > mckw-uat.factory.alshaya.com
    // curl: (6) Could not resolve host: mckw-uat.factory.alshaya.com.
    if (str_contains($host, 'factory.alshaya.com')) {
      $options['curl'] = [
        CURLOPT_RESOLVE => [
          // @todo Make this IP a setting.
          $host . ':80:104.18.6.59',
          $host . ':443:104.18.6.59',
        ],
      ];
    }

    return (new Client($options));
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
