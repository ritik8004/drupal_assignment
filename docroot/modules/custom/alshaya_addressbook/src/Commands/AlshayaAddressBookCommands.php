<?php

namespace Drupal\alshaya_addressbook\Commands;

use Drupal\alshaya_addressbook\AlshayaAddressBookManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Class Alshaya Address Book Commands.
 *
 * @package Drupal\alshaya_addressbook\Commands
 */
class AlshayaAddressBookCommands extends DrushCommands {

  /**
   * Logger Channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

  /**
   * Alshaya address book manager.
   *
   * @var \Drupal\alshaya_addressbook\AlshayaAddressBookManagerInterface
   */
  private $alshayaAddressBookManager;

  /**
   * AlshayaAddressBookCommands constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger Factory.
   * @param \Drupal\alshaya_addressbook\AlshayaAddressBookManagerInterface $alshayaAddressBookManager
   *   Alshaya address book manager.
   */
  public function __construct(LoggerChannelFactoryInterface $loggerChannelFactory,
                              AlshayaAddressBookManagerInterface $alshayaAddressBookManager) {
    $this->drupalLogger = $loggerChannelFactory->get('alshaya_addressbook');
    $this->alshayaAddressBookManager = $alshayaAddressBookManager;
  }

  /**
   * Imports all areas into Drupal using direct Magento API.
   *
   * @command alshaya_addressbook:sync-areas
   *
   * @aliases aasa,sync-areas,alshaya-addressbook-sync-areas
   */
  public function syncAreas() {
    // DM Version check.
    if ($this->alshayaAddressBookManager->getDmVersion() != AlshayaAddressBookManagerInterface::DM_VERSION_2) {
      $this->drupalLogger->error('Incorrect DM version for this command.');
      return;
    }

    // Update the area list.
    $this->drupalLogger->notice('Synchronizing all areas, please wait...');

    // Import DM data.
    $this->alshayaAddressBookManager->syncAreas();

    $this->drupalLogger->notice('Done.');
  }

  /**
   * Syncs and resets form fields cache.
   *
   * @command alshaya_addressbook:sync-form-fields
   *
   * @aliases aasff,sync-form-fields,alshaya-addressbook-sync-form-fields
   */
  public function syncFormFields() {
    // DM Version check.
    if ($this->alshayaAddressBookManager->getDmVersion() != AlshayaAddressBookManagerInterface::DM_VERSION_2) {
      $this->drupalLogger->error('Incorrect DM version for this command.');
      return;
    }

    // Reset magento form fields cache.
    $this->alshayaAddressBookManager->resetMagentoFormFields();
    // Invalidate checkout page cache.
    Cache::invalidateTags(['page_manager_route_name:alshaya_spc.checkout']);
    $this->drupalLogger->notice('Form fields cache reset.');
  }

}
