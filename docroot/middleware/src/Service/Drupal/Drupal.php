<?php

namespace App\Service\Drupal;

use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\TransferStats;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Drupal.
 */
class Drupal {

  /**
   * Drupal info.
   *
   * @var \AlshayaMiddleware\Drupal\DrupalInfo
   */
  private $drupalInfo;

  /**
   * The request stack object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $request;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * Drupal constructor.
   *
   * @param \AlshayaMiddleware\Drupal\DrupalInfo $drupal_info
   *   Drupal info service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(
    DrupalInfo $drupal_info,
    RequestStack $request_stack,
    LoggerInterface $logger
  ) {
    $this->drupalInfo = $drupal_info;
    $this->request = $request_stack;
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
    $request_options['timeout'] = $request_options['timeout'] ?? 30;
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
    $cookies = new SetCookie($this->request->getCurrentRequest()->cookies->all());
    $request_options['headers']['Cookie'] = $cookies->__toString();
    return $this->invokeApi($method, $url, $request_options);
  }

  /**
   * Get info from drupal for cart items data.
   *
   * @param array $skus
   *   Skus.
   *
   * @return array
   *   Items data with info from drupal.
   */
  public function getCartItemDrupalData(array $skus) {
    $data = [];
    foreach ($skus as $sku) {
      $url = sprintf('/rest/v1/product/%s', $sku) . '?context=cart';
      $response = $this->invokeApi('GET', $url);
      $result = $response->getBody()->getContents();
      $data[$sku] = json_decode($result, TRUE);
    }

    return $data;
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
    $url = sprintf('/rest/v1/product/%s', $sku) . '?context=cart';
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
   * Get linked skus info from Drupal.
   *
   * @param array $skus
   *   Skus.
   *
   * @return array
   *   Linked skus data.
   */
  public function getDrupalLinkedSkus(array $skus) {
    $data = [];
    foreach ($skus as $sku) {
      $url = sprintf('/rest/v1/product/%s/linked?context=cart', $sku);
      $response = $this->invokeApi('GET', $url);
      $result = $response->getBody()->getContents();
      $data[$sku] = json_decode($result, TRUE);
    }

    return $data;
  }

  /**
   * Get all promo data from drupal.
   *
   * @return mixed
   *   All promo data.
   */
  public function getAllPromoData() {
    $url = '/rest/v1/promotion/all';
    $response = $this->invokeApi('GET', $url);
    $result = $response->getBody()->getContents();
    return json_decode($result, TRUE);
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

}
