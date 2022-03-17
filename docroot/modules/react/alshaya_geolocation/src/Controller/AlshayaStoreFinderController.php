<?php

namespace Drupal\alshaya_geolocation\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Alshaya Top Up Controller.
 */
class AlshayaStoreFinderController extends ControllerBase {

  /**
   * Store Finder controller.
   */
  public function store() {
    // For transac.
    $api_url = '/alshaya-locations/stores-list';
    $library = [
      'alshaya_geolocation/alshaya-store-finder',
      'alshaya_white_label/store_finder',
    ];
    // For non-transac.
    if (drupal_get_profile() == 'alshaya_non_transac') {
      $api_url = '/alshaya-locations/local';
      $library = [
        'alshaya_geolocation/alshaya-store-finder',
      ];
    }
    return [
      '#type' => 'markup',
      '#markup' => '<div id="store-finder-wrapper"></div>',
      '#attached' => [
        'library' => $library,
        'drupalSettings' => [
          'cac' => [
            'apiUrl' => $api_url,
          ],
        ],
      ],
    ];
  }

  /**
   * Store finder list controller.
   */
  public function storeList() {
    // For transac.
    $api_url = '/alshaya-locations/stores-list';
    $library = [
      'alshaya_geolocation/alshaya-store-finder-list',
      'alshaya_white_label/store_finder',
    ];
    // For non-transac.
    if (drupal_get_profile() == 'alshaya_non_transac') {
      $api_url = '/alshaya-locations/local';
      $library = [
        'alshaya_geolocation/alshaya-store-finder-list',
      ];
    }
    return [
      '#type' => 'markup',
      '#markup' => '<div id="store-finder-list-wrapper"></div>',
      '#attached' => [
        'library' => $library,
        'drupalSettings' => [
          'cac' => [
            'apiUrl' => $api_url,
          ],
        ],
      ],
    ];
  }

}
