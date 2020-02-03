<?php

namespace Drupal\alshaya_stores_finder_transac;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class StoresFinderManager.
 *
 * @package Drupal\alshaya_stores_finder_transac
 */
class StoresFinderManager {

  /**
   * Alshaya API Wrapper service object.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $api;

  /**
   * Stores Finder Utility service object.
   *
   * @var \Drupal\alshaya_stores_finder_transac\StoresFinderUtility
   */
  protected $utility;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Logger service object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * StoresFinderManager constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshaya_api_wrapper
   *   Alshaya API Wrapper service object.
   * @param \Drupal\alshaya_stores_finder_transac\StoresFinderUtility $utility
   *   Stores Finder Utility service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger service object.
   */
  public function __construct(
    AlshayaApiWrapper $alshaya_api_wrapper,
    StoresFinderUtility $utility,
    LanguageManagerInterface $language_manager,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->api = $alshaya_api_wrapper;
    $this->utility = $utility;
    $this->languageManager = $language_manager;
    $this->logger = $logger_factory->get('alshaya_stores_finder_transac');
  }

  /**
   * Function to sync all stores.
   *
   * @return bool
   *   Flag to specify if sync was successful or not.
   */
  public function syncStores() {
    $stored_synced = FALSE;

    $store_locator_ids = [];

    // Do API call to get stores for each language.
    foreach ($this->languageManager->getLanguages() as $langcode => $language) {
      // Get all stores for particular language.
      $stores = $this->api->getStores($langcode);

      if ($stores && is_array($stores) && !empty($stores['items'])) {
        // Loop through all the stores and add/edit/translate the store node.
        foreach ($stores['items'] as $store) {
          $this->utility->updateStore($store, $langcode);

          // Store code will be unique for node/language.
          $store_locator_ids[] = $store['store_code'];

          // If we update even single store, we return TRUE.
          $stored_synced = TRUE;
        }
      }
    }

    // If there is at least one store id.
    if (!empty($store_locator_ids)) {
      // Get orphan store node ids.
      $orphan_store_nids = $this->utility->getOrphanStores($store_locator_ids);
      // Delete orphan stores.
      $this->utility->deleteStores($orphan_store_nids);
    }

    if ($stored_synced) {
      $this->logger->notice('Stores sync completed.');
    }
    else {
      $this->logger->error('Either no stores found or error occurred while doing stores sync. Please check logs for more details.');
    }

    return $stored_synced;
  }

}
