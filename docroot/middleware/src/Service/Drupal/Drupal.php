<?php

namespace App\Service\Drupal;

use App\Service\Config\SystemSettings;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\TransferStats;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Mainly provides information about the related Drupal site.
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
    $request_options['timeout'] = $request_options['timeout'] ?? $this->drupalInfo->getPhpTimeout('default');

    // Bypass CloudFlare for all requests from middleware to Drupal.
    // Rules are added in CF to disable caching for urls having the following
    // query string.
    // The query string is added since same APIs are used by MAPP also.
    $url .= (strpos($url, '?') !== FALSE) ? '&_cf_cache_bypass=1' : '?_cf_cache_bypass=1';

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
   * Get stock info from drupal for sku.
   *
   * @param string $sku
   *   SKU.
   *
   * @return array
   *   Items data with info from drupal.
   */
  public function getCartItemDrupalStock($sku) {
    $url = sprintf('/rest/v1/stock/%s', $sku);
    $response = $this->invokeApi('GET', $url);
    $result = $response->getBody()->getContents();
    return json_decode($result, TRUE);
  }

  /**
   * Get CnC status for cart based on skus in cart.
   *
   * @param string $skus_list
   *   Comma separated sku list.
   *
   * @return mixed
   *   CnC status for cart.
   */
  public function getCncStatusForCart(string $skus_list = '') {
    $url = sprintf('/spc/cart/cnc-status?skus=%s', $skus_list);
    $response = $this->invokeApi('GET', $url);
    $result = $response->getBody()->getContents();
    return json_decode($result, TRUE);
  }

  /**
   * Trigger event to let Drupal know about the update.
   *
   * @param string $event
   *   Event to trigger..
   * @param array $data
   *   Data form checkout event.
   *
   * @return mixed
   *   Result from drupal.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function triggerCheckoutEvent(string $event, array $data) {
    $url = '/spc/checkout-event';

    $data['action'] = $event;
    $options = ['form_params' => $data];

    try {
      $response = $this->invokeApiWithSession('POST', $url, $options);
      $result = $response->getBody()->getContents();
      return json_decode($result, TRUE);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while triggering checkout event @event. Message: @message', [
        '@event' => $event,
        '@message' => $e->getMessage(),
      ]);
    }

    return ['status' => FALSE];
  }

  /**
   * Get Drupal uid and customer id for the user in session.
   */
  public function getSessionCustomerInfo() {
    $url = '/spc/customer';
    $response = $this->invokeApiWithSession('GET', $url);
    $result = $response->getBody()->getContents();
    $customer = json_decode($result, TRUE);

    // Clean customer data.
    $customer['customer_id'] = (int) $customer['customer_id'];

    return $customer;
  }

  /**
   * Get existing acm cart for user.
   */
  public function getAcmCartId() {
    $url = '/cart/old';
    $response = $this->invokeApiWithSession('GET', $url);
    $result = $response->getBody()->getContents();
    return json_decode($result, TRUE);
  }

  /**
   * Get store info for given store code.
   *
   * @param string $store_code
   *   The store code.
   *
   * @return mixed
   *   Return store info.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getStoreInfo($store_code) {
    $url = sprintf('/cnc/store/%s', $store_code);
    $response = $this->invokeApi('GET', $url);
    $result = $response->getBody()->getContents();
    return json_decode($result, TRUE);
  }

  /**
   * Validate area/city of address.
   *
   * @param array $address
   *   Address array.
   *
   * @return mixed
   *   Address validation response.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function validateAddressAreaCity(array $address) {
    $options = [
      'json' => [
        'address' => $address,
      ],
    ];
    $response = $this->invokeApi('POST', '/spc/validate-info', $options);
    $result = $response->getBody()->getContents();
    return json_decode($result, TRUE);
  }

}
