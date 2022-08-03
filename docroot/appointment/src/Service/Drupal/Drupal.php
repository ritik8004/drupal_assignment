<?php

namespace App\Service\Drupal;

use App\Service\Config\SystemSettings;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\TransferStats;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Helper class which interacts Drupal.
 */
class Drupal {

  /**
   * Drupal info.
   *
   * @var \App\Service\Drupal\DrupalInfo
   */
  private $drupalInfo;

  /**
   * The request stack object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $request;

  /**
   * System Settings service.
   *
   * @var \App\Service\Config\SystemSettings
   */
  private $settings;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * Drupal constructor.
   *
   * @param \App\Service\Drupal\DrupalInfo $drupal_info
   *   Drupal info service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(
    DrupalInfo $drupal_info,
    RequestStack $request_stack,
    SystemSettings $settings,
    LoggerInterface $logger
  ) {
    $this->drupalInfo = $drupal_info;
    $this->request = $request_stack;
    $this->settings = $settings;
    $this->logger = $logger;
  }

  /**
   * Wrapper function to invoke Drupal API.
   *
   * @param string $method
   *   Request method - get/post.
   * @param string $url
   *   URL without language code.
   * @param array $request_options
   *   Request options.
   *
   * @return mixed|\Psr\Http\Message\ResponseInterface
   *   Response.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function invokeApi(string $method, string $url, array $request_options = []) {
    $client = $this->drupalInfo->getDrupalApiClient();

    // Add language code in url.
    $url = '/' . $this->drupalInfo->getDrupalLangcode() . $url;

    $that = $this;
    $request_options['on_stats'] = function (TransferStats $stats) use ($that) {
      $code = ($stats->hasResponse())
        ? $stats->getResponse()->getStatusCode()
        : 0;

      $that->logger->info(sprintf(
        'Finished API request %s in %.4f. Response code: %d',
        $stats->getEffectiveUri(),
        $stats->getTransferTime(),
        $code
      ));
    };

    $request_options['headers']['Host'] = $this->drupalInfo->getDrupalBaseUrl();
    $request_options['timeout'] ??= $this->drupalInfo->getPhpTimeout('default');

    return $client->request($method, $url, $request_options);
  }

  /**
   * Wrapper function to invoke Drupal API.
   *
   * @param string $method
   *   Request method - get/post.
   * @param string $url
   *   URL without language code.
   * @param array $request_options
   *   Request options.
   *
   * @return mixed|\Psr\Http\Message\ResponseInterface
   *   Response.
   */
  protected function invokeApiWithSession(string $method, string $url, array $request_options = []) {
    // Add current request cookies to ensure request is done with same session
    // as the browser.
    $cookies = new SetCookie($this->request->getCurrentRequest()->cookies->all());
    $request_options['headers']['Cookie'] = $cookies->__toString();

    // Add a custom header to ensure Drupal allows this request without
    // further authentication.
    $request_options['headers']['alshaya-middleware'] = md5($this->settings->getSettings('middleware_auth'));

    return $this->invokeApi($method, $url, $request_options);
  }

  /**
   * Get Drupal uid and customer id for the user in session.
   */
  public function getSessionUserInfo() {
    $url = '/get/userinfo';
    $response = $this->invokeApiWithSession('GET', $url);
    $result = $response->getBody()->getContents();
    $user = json_decode($result, TRUE);

    return $user;
  }

}
