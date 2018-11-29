<?php

namespace Drupal\alshaya_acm_product\Commands;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Class AlshayaAddressBookCommands.
 *
 * @package Drupal\alshaya_acm_product\Commands
 */
class AlshayaAcmProductCommands extends DrushCommands {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * AlshayaAcmProductCommands constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   Logger Channel Factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_channel_factory,
                              ConfigFactoryInterface $config_factory,
                              SkuManager $sku_manager) {
    $this->logger = $logger_channel_factory->get('alshaya_acm_product');
    $this->configFactory = $config_factory;
    $this->skuManager = $sku_manager;
  }

  /**
   * Aggregate configurable products on listing pages.
   *
   * @command alshaya_acm_product:listing-aggregate-products
   *
   * @aliases listing-aggregate-products
   */
  public function aggregateListing() {
    $mode = $this->skuManager->getListingDisplayMode();

    if ($mode === 'all') {
      $message = 'Current mode is already set to display one product per configurable in listing pages.';
      $this->logger->info($message);
      $this->yell($message);

      $ask = 'Are you sure you want to redo node deletion? Type "all" if you are sure.';
    }
    else {
      $ask = 'Are you sure you want to switch to one product per configurable in listing pages? Type "all" if you are sure.';
    }

    $confirmation = $this->ask($ask);
    if ($confirmation !== 'all') {
      return;
    }

    // Update mode.
    $this->updateListingMode('all');

    // Reset index.
    drush_invoke_process('@self', 'sapi-r');

    // Delete color nodes.
    $batch = [
      'title' => 'Delete color nodes',
      'init_message' => 'Deleting color nodes...',
      'progress_message' => 'Processed @current out of @total.',
      'error_message' => 'Error occurred while deleting color nodes, please check logs.',
      'operations' => [
        ['_alshaya_acm_product_delete_configurable_nodes_for_color', []],
      ],
    ];

    batch_set($batch);

    // Process the batch.
    drush_backend_batch_process();

    $message = 'Updated mode to display one product per configurable in listing pages.';
    $this->logger->info($message);
    $this->say($message);
  }

  /**
   * Split configurable products on listing pages.
   *
   * @command alshaya_acm_product:listing-split-products
   *
   * @aliases listing-split-products
   */
  public function splitListing() {
    $mode = $this->skuManager->getListingDisplayMode();

    if ($mode === 'group_by_color') {
      $message = 'Current mode is already set to display one product per color in listing pages.';
      $this->logger->info($message);
      $this->yell($message);

      $ask = 'Are you sure you want to redo node creation? Type "group_by_color" if you are sure.';
    }
    else {
      $ask = 'Are you sure you want to switch to one product per color in listing pages? Type "group_by_color" if you are sure.';
    }

    $confirmation = $this->ask($ask);
    if ($confirmation !== 'group_by_color') {
      return;
    }

    // Update mode.
    $this->updateListingMode('group_by_color');

    // Reset index.
    drush_invoke_process('@self', 'sapi-r');

    // Create color nodes.
    $batch = [
      'title' => 'Create color nodes',
      'init_message' => 'Creating color nodes...',
      'progress_message' => 'Processed @current out of @total.',
      'error_message' => 'Error occurred while creating color nodes, please check logs.',
      'operations' => [
        ['_alshaya_acm_product_create_configurable_nodes_for_color', []],
      ],

    ];

    batch_set($batch);

    // Process the batch.
    drush_backend_batch_process();

    $message = 'Updated mode to display one product per color in listing pages.';
    $this->logger->info($message);
    $this->say($message);
  }

  /**
   * Helper function to update config for listing mode.
   *
   * @param string $mode
   *   New mode to set in config.
   */
  private function updateListingMode(string $mode) {
    $config = $this->configFactory->getEditable('alshaya_acm_product.display_settings');
    $config->set('listing_display_mode', $mode);
    $config->save();

    // Reset static caches.
    drupal_static_reset();
  }

}
