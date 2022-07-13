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
   * Current profile.
   *
   * @var string
   */
  protected $installProfile;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_geolocation.store_utility'),
      $container->getParameter('install_profile'),
    );
  }

  /**
   * AlshayaStoreFinder constructor.
   *
   * @param \Drupal\alshaya_geolocation\AlshayaStoreUtility $storeUtility
   *   Config object.
   * @param string $installProfile
   *   The current installation profile.
   */
  public function __construct(AlshayaStoreUtility $storeUtility, $installProfile) {
    $this->storeUtility = $storeUtility;
    $this->installProfile = $installProfile;
  }

  /**
   * Store Finder controller.
   */
  public function store() {
    $labels = $this->storeUtility->storeLabels();
    // Site specific libraries.
    if ($this->installProfile == 'alshaya_non_transac') {
      $libraries = $this->storeUtility->storeLibraries(FALSE);
    }
    else {
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
    $labels = $this->storeUtility->storeLabels();
    // Site specific libraries.
    if ($this->installProfile == 'alshaya_non_transac') {
      $libraries = $this->storeUtility->storeLibraries(FALSE);
    }
    else {
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
