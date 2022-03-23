<?php

namespace Drupal\alshaya_geolocation;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * AlshayaStoreUtility object contains utility functions.
 */
class AlshayaStoreUtility {

  /**
   * Config object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AlshayaStoreFinder constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config object.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Store finder list labels.
   */
  public function storeLabels($transac = TRUE) {
    $labels = [];
    $config = $this->configFactory->getEditable('alshaya_stores_finder.settings');
    $labels['search_proximity_radius'] = $config->get('search_proximity_radius');
    $labels['store_list_label'] = $config->get('store_list_label');
    $labels['search_placeholder'] = $config->get('store_search_placeholder');
    $labels['load_more_item_limit'] = $config->get('load_more_item_limit');
    if ($transac) {
      $labels['apiUrl'] = '/alshaya-locations/stores-list';
      return $labels;
    }
    $labels['apiUrl'] = '/alshaya-locations/local';
    return $labels;
  }

  /**
   * Store finder list labels.
   */
  public function storeLibraries($transac = TRUE) {
    $libraries = [];
    if ($transac) {
      $libraries = [
        'alshaya_white_label/store_finder',
        'alshaya_geolocation/marker-dropdown',
      ];
      return $libraries;
    }
    $libraries = [
      'whitelabel/view-store-list-locator',
      'whitelabel/views-unformatted-store-finder-glossary',
      'whitelabel/block-store-finder-form',
      'whitelabel/views-store-finder-glossary',
      'whitelabel/field-store-open-hours',
      'whitelabel/node-individual-store',
      'whitelabel/block-footer-store',
      'whitelabel/block-header-store',
      'whitelabel/view-store-finder-list',
    ];
    return $libraries;
  }

}
