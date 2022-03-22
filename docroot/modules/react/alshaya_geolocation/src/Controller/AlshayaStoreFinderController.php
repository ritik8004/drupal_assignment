<?php

namespace Drupal\alshaya_geolocation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_geolocation\AlshayaStoreUtility;

/**
 * Alshaya Top Up Controller.
 */
class AlshayaStoreFinderController extends ControllerBase {

  /**
   * Config object.
   *
   * @var \Drupal\alshaya_geolocation\AlshayaStoreUtility
   */
  protected $storeUtility;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('alshaya_geolocation.store_utility'));
  }

  /**
   * AlshayaStoreFinder constructor.
   *
   * @param \Drupal\alshaya_geolocation\AlshayaStoreUtility $storeUtility
   *   Config object.
   */
  public function __construct(AlshayaStoreUtility $storeUtility) {
    $this->storeUtility = $storeUtility;
  }

  /**
   * Store Finder controller.
   */
  public function store() {
    $labels = $this->storeUtility->storeLabels();
    // For transac.
    $labels['apiUrl'] = '/alshaya-locations/stores-list';
    $library = [
      'alshaya_geolocation/alshaya-store-finder',
      'alshaya_white_label/store_finder',
      'alshaya_geolocation/marker-dropdown',
    ];
    // For non-transac.
    if (drupal_get_profile() == 'alshaya_non_transac') {
      $labels['apiUrl'] = '/alshaya-locations/local';
      $library = [
        'alshaya_geolocation/alshaya-store-finder',
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
    return [
      '#type' => 'markup',
      '#markup' => '<div id="store-finder-wrapper"></div>',
      '#attached' => [
        'library' => $library,
        'drupalSettings' => [
          'storeLabels' => $labels,
        ],
      ],
    ];
  }

  /**
   * Store finder list controller.
   */
  public function storeList() {
    $labels = $this->storeUtility->storeLabels();
    // For transac.
    $labels['apiUrl'] = '/alshaya-locations/stores-list';
    $library = [
      'alshaya_geolocation/alshaya-store-finder-list',
      'alshaya_white_label/store_finder',
      'alshaya_geolocation/marker-dropdown',
    ];
    // For non-transac.
    if (drupal_get_profile() == 'alshaya_non_transac') {
      $labels['apiUrl'] = '/alshaya-locations/local';
      $library = [
        'alshaya_geolocation/alshaya-store-finder-list',
        'whitelabel/view-store-list-locator',
        'whitelabel/view-store-finder-list',
        'whitelabel/field-store-open-hours',
        'whitelabel/block-store-finder-form',
        'whitelabel/views-unformatted-store-finder-glossary',
        'whitelabel/views-store-finder-glossary',
        'whitelabel/node-individual-store',
        'whitelabel/block-footer-store',
        'whitelabel/block-header-store',
      ];
    }
    return [
      '#type' => 'markup',
      '#markup' => '<div id="store-finder-list-wrapper"></div>',
      '#attached' => [
        'library' => $library,
        'drupalSettings' => [
          'storeLabels' => $labels,
        ],
      ],
    ];
  }

}
