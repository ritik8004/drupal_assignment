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
    return [
      '#type' => 'markup',
      '#markup' => '<div id="store-finder-wrapper"></div>',
      '#attached' => [
        'library' => [
          'alshaya_geolocation/alshaya-store-finder',
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
        ],
      ],
    ];
  }

}
