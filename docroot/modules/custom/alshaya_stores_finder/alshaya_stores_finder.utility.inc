<?php

/**
 * @file
 * Utility file to play with stores data.
 */

/**
 * Utility function to get store node from store code.
 *
 * @param string $store_code
 *   Store code.
 *
 * @return \Drupal\node\Entity\Node|Bykk
 *   Store node.
 */
function alshaya_stores_finder_get_store_from_code($store_code) {

  $query = \Drupal::entityQuery('node');
  $query->condition('field_store_locator_id', $store_code);
  $ids = $query->execute();

  // No stores found.
  if (count($ids) === 0) {
    \Drupal::logger('alshaya_stores_finder')->error('No store node found for store code: @store_code.', ['@store_code' => $store_code]);
    return NULL;
  }
  // Some issue in DATA.
  elseif (count($ids) > 1) {
    \Drupal::logger('alshaya_stores_finder')->error('Multiple store nodes found for store code: @store_code.', ['@store_code' => $store_code]);
  }

  // Get the first id.
  $nid = array_shift($ids);

  return \Drupal::entityTypeManager()->getStorage('node')->load($nid);
}

/**
 * Function to get stores for a product near the user's location.
 *
 * @param string $sku
 *   Product SKU.
 * @param float $lat
 *   Latitude.
 * @param float $lon
 *   Longitude.
 *
 * @return array
 *   Stores array.
 */
function alshaya_stores_finder_get_product_stores($sku, $lat, $lon) {
  $stores = [];

  /** @var \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper */
  $api_wrapper = \Drupal::service('alshya_api.api');

  $stores_data = $api_wrapper->getProductStores($sku, $lat, $lon);

  foreach ($stores_data as $store_data) {

    if ($store_node = alshaya_stores_finder_get_store_from_code($store_data['code'])) {
      $store = [];

      $store['name'] = $store_node->label();
      $store['code'] = $store_node->get('field_store_locator_id')->getString();
      $store['address'] = $store_node->get('field_store_address')->getString();
      $store['opening_hours'] = $store_node->get('field_store_open_hours')->getString();

      if ($lat_lon = $store->get('field_latitude_longitude')->getValue()) {
        $store['lat'] = $lat_lon[0]['lat'];
        $store['lon'] = $lat_lon[0]['lon'];
      }

      $store['distance'] = $store_data['distance'];
      $store['rnc_available'] = $store_data['rnc_available'];
      $store['sts_available'] = $store_data['sts_available'];
      $store['low_stock'] = $store_data['low_stock'];

      $stores[$store_node->id()] = $store;
    }
  }

  return $stores;
}
