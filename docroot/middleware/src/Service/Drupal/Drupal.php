<?php

namespace App\Service\Drupal;

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
   * Drupal constructor.
   *
   * @param \AlshayaMiddleware\Drupal\DrupalInfo $drupal_info
   *   Drupal info service.
   */
  public function __construct(DrupalInfo $drupal_info) {
    $this->drupalInfo = $drupal_info;
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
      $url = '/' . sprintf('rest/v1/product/%s', $sku) . '?context=cart';
      $response = $client->request('GET', $url, ['verify' => FALSE]);
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
      $url = '/' . sprintf('rest/v1/product/%s/linked?context=cart', $sku);
      $response = $client->request('GET', $url, ['verify' => FALSE]);
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
    $response = $client->request('GET', '/rest/v1/promotion/all', ['verify' => FALSE]);
    $result = $response->getBody()->getContents();
    return json_decode($result, TRUE);
  }

}
