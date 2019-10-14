<?php

namespace AlshayaMiddleware\Drupal;

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
      $url = $this->drupalInfo->getDrupalUrl() . '/' . sprintf('sku-details/%s', $sku);
      $response = $client->request('GET', $url, ['verify' => FALSE]);
      $result = $response->getBody()->getContents();
      $data = array_merge($data, json_decode($result, TRUE));
    }

    return $data;
  }

}
