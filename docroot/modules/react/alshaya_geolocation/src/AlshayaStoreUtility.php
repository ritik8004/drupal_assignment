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
  public function storeLabels() {
    $labels = [];
    $config = $this->configFactory->getEditable('alshaya_stores_finder.settings');
    $labels['search_proximity_radius'] = $config->get('search_proximity_radius');
    $labels['store_list_label'] = $config->get('store_list_label');
    $labels['search_placeholder'] = $config->get('store_search_placeholder');
    return $labels;
  }

}
