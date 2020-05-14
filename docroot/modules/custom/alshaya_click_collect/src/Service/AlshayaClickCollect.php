<?php

namespace Drupal\alshaya_click_collect\Service;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\alshaya_stores_finder_transac\StoresFinderUtility;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class AlshayaClickCollect.
 *
 * @package Drupal\alshaya_click_collect\Service
 */
class AlshayaClickCollect {

  use StringTranslationTrait;

  /**
   * AlshayaApiWrapper service object.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Stores Finder Utility service object.
   *
   * @var \Drupal\alshaya_stores_finder_transac\StoresFinderUtility
   */
  protected $storesFinderUtility;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AlshayaClickCollect constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   AlshayaApiWrapper service object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\alshaya_stores_finder_transac\StoresFinderUtility $stores_finder_utility
   *   Stores Finder Utility service object.
   */
  public function __construct(
    AlshayaApiWrapper $api_wrapper,
    ConfigFactoryInterface $config_factory,
    StoresFinderUtility $stores_finder_utility
  ) {
    $this->apiWrapper = $api_wrapper;
    $this->storesFinderUtility = $stores_finder_utility;
    $this->configFactory = $config_factory;
  }

  /**
   * Function to get the cart stores.
   *
   * @param int $cart_id
   *   The cart id.
   * @param float $lat
   *   The latitude.
   * @param float $lon
   *   The longitude.
   *
   * @return array
   *   Return the array of all available stores.
   */
  public function getCartStores($cart_id, $lat = NULL, $lon = NULL) {
    if ($this->getConfig()->get('feature_status') === 'disabled') {
      return [];
    }

    // Get the stores from Magento.
    if ($cart_stores = $this->apiWrapper->getCartStores($cart_id, $lat, $lon)) {
      $stores_by_code = [];
      $config_cnc_rnc = $this->configFactory->get('alshaya_click_collect.settings')->get('click_collect_rnc');;
      foreach ($cart_stores as $cart_store) {
        $stores_by_code[$cart_store['code']] = $cart_store;
      }
      $stores = $this->storesFinderUtility->getMultipleStoresExtraData($stores_by_code);
      foreach ($stores as &$store) {
        $store_cart_data = $stores_by_code[$store['code']];
        $store['rnc_available'] = (int) $store_cart_data['rnc_available'];
        $store['sts_available'] = (int) $store_cart_data['sts_available'];
        $store['distance'] = (float) $store_cart_data['distance'];
        $store['formatted_distance'] = $this->t('@distance miles', [
          '@distance' => number_format((float) $store_cart_data['distance'], 2, '.', ''),
        ]);

        // Display sts label by default.
        $store['delivery_time'] = $store_cart_data['sts_delivery_time_label'];

        // Display configured value for rnc if available.
        if ($store['rnc_available']) {
          $store['delivery_time'] = $config_cnc_rnc;
        }
      }

      // Sort the stores first by distance and then by name.
      alshaya_master_utility_usort($stores, 'rnc_available', 'desc', 'distance', 'asc');
      return $stores;
    }

    return [];
  }

  /**
   * Get store info for given store code.
   *
   * @param string $store_code
   *   The store code.
   *
   * @return array
   *   Return array of store related info.
   */
  public function getStoreInfo(string $store_code) {
    return $this->storesFinderUtility->getMultipleStoresExtraData([$store_code => []]);
  }

  /**
   * Wrapper function to get config.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   Click and collect config.
   */
  protected function getConfig() {
    static $config;

    if (empty($config)) {
      $config = $this->configFactory->get('alshaya_click_collect.settings');
    }

    return $config;
  }

}
