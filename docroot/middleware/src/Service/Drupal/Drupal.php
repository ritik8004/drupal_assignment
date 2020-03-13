<?php

namespace App\Service\Drupal;

use GuzzleHttp\Cookie\SetCookie;
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
   * Drupal constructor.
   *
   * @param \AlshayaMiddleware\Drupal\DrupalInfo $drupal_info
   *   Drupal info service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(
    DrupalInfo $drupal_info,
    RequestStack $request_stack
  ) {
    $this->drupalInfo = $drupal_info;
    $this->request = $request_stack;
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
    $client = $this->drupalInfo->getDrupalApiClient();
    $data = [];
    foreach ($skus as $sku) {
      $url = sprintf('/%s/rest/v1/product/%s', $this->drupalInfo->getDrupalLangcode(), $sku) . '?context=cart';
      $response = $client->request('GET', $url, ['headers' => ['Host' => $this->drupalInfo->getDrupalBaseUrl()]]);
      $result = $response->getBody()->getContents();
      $data[$sku] = json_decode($result, TRUE);
    }

    return $data;
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
    $client = $this->drupalInfo->getDrupalApiClient();
    $data = [];
    foreach ($skus as $sku) {
      $url = sprintf('/%s/rest/v1/product/%s/linked?context=cart', $this->drupalInfo->getDrupalLangcode(), $sku);
      $response = $client->request('GET', $url, ['headers' => ['Host' => $this->drupalInfo->getDrupalBaseUrl()]]);
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
    $client = $this->drupalInfo->getDrupalApiClient();
    $url = sprintf('/%s/rest/v1/promotion/all', $this->drupalInfo->getDrupalLangcode());
    $response = $client->request('GET', $url, ['headers' => ['Host' => $this->drupalInfo->getDrupalBaseUrl()]]);
    $result = $response->getBody()->getContents();
    return json_decode($result, TRUE);
  }

  /**
   * Get Drupal uid and customer id for the user in session.
   */
  public function getSessionCustomerInfo() {
    $client = $this->drupalInfo->getDrupalApiClient();
    $url = sprintf('/%s/spc/customer', $this->drupalInfo->getDrupalLangcode());
    $cookies = new SetCookie($this->request->getCurrentRequest()->cookies->all());
    $response = $client->request('GET', $url, [
      'headers' => [
        'Host' => $this->drupalInfo->getDrupalBaseUrl(),
        'Cookie' => $cookies->__toString(),
      ],
    ]);
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
    $client = $this->drupalInfo->getDrupalApiClient();
    $url = sprintf('/%s/cnc/store/%s', $this->drupalInfo->getDrupalLangcode(), $store_code);
    $response = $client->request('GET', $url, [
      'headers' => [
        'Host' => $this->drupalInfo->getDrupalBaseUrl(),
      ],
    ]);
    $result = $response->getBody()->getContents();
    return json_decode($result, TRUE);
  }

}
