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

    $api_url = '/alshaya-locations/stores-list';
    if (drupal_get_profile() == 'alshaya_non_transac') {
      $api_url = '/alshaya-locations/local';
    }
    return [
      '#type' => 'markup',
      '#markup' => '<div id="store-finder-wrapper"></div>',
      '#attached' => [
        'library' => [
          'alshaya_geolocation/alshaya-store-finder',
          'alshaya_white_label/store_finder',
        ],
        'drupalSettings' => [
          'cnc' => [
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
    return [
      '#type' => 'markup',
      '#markup' => '<div id="store-finder-list-wrapper"></div>',
      '#attached' => [
        'library' => [
          'alshaya_geolocation/alshaya-store-finder-list',
          'alshaya_white_label/store_finder',
        ],
        'drupalSettings' => [
          'cnc' => [
            'apiUrl' => $api_url,
          ],
        ],
      ],
    ];
  }

}
