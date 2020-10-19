<?php

namespace Drupal\alshaya_stores_finder_transac\Commands;

use Drupal\alshaya_stores_finder_transac\StoresFinderManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Alshaya Store Finder Commands class.
 */
class AlshayaStoreFinderCommands extends DrushCommands {

  /**
   * Stores finder manager.
   *
   * @var \Drupal\alshaya_stores_finder_transac\StoresFinderManager
   */
  private $storesFinderManager;

  /**
   * AlshayaStoreFinderCommands constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   * @param \Drupal\alshaya_stores_finder_transac\StoresFinderManager $storesFinderManager
   *   Stores finder Manager.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory,
                              StoresFinderManager $storesFinderManager) {
    $this->logger = $logger_factory->get('alshaya_stores_finder');
    $this->storesFinderManager = $storesFinderManager;
  }

  /**
   * Imports all stores into Drupal using direct Magento API.
   *
   * @command alshaya_stores_finder_transac:sync-stores
   *
   * @validate-module-enabled alshaya_stores_finder_transac
   *
   * @aliases aass,sync-stores
   */
  public function syncStores() {
    $this->logger->info(dt('Synchronizing all stores, please wait...'));

    $this->storesFinderManager->syncStores();
  }

}
