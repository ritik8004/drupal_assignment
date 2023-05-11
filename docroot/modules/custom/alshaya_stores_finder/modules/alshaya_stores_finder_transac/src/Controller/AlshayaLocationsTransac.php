<?php

namespace Drupal\alshaya_stores_finder_transac\Controller;

use Drupal\alshaya_stores_finder_transac\StoresFinderUtility;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Alshaya Locations Controller Transac.
 */
class AlshayaLocationsTransac extends ControllerBase {

  /**
   * Stores Finder Utility service object.
   *
   * @var \Drupal\alshaya_stores_finder_transac\StoresFinderUtility
   */
  protected $storeFinderUtility;

  /**
   * AlshayaLocationsTransac constructor.
   *
   * @param \Drupal\alshaya_stores_finder_transac\StoresFinderUtility $stores_finder_utility
   *   Stores Finder Utility service object.
   */
  public function __construct(StoresFinderUtility $stores_finder_utility) {
    $this->storeFinderUtility = $stores_finder_utility;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_stores_finder_transac.utility')
    );
  }

  /**
   * Stores list for the brand transac site.
   *
   * @return object
   *   Stores list fetched from the respective MDC API.
   */
  public function stores() {
    $stores = $this->storeFinderUtility->getStores();

    return new JsonResponse($stores);
  }

}
