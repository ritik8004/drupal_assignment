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
  $api_wrapper = \Drupal::service('alshaya_api.api');

  $stores_data = $api_wrapper->getProductStores($sku, $lat, $lon);

  foreach ($stores_data as $store_data) {
    $store = [];

    $store['code'] = $store_data['code'];
    $store['distance'] = $store_data['distance'];
    $store['rnc_available'] = $store_data['rnc_available'];
    $store['sts_available'] = $store_data['sts_available'];
    $store['sts_delivery_time_label'] = $store_data['sts_delivery_time_label'];
    $store['low_stock'] = $store_data['low_stock'];

    if ($store_node = alshaya_stores_finder_get_store_from_code($store_data['code'])) {

      $store['name'] = $store_node->label();
      $store['code'] = $store_node->get('field_store_locator_id')->getString();
      $store['address'] = $store_node->get('field_store_address')->getString();
      $store['opening_hours'] = $store_node->get('field_store_open_hours')->getString();

      if ($lat_lon = $store->get('field_latitude_longitude')->getValue()) {
        $store['lat'] = $lat_lon[0]['lat'];
        $store['lon'] = $lat_lon[0]['lon'];
      }

      $stores[$store_node->id()] = $store;
    }
    // @TODO: Remove this once stores API is done.
    else {
      $store['name'] = $store['code'];
      $store['address'] = $store['code'];
      $store['opening_hours'] = $store['code'];
      $stores[$store['code']] = $store;
    }
  }

  alshaya_master_utility_usort($stores, 'distance', 'ASC', 'name', 'ASC');

  // Add sequence and proper delivery_time label and low stock text.
  foreach ($stores as $index => $store) {
    $stores[$index]['sequence'] = $index + 1;

    $time = $store['rnc_available'] ? t('1 hour') : $store['sts_delivery_time_label'];
    $stores[$index]['delivery_time'] = t('Collect from store in <em>@time</em>', ['@time' => $time]);

    $stores[$index]['low_stock_text'] = $store['low_stock'] ? t('Low stock') : '';
  }

  return $stores;
}
