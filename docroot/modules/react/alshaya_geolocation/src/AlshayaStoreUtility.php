<?php

namespace Drupal\alshaya_geolocation;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;

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
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * AlshayaStoreFinder constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   */
  public function __construct(ConfigFactoryInterface $configFactory, LanguageManagerInterface $languageManager) {
    $this->configFactory = $configFactory;
    $this->languageManager = $languageManager;
  }

  /**
   * Store finder list labels.
   */
  public function storeLabels() {
    $labels = [];
    $langCode = $this->languageManager->getCurrentLanguage()->getId();
    $translatedConfig = $this->languageManager->getLanguageConfigOverride($langCode, 'alshaya_stores_finder.settings');
    $config = $this->configFactory->getEditable('alshaya_stores_finder.settings');
    $labels['search_proximity_radius'] = $config->get('search_proximity_radius');
    $labels['store_list_label'] = $translatedConfig->get('store_list_label') ?? $config->get('store_list_label');
    $labels['search_placeholder'] = $translatedConfig->get('store_search_placeholder') ?? $config->get('store_search_placeholder');
    $labels['load_more_item_limit'] = $config->get('load_more_item_limit');

    $labels['apiUrl'] = "/$langCode/alshaya-locations/stores-list";

    return $labels;
  }

  /**
   * Store finder list labels.
   */
  public function storeLibraries($transac = TRUE) {
    if ($transac) {
      return [
        'alshaya_white_label/store_finder',
        'alshaya_geolocation/marker-dropdown',
      ];
    }
    return [
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
  }

}
