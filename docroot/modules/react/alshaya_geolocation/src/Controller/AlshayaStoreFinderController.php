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
    // For non-transac.
    if (drupal_get_profile() == 'alshaya_non_transac') {
      $labels = $this->storeUtility->storeLabels(FALSE);
      $libraries = $this->storeUtility->storeLibraries(FALSE);
    }
    else {
      // For transac.
      $labels = $this->storeUtility->storeLabels();
      $libraries = $this->storeUtility->storeLibraries();
    }
    $libraries[] = 'alshaya_geolocation/alshaya-store-finder';
    return [
      '#type' => 'markup',
      '#markup' => '<div id="store-finder-wrapper"></div>',
      '#attached' => [
        'library' => $libraries,
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
    // For non-transac.
    if (drupal_get_profile() == 'alshaya_non_transac') {
      $labels = $this->storeUtility->storeLabels(FALSE);
      $libraries = $this->storeUtility->storeLibraries(FALSE);
    }
    else {
      // For transac.
      $labels = $this->storeUtility->storeLabels();
      $libraries = $this->storeUtility->storeLibraries();
    }
    $libraries[] = 'alshaya_geolocation/alshaya-store-finder-list';
    return [
      '#type' => 'markup',
      '#markup' => '<div id="store-finder-list-wrapper"></div>',
      '#attached' => [
        'library' => $libraries,
        'drupalSettings' => [
          'storeLabels' => $labels,
        ],
      ],
    ];
  }

}
