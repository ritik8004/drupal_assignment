<?php

namespace Drupal\alshaya_acm_product\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\file\FileInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class AlshayaAddressBookCommands.
 *
 * @package Drupal\alshaya_acm_product\Commands
 */
class AlshayaAcmProductCommands extends DrushCommands {

  /**
   * Event dispatched after each drush commmand.
   */
  const POST_DRUSH_COMMAND_EVENT = 'alshaya_acm_product.post_drush_command';

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
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private $eventDispatcher;

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * AlshayaAcmProductCommands constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   Logger Channel Factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_channel_factory,
                              ConfigFactoryInterface $config_factory,
                              SkuManager $sku_manager,
                              EventDispatcherInterface $event_dispatcher,
                              Connection $connection,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->logger = $logger_channel_factory->get('alshaya_acm_product');
    $this->configFactory = $config_factory;
    $this->skuManager = $sku_manager;
    $this->eventDispatcher = $event_dispatcher;
    $this->connection = $connection;
    $this->entityTypeManager = $entity_type_manager;
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

    if ($mode === SkuManager::AGGREGATED_LISTING) {
      $message = 'Current mode is already set to display one product per configurable in listing pages.';
      $this->logger->info($message);
      $this->yell($message, 40, 'red');

      $ask = 'Are you sure you want to redo node deletion? Type "ok" if you are sure.';
    }
    else {
      $ask = 'Are you sure you want to switch to one product per configurable in listing pages? Type "ok" if you are sure.';
    }

    $confirmation = $this->ask($ask);
    if ($confirmation !== 'ok') {
      return;
    }

    // Update mode.
    $this->updateListingMode(SkuManager::AGGREGATED_LISTING);

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

    if ($mode === SkuManager::NON_AGGREGATED_LISTING) {
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
    $this->updateListingMode(SkuManager::NON_AGGREGATED_LISTING);

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
      $query->addTag('get_display_node_for_sku');
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
      try {
        if (($node = $storage->load($nid)) && ($node instanceof NodeInterface)) {
          $node->delete();
        }
      }
      catch (\Exception $e) {
        \Drupal::logger('alshaya_acm_product')->error('Error while deleting color node: @nid Message: @message in method: @method', [
          '@nids' => $nid,
          '@message' => $e->getMessage(),
          '@method' => 'AlshayaAcmProductCommands::deleteColorNodes',
        ]);
      }
    }

    $context['sandbox']['current']++;
    $context['finished'] = $context['sandbox']['current'] / $context['sandbox']['max'];
  }

  /**
   * Deletes product nodes having sku attached but sku not available in system.
   *
   * @command alshaya_acm_product:delete-orphan-product-nodes
   *
   * @validate-module-enabled alshaya_acm_product
   *
   * @aliases delete-orphan-product-nodes
   *
   * @usage drush delete-orphan-product-nodes
   *   Deletes orphan product nodes from drupal.
   */
  public function deleteOrphanProductNodes() {
    $query = $this->connection->select('node__field_skus', 'nfs');
    $query->addField('nfs', 'entity_id', 'nid');
    $query->addField('nfs', 'field_skus_value', 'sku');
    $query->leftJoin('acq_sku_field_data', 'ac', 'nfs.field_skus_value=ac.sku');
    $query->innerJoin('node_field_data', 'nfd', 'nfd.nid=nfs.entity_id');
    $query->isNull('ac.sku');
    $result = $query->execute()->fetchAllAssoc('nid', \PDO::FETCH_ASSOC);

    // If there are nodes having sku attached but sku not in system.
    if (!empty($result)) {
      // Print nids and skus for review/check.
      $this->io()->table([dt('Node'), dt('SKU')], $result);

      // Confirmation before delete.
      if (!$this->io()->confirm(dt('Are you sure you want to delete these orphan product nodes?'), FALSE)) {
        throw new UserAbortException();
      }

      foreach ($result as $rs) {
        try {
          $node = $this->entityTypeManager->getStorage('node')->load($rs['nid']);
          if ($node instanceof NodeInterface) {
            $node->delete();
            $this->logger->notice(dt('Node:@nid having sku:@sku attached is deleted from the system successfully.', [
              '@nid' => $rs['nid'],
              '@sku' => $rs['sku'],
            ]));
          }
          else {
            // On deletion of actual/parent node, color nodes associated with
            // the node also deleted. And this node might be a color node.
            // @see alshaya_acm_product_node_delete().
            // There are cases when there is an entry in node_field_data
            // table but actual node not exists in the system.
            $this->logger->error(dt('Node:@nid with sku:@sku was either a color node or just having an entry in `node_field_data` table but actual node not exists .', [
              '@nid' => $rs['nid'],
              '@sku' => $rs['sku'],
            ]));
          }
        }
        catch (\Exception $e) {
          $this->logger->error(dt('There was an error while deleting node:@nid of sku:@sku Message:@message', [
            '@nid' => $rs['nid'],
            '@sku' => $rs['sku'],
            '@message' => $e->getMessage(),
          ]));
          continue;
        }
      }

      return;
    }

    $this->output->writeln(dt('There are no orphan product nodes in the system.'));
  }

  /**
   * Post command hook to execute after each drush command.
   *
   * Added (*) to execute after each drush command.
   *
   * @hook post-command *
   */
  public function alshayaAcmProductPostCommand($result, CommandData $commandData) {
    $this->eventDispatcher->dispatch(self::POST_DRUSH_COMMAND_EVENT);
  }

  /**
   * Clean up data in node_field_data table.
   *
   * @command alshaya_acm_product:cleanup-node-field-data
   *
   * @aliases cleanup-nfd, cleanup-node-field-data
   */
  public function cleanNodeFieldData() {
    $query = $this->connection->query('SELECT nf.nid, nf.vid, nf.langcode
      FROM {node_field_data} nf
      WHERE vid NOT IN (SELECT vid FROM {node})');

    $result = $query->fetchAll();

    if (empty($result)) {
      $this->yell('No corrupt entry found in node_field_data.');
      return;
    }

    $message = dt('Found following entries in node_field_data which do not have any entry in node. Entries: @entries', [
      '@entries' => print_r($result, TRUE),
    ]);

    $this->io()->writeln($message);

    if (!$this->io()->confirm(dt('Do you want to delete them?'))) {
      throw new UserAbortException();
    }

    $vids = array_column($result, 'vid');

    $this->connection->delete('node_field_data')
      ->condition('vid', $vids, 'IN')
      ->execute();

    $this->io()->writeln(dt('Corrupt entries in node_field_data are removed.'));
  }

  /**
   * Clean up data in acq_sku_field_data table.
   *
   * This command removes entries from acq_sku_field_data
   * for which we do not have entry in acq_sku (entity)
   * table.
   *
   * @command alshaya_acm_product:cleanup-sku-field-data
   *
   * @aliases cleanup-sku-field-data
   */
  public function cleanAcqSkuFieldData() {
    $query = $this->connection->query('SELECT id, sku
      FROM {acq_sku_field_data} fd
      WHERE id NOT IN (SELECT id FROM {acq_sku})');

    $result = $query->fetchAll();

    if (empty($result)) {
      $this->yell('No corrupt entry found in acq_sku_field_data.');
      return;
    }

    $message = dt('Found following entries in acq_sku_field_data which do not have any entry in acq_sku. Entries: @entries', [
      '@entries' => print_r($result, TRUE),
    ]);

    $this->logger()->notice($message);

    if (!$this->io()->confirm(dt('Do you want to delete them?'))) {
      throw new UserAbortException();
    }

    $ids = array_column($result, 'id');

    $this->connection->delete('acq_sku_field_data')
      ->condition('id', $ids, 'IN')
      ->execute();

    $this->logger()->notice(dt('Corrupt entries in acq_sku_field_data are removed.'));
  }

  /**
   * Clean duplicate SKU data in acq_sku_field_data.
   *
   * @command alshaya_acm_product:cleanup-duplicate-skus
   *
   * @aliases cleanup-duplicate-skus
   */
  public function cleanDuplicateSkus() {
    $query = 'SELECT sku FROM {acq_sku_field_data} fd1 GROUP BY sku HAVING COUNT(*) > 2';
    $result = $this->connection->query($query)->fetchAllKeyed(0, 0);

    if (empty($result)) {
      $this->yell('No duplicate entry found in acq_sku_field_data.');
      return;
    }

    $message = dt('Duplicate SKUs found for: @entries', [
      '@entries' => print_r($result, TRUE),
    ]);

    $this->logger()->notice($message);

    if (!$this->io()->confirm(dt('Do you want to delete them?'))) {
      throw new UserAbortException();
    }

    foreach ($result as $sku) {
      $sku_records = $this->connection->query('SELECT id FROM {acq_sku_field_data} WHERE sku=:sku', [
        ':sku' => $sku,
      ])->fetchAllKeyed(0, 0);

      // Remove the first ID we use in Development.
      array_shift($sku_records);

      foreach ($sku_records as $id) {
        $entity = $this->entityTypeManager->getStorage('acq_sku')->load($id);
        if ($entity instanceof SKUInterface) {
          $entity->delete();

          $this->logger()->notice(dt('Deleted SKU entity with ID @id for SKU @sku', [
            '@id' => $id,
            '@sku' => $sku,
          ]));
        }
      }
    }

  }

  /**
   * Command to go through all the media items and add file usage for them.
   *
   * @param string $field
   *   Field to find unused media from.
   * @param array $options
   *   Command options.
   *
   * @command alshaya_acm_product:add-media-file-usage
   *
   * @option batch_size
   *   Batch size.
   *
   * @aliases alshaya-add-media-file-usage
   *
   * @usage drush alshaya-add-media-file-usage
   *   Process data from media__value field.
   * @usage drush alshaya-add-media-file-usage attr_assets
   *   Process data from attr_assets__value field.
   */
  public function addMediaFilesUsage($field = 'media', array $options = ['batch_size' => 100]) {
    $batch_size = (int) $options['batch_size'];

    $this->logger()->notice('Add file usage for all product media files...');

    $select = $this->connection->select('acq_sku_field_data');
    $select->fields('acq_sku_field_data', ['sku']);
    $select->condition('default_langcode', 1);
    $select->condition($field . '__value', '%fid%', 'LIKE');

    $result = $select->execute()->fetchAll();

    $skus = array_column($result, 'sku');

    // If no sku available, then no need to process further as with empty
    // array, drush throws error.
    if (!$skus) {
      $this->output->writeln(dt('No matched sku found for adding its media files usage.'));
      return;
    }

    $batch = [
      'title' => 'Process skus',
      'error_message' => 'Error occurred while processing skus, please check logs.',
    ];

    foreach (array_chunk($skus, $batch_size) as $chunk) {
      $batch['operations'][] = [
        [__CLASS__, 'addMediaFilesUsageChunk'],
        [$chunk, $field],
      ];
    }

    batch_set($batch);
    drush_backend_batch_process();

    $this->logger()->notice('Added usage for all media files.');
  }

  /**
   * Batch callback for addMediaFilesUsage.
   *
   * @param array $skus
   *   SKUs to process.
   * @param string $field
   *   Field to find unused media from.
   */
  public static function addMediaFilesUsageChunk(array $skus, $field) {
    /** @var \Drupal\file\FileStorageInterface $fileStorage */
    $fileStorage = \Drupal::entityTypeManager()->getStorage('file');

    /** @var \Drupal\file\FileUsage\FileUsageInterface $fileUsage */
    $fileUsage = \Drupal::service('file.usage');

    $logger = \Drupal::logger('AlshayaAcmProductCommands');

    foreach ($skus as $sku_string) {
      $sku = SKU::loadFromSku($sku_string);
      if (!($sku instanceof SKU)) {
        continue;
      }

      $assets = unserialize($sku->get($field)->getString());

      foreach ($assets ?? [] as $asset) {
        if (empty($asset['fid'])) {
          continue;
        }

        $file = $fileStorage->load($asset['fid']);
        if ($file instanceof FileInterface) {
          try {
            $fileUsage->add($file, $sku->getEntityTypeId(), $sku->getEntityTypeId(), $sku->id());
            $logger->notice('Added file usage for fid @fid for sku @sku', [
              '@fid' => $file->id(),
              '@sku' => $sku->getSku(),
            ]);
          }
          catch (\Exception $e) {
            $logger->warning('Failed to add usage for fid @fid for sku @sku, message: @message', [
              '@fid' => $file->id(),
              '@sku' => $sku->getSku(),
              '@message' => $e->getMessage(),
            ]);
          }
        }
      }
    }
  }

  /**
   * Delete unsed media items.
   *
   * @param string $prefix
   *   Prefix to check in media files uri.
   * @param array $options
   *   Command options.
   *
   * @command alshaya_acm_product:delete-unused-media
   *
   * @aliases alshaya-delete-unused-media
   *
   * @usage drush alshaya-delete-unused-media media
   *   Finds unused files .
   * @usage drush alshaya-delete-unused-media assets
   *   Print the category menu true for en language.
   */
  public function deleteUnusedMediaFiles($prefix = 'media', array $options = ['batch-size' => 50, 'dry-run' => FALSE]) {
    $dry_run = (bool) $options['dry-run'];
    $batch_size = (int) $options['batch-size'];

    $query = $this->connection->select('file_managed', 'fm');
    $query->fields('fm', ['fid']);
    $query->leftJoin('file_usage', 'fu', 'fm.fid = fu.fid');
    $query->isNull('fu.fid');
    $query->condition('fm.uri', 'public://' . $prefix . '%', 'LIKE');
    $result = $query->execute()->fetchAllKeyed(0, 0);

    if (empty($result)) {
      $this->logger()->notice('No media files to check.');
      return;
    }

    $batch = [
      'operations' => [],
      'init_message' => dt('Processing all files to check if they are still used...'),
      'progress_message' => dt('Completed @current step of @total.'),
      'error_message' => dt('Failed to check for unused media files.'),
    ];

    foreach (array_chunk($result, $batch_size) as $chunk) {
      $batch['operations'][] = [
        [__CLASS__, 'deleteUnusedMediaFilesChunk'],
        [$chunk, $dry_run],
      ];
    }

    batch_set($batch);

    // Process the batch.
    drush_backend_batch_process();
  }

  /**
   * Batch callback for deleteUnusedMediaFiles.
   *
   * @param array $files
   *   Files to process.
   * @param bool $dry_run
   *   Dry run flag.
   */
  public static function deleteUnusedMediaFilesChunk(array $files, $dry_run) {
    /** @var \Drupal\file\FileStorageInterface $fileStorage */
    $fileStorage = \Drupal::entityTypeManager()->getStorage('file');

    $logger = \Drupal::logger('AlshayaAcmProductCommands');

    foreach ($files as $file_id) {
      $file = $fileStorage->load($file_id);

      if ($file instanceof FileInterface) {
        $logger->notice('Delete file @fid with uri @uri', [
          '@fid' => $file->id(),
          '@uri' => $file->getFileUri(),
        ]);

        if (!$dry_run) {
          $file->delete();
        }
      }
    }
  }

}
