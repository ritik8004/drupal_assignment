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
   * @param bool $log_not_found
   *   Log errors when store not found. Can be false during sync.
   *
   * @return \Drupal\node\Entity\Node
   *   Store node.
   */
  public function getStoreFromCode($store_code, $log_not_found = TRUE) {

    $query = $this->nodeStorage->getQuery();
    $query->condition('field_store_locator_id', $store_code);
    $ids = $query->execute();

    // No stores found.
    if (count($ids) === 0) {
      if ($log_not_found) {
        $this->logger->error('No store node found for store code: @store_code.', ['@store_code' => $store_code]);
      }
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
    $langcode = \Drupal::service('language_manager')->getCurrentLanguage()->getId();

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
        if ($store_node->hasTranslation($langcode)) {
          $store_node = $store_node->getTranslation($langcode);
        }
        else {
          continue;
        }

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

  /**
   * Function to sync all stores.
   */
  public function syncStores() {
    // Prepare the alternate locale data.
    foreach (acq_commerce_get_store_language_mapping() as $lang => $store_id) {
      // Get all stores for particular store id.
      $stores = $this->apiWrapper->getStores($store_id);

      // Loop through all the stores and add/edit/translate the store node.
      foreach ($stores['items'] as $store) {
        $this->updateStore($store, $lang);
      }
    }
  }

  /**
   * Function to create/update single store.
   *
   * @param array $store
   *   Store array.
   * @param string $langcode
   *   Language code.
   */
  protected function updateStore(array $store, $langcode) {
    if ($node = $this->getStoreFromCode($store['store_code'], FALSE)) {
      if ($node->hasTranslation($langcode)) {
        $node = $node->getTranslation($langcode);
        $this->logger->info('Updating store @store_code and @langcode', ['@store_code' => $store['store_code'], 'langcode' => $store['langcode']]);
      }
      else {
        $node = $node->addTranslation($langcode);
        $this->logger->info('Adding @langcode translation for store @store_code', ['@store_code' => $store['store_code'], 'langcode' => $store['langcode']]);
      }
    }
    else {
      $node = $this->nodeStorage->create([
        'type' => 'store',
      ]);

      $node->get('langcode')->setValue($langcode);

      $node->get('field_store_locator_id')->setValue($store['store_code']);

      $this->logger->info('Creating store @store_code in @langcode', ['@store_code' => $store['store_code'], 'langcode' => $store['langcode']]);
    }

    if (!empty($store['store_name'])) {
      $node->get('title')->setValue($store['store_name']);
    }
    else {
      $node->get('title')->setValue($store['store_code']);
    }

    $node->get('field_latitude_longitude')->setValue(['lat' => $store['latitude'], 'lng' => $store['longitude']]);

    $node->get('field_store_phone')->setValue($store['store_phone']);

    if (isset($store['address'])) {
      $node->get('field_store_address')->setValue($store['address']);
    }
    else {
      $node->get('field_store_address')->setValue('');
    }

    // Set the status.
    $node->setPublished((bool) $store['status']);

    $node->save();
  }

}
