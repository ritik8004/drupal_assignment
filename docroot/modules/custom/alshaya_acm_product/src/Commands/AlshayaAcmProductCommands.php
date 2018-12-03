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
      $this->yell($message, 40, 'red');

      $ask = 'Are you sure you want to redo node deletion? Type "ok" if you are sure.';
    }
    else {
      $ask = 'Are you sure you want to switch to one product per configurable in listing pages? Type "all" if you are sure.';
    }

    $confirmation = $this->ask($ask);
    if ($confirmation !== 'ok') {
      return;
    }

    // Update mode.
    $this->updateListingMode('all');

    // Clear all indexed data.
    drush_invoke_process('@self', 'sapi-c');

    // Delete color nodes.
    $batch = [
      'title' => 'Delete color nodes',
      'init_message' => 'Deleting color nodes...',
      'progress_message' => 'Processed @current out of @total.',
      'error_message' => 'Error occurred while deleting color nodes, please check logs.',
      'operations' => [
        [[__CLASS__, 'deleteColorNodes'], []],
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
      $this->yell($message, 40, 'red');

      $ask = 'Are you sure you want to redo node creation? Type "ok" if you are sure.';
    }
    else {
      $ask = 'Are you sure you want to switch to one product per color in listing pages? Type "ok" if you are sure.';
    }

    $confirmation = $this->ask($ask);
    if ($confirmation !== 'ok') {
      return;
    }

    // Update mode.
    $this->updateListingMode('group_by_color');

    // Clear all indexed data.
    drush_invoke_process('@self', 'sapi-c');

    // Create color nodes.
    $batch = [
      'title' => 'Create color nodes',
      'init_message' => 'Creating color nodes...',
      'progress_message' => 'Processed @current out of @total.',
      'error_message' => 'Error occurred while creating color nodes, please check logs.',
      'operations' => [
        [[__CLASS__, 'createColorNodes'], []],
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

  /**
   * Batch callback to create color nodes when switching to split listing.
   *
   * @param mixed $context
   *   Batch context.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function createColorNodes(&$context) {
    $storage = \Drupal::entityTypeManager()->getStorage('node');

    if (empty($context['sandbox'])) {
      $query = $storage->getQuery();
      $query->condition('type', 'acq_product');
      $query->exists('field_skus');
      $context['sandbox']['result'] = array_chunk($query->execute(), 100);
      $context['sandbox']['max'] = count($context['sandbox']['result']);
      $context['sandbox']['current'] = 0;
    }

    if (empty($context['sandbox']['result'])) {
      $context['finished'] = 1;
      return;
    }

    /** @var \Drupal\alshaya_acm_product\SkuManager $skuManager */
    $skuManager = \Drupal::service('alshaya_acm_product.skumanager');

    $nids = array_shift($context['sandbox']['result']);

    foreach ($nids as $nid) {
      /** @var \Drupal\node\NodeInterface $node */
      $node = $storage->load($nid);

      foreach ($node->getTranslationLanguages() as $language) {
        $translationNode = $node->getTranslation($language->getId());
        $skuManager->processColorNodesForConfigurable($translationNode);
      }

      // Reset static caches, we won't need it again.
      $storage->resetCache();
      drupal_static_reset('loadFromSku');
    }

    $context['sandbox']['current']++;
    $context['finished'] = $context['sandbox']['current'] / $context['sandbox']['max'];
  }

  /**
   * Batch callback to delete color nodes when switching to aggregated listing.
   *
   * @param mixed $context
   *   Batch context.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function deleteColorNodes(&$context) {
    $storage = \Drupal::entityTypeManager()->getStorage('node');

    if (empty($context['sandbox'])) {
      $query = $storage->getQuery();
      $query->condition('type', 'acq_product');
      $query->exists('field_product_color');
      $context['sandbox']['result'] = array_chunk($query->execute(), 250);
      $context['sandbox']['max'] = count($context['sandbox']['result']);
      $context['sandbox']['current'] = 0;
    }

    if (empty($context['sandbox']['result'])) {
      $context['finished'] = 1;
      return;
    }

    $nids = array_shift($context['sandbox']['result']);

    foreach ($nids as $nid) {
      $node = $storage->load($nid);
      $node->delete();
    }

    $context['sandbox']['current']++;
    $context['finished'] = $context['sandbox']['current'] / $context['sandbox']['max'];
  }

}
