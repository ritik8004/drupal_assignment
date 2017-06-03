<?php

namespace Drupal\alshaya_stores_finder;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class StoresFinderUtility.
 */
class StoresFinderUtility {

  /**
   * AlshayaApiWrapper service object.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * AlshayaApiWrapper service object.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $nodeStorage;

  /**
   * Constructs a new StoresFinderUtility object.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   AlshayaApiWrapper service object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(AlshayaApiWrapper $api_wrapper, EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger_factory) {
    $this->apiWrapper = $api_wrapper;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->logger = $logger_factory->get('alshaya_stores_finder');
  }

  /**
   * Utility function to get store node from store code.
   *
   * @param string $store_code
   *   Store code.
   *
   * @return \Drupal\node\Entity\Node
   *   Store node.
   */
  public function getStoreFromCode($store_code) {

    $query = $this->nodeStorage->getQuery();
    $query->condition('field_store_locator_id', $store_code);
    $ids = $query->execute();

    // No stores found.
    if (count($ids) === 0) {
      $this->logger->error('No store node found for store code: @store_code.', ['@store_code' => $store_code]);
      return NULL;
    }
    // Some issue in DATA.
    elseif (count($ids) > 1) {
      $this->logger->error('Multiple store nodes found for store code: @store_code.', ['@store_code' => $store_code]);
    }

    // Get the first id.
    $nid = array_shift($ids);

    return $this->nodeStorage->load($nid);
  }

  /**
   * Function to get stores for a product variant near the user's location.
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
  public function getSkuStores($sku, $lat, $lon) {
    $stores = [];

    $stores_data = $this->apiWrapper->getProductStores($sku, $lat, $lon);

    foreach ($stores_data as $store_data) {
      $store = [];

      $store['code'] = $store_data['code'];
      $store['distance'] = $store_data['distance'];
      $store['rnc_available'] = $store_data['rnc_available'];
      $store['sts_available'] = $store_data['sts_available'];
      $store['sts_delivery_time_label'] = $store_data['sts_delivery_time_label'];
      $store['low_stock'] = $store_data['low_stock'];

      if ($store_node = $this->getStoreFromCode($store_data['code'])) {

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

    // Sort the stores first by distance and then by name.
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

}
