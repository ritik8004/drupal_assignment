<?php

namespace Drupal\alshaya_click_collect\Service;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\alshaya_stores_finder_transac\StoresFinderUtility;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class AlshayaClickCollect.
 *
 * @package Drupal\alshaya_click_collect\Service
 */
class AlshayaClickCollect {

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
   * Logger service object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * AlshayaClickCollect constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   AlshayaApiWrapper service object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\alshaya_stores_finder_transac\StoresFinderUtility $stores_finder_utility
   *   Stores Finder Utility service object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger service object.
   */
  public function __construct(
    AlshayaApiWrapper $api_wrapper,
    ConfigFactoryInterface $config_factory,
    StoresFinderUtility $stores_finder_utility,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->apiWrapper = $api_wrapper;
    $this->storesFinderUtility = $stores_finder_utility;
    $this->configFactory = $config_factory;
    $this->logger = $logger_factory->get('alshaya_acm_product');
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
    // Get the stores from Magento.
    if ($stores = $this->apiWrapper->getCartStores($cart_id, $lat, $lon)) {
      $config = $this->configFactory->get('alshaya_click_collect.settings');

      foreach ($stores as $index => &$store) {
        $store['rnc_available'] = (int) $store['rnc_available'];
        $store['sts_available'] = (int) $store['sts_available'];

        if ($extra_data = $this->storesFinderUtility->getStoreExtraData($store)) {
          $store = array_merge($store, $extra_data);

          if (!empty($store['rnc_available'])) {
            $store['delivery_time'] = $config->get('click_collect_rnc');
          }
        }
        else {
          // We don't display the stores which are not in our system.
          unset($stores[$index]);

          // Log into Drupal for admins to check and take required action.
          $this->logger->warning('Received a store in Cart Stores API response which is not yet available in Drupal. Store code: %store_code', [
            '%store_code' => $store['code'],
          ]);
        }
      }

      return $stores;
    }

    return [];
  }

}
