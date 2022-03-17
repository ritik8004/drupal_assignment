<?php

namespace Drupal\alshaya_geolocation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Alshaya Top Up Controller.
 */
class AlshayaStoreFinderController extends ControllerBase {

  /**
   * Config object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('config.factory'));
  }

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
   * Store Finder controller.
   */
  public function store() {
    $labels = $this->storeLabels();
    // For transac.
    $labels['apiUrl'] = '/alshaya-locations/stores-list';
    $library = [
      'alshaya_geolocation/alshaya-store-finder',
      'alshaya_white_label/store_finder',
    ];
    // For non-transac.
    if (drupal_get_profile() == 'alshaya_non_transac') {
      $labels['apiUrl'] = '/alshaya-locations/local';
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
          'cac' => $labels,
        ],
      ],
    ];
  }

  /**
   * Store finder list controller.
   */
  public function storeList() {
    $labels = $this->storeLabels();
    // For transac.
    $labels['apiUrl'] = '/alshaya-locations/stores-list';
    $library = [
      'alshaya_geolocation/alshaya-store-finder-list',
      'alshaya_white_label/store_finder',
    ];
    // For non-transac.
    if (drupal_get_profile() == 'alshaya_non_transac') {
      $labels['apiUrl'] = '/alshaya-locations/local';
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
          'cac' => $labels,
        ],
      ],
    ];
  }

  /**
   * Store finder list labels.
   */
  public function storeLabels() {
    $labels = [];
    $config = $this->configFactory->getEditable('alshaya_stores_finder.settings');
    $labels['search_proximity_radius'] = $config->get('search_proximity_radius');
    $labels['store_list_label'] = $config->get('store_list_label');
    $labels['store_search_placeholder'] = $config->get('store_search_placeholder');
    return $labels;
  }

}
