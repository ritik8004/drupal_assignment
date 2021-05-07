<?php

namespace Drupal\alshaya_acm_product\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\Service\ProductProcessedManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\file\FileInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_commerce\Conductor\IngestAPIWrapper;

/**
 * Class Alshaya Address Product Commands.
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
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private $drupalLogger;

  /**
   * The Module Handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * I18n Helper.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  private $i18nHelper;

  /**
   * Conductor Ingest API Helper.
   *
   * @var \Drupal\acq_commerce\Conductor\IngestAPIWrapper
   */
  private $ingestApi;

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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The Module Handler service.
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18nHelper object.
   * @param \Drupal\acq_commerce\Conductor\IngestAPIWrapper $ingest_api
   *   IngestAPI manager interface.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_channel_factory,
                              ConfigFactoryInterface $config_factory,
                              SkuManager $sku_manager,
                              EventDispatcherInterface $event_dispatcher,
                              Connection $connection,
                              EntityTypeManagerInterface $entity_type_manager,
                              ModuleHandlerInterface $module_handler,
                              I18nHelper $i18n_helper,
                              IngestAPIWrapper $ingest_api) {
    $this->drupalLogger = $logger_channel_factory->get('alshaya_acm_product');
    $this->configFactory = $config_factory;
    $this->skuManager = $sku_manager;
    $this->eventDispatcher = $event_dispatcher;
    $this->connection = $connection;
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
    $this->i18nHelper = $i18n_helper;
    $this->ingestApi = $ingest_api;
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
      $this->drupalLogger->info($message);
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
    $this->drupalLogger->info($message);
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
      $this->drupalLogger->info($message);
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
    $this->drupalLogger->info($message);
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
            $this->drupalLogger->notice(dt('Node:@nid having sku:@sku attached is deleted from the system successfully.', [
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
            $this->drupalLogger->error(dt('Node:@nid with sku:@sku was either a color node or just having an entry in `node_field_data` table but actual node not exists .', [
              '@nid' => $rs['nid'],
              '@sku' => $rs['sku'],
            ]));
          }
        }
        catch (\Exception $e) {
          $this->drupalLogger->error(dt('There was an error while deleting node:@nid of sku:@sku Message:@message', [
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

    $this->drupalLogger->notice($message);

    if (!$this->io()->confirm(dt('Do you want to delete them?'))) {
      throw new UserAbortException();
    }

    $ids = array_column($result, 'id');

    $this->connection->delete('acq_sku_field_data')
      ->condition('id', $ids, 'IN')
      ->execute();

    $this->drupalLogger->notice(dt('Corrupt entries in acq_sku_field_data are removed.'));
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

    $this->drupalLogger->notice($message);

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

          $this->drupalLogger->notice(dt('Deleted SKU entity with ID @id for SKU @sku', [
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

    $this->drupalLogger->notice('Add file usage for all product media files...');

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

    $this->drupalLogger->notice('Added usage for all media files.');
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
  public function deleteUnusedMediaFiles($prefix = 'media', array $options = [
    'batch-size' => 50,
    'dry-run' => FALSE,
  ]) {
    $dry_run = (bool) $options['dry-run'];
    $batch_size = (int) $options['batch-size'];

    $query = $this->connection->select('file_managed', 'fm');
    $query->fields('fm', ['fid']);
    $query->leftJoin('file_usage', 'fu', 'fm.fid = fu.fid');
    $query->isNull('fu.fid');
    $query->condition('fm.uri', 'public://' . $prefix . '%', 'LIKE');
    $result = $query->execute()->fetchAllKeyed(0, 0);

    if (empty($result)) {
      $this->drupalLogger->notice('No media files to check.');
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

  /**
   * Identifies and if required, fixes Product node aliases.
   *
   * @param string $langcode
   *   The langcode for which to check/generate alias. Defaults to 'en'.
   * @param array $options
   *   An option that takes multiple values.
   *
   * @command alshaya_acm_product:fix-missing-pathauto
   * @options fix
   *   Whether to fix the translations or not.
   *
   * @aliases fix-missing-pathauto
   *
   * @usage fix-missing-pathauto ar
   *   Checks and mentions the count and nids of untranslated AR nodes.
   * @usage fix-missing-pathauto en --fix
   *   Checks and mentions the count and nids of untranslated EN nodes and
   *   generates EN aliases for them.
   */
  public function checkAndFixProductAlias(string $langcode = 'en', array $options = ['fix' => FALSE]) {
    // Get nids for which translation is available.
    $query = $this->connection->select('node', 'n');
    $query->addField('n', 'nid');
    $query->innerJoin('url_alias', 'ua', "ua.source=concat('/node/', n.nid)");
    $query->condition('ua.langcode', $langcode);
    $query->condition('ua.alias', '/node/%', 'NOT LIKE');
    $nids = $query->execute()->fetchCol();

    // Get Product nids for which translation is not available.
    $query = $this->connection->select('node', 'n');
    $query->addField('n', 'nid');
    $query->innerJoin('node_field_data', 'nfd', 'nfd.nid=n.nid');
    $query->condition('nfd.type', 'acq_product');
    $query->condition('nfd.langcode', $langcode);
    $query->condition('n.nid', $nids, 'NOT IN');
    $nids = $query->execute()->fetchCol();

    $this->output()->writeln('Total number of nodes for which url alias is not generated: ' . count($nids));
    if (!empty($nids)) {
      $this->output()->writeln('The nids are: ' . json_encode($nids));
    }

    if ($options['fix']) {
      if (empty($nids)) {
        $this->output()->writeln('Nothing to fix!');
        return;
      }
      $storage = $this->entityTypeManager->getStorage('node');
      // Loading and saving the node should generate the URL alias
      // automatically.
      foreach ($nids as $nid) {
        try {
          $storage->load($nid)->save();
          $this->output()->writeln('Fixed url alias for nid: ' . $nid);
        }
        catch (\Exception $e) {
          $this->output()->writeln("Exception occured for node $nid: ", $e->getMessage());
        }
      }
      $this->output()->writeln('Re-check if process was successful by running the same command without --fix.');
    }
    else {
      if (!empty($nids)) {
        $this->output()->writeln('To fix the issues, provide "--fix" option in the command.');
      }
      else {
        $this->output()->writeln('Looks good! Nothing to do.');
      }
    }
  }

  /**
   * Check sku broken images and if found, delete them.
   *
   * @param array $options
   *   Command options.
   *
   * @command alshaya_acm_product:fix-missing-files
   *
   * @option batch_size
   *   Batch size.
   *
   * @aliases fix-missing-files
   *
   * @usage fix-missing-files
   *   Checks and delete files for all skus.
   */
  public function checkAndFixMissingFiles(array $options = ['batch_size' => 100]) {
    $batch_size = (int) $options['batch_size'];

    $query = $this->connection->select('acq_sku_field_data', 'acq_sku');
    $query->fields('acq_sku', ['sku']);
    $result = $query->execute()->fetchAll();

    $skus = array_column($result, 'sku');

    $batch = [
      'operations' => [],
      'init_message' => dt('Processing all skus to check if they have missing files...'),
      'progress_message' => dt('Completed @current step of @total.'),
      'error_message' => dt('Failed to check for missing media files.'),
    ];

    foreach (array_chunk($skus, $batch_size) as $chunk) {
      $batch['operations'][] = [
        [__CLASS__, 'fixMissingFilesForSkus'],
        [$chunk],
      ];
    }

    batch_set($batch);

    // Process the batch.
    drush_backend_batch_process();

    $this->drupalLogger->notice('Fixed missing files issues for all skus.');
  }

  /**
   * Batch callback for checkAndFixMissingFiles.
   *
   * @param array $skus
   *   SKUs to process.
   */
  public static function fixMissingFilesForSkus(array $skus) {
    $logger = \Drupal::logger('AlshayaAcmProductCommands');

    $fileStorage = \Drupal::entityTypeManager()->getStorage('file');

    foreach ($skus as $sku_string) {
      $flag = 0;
      $sku_data = SKU::loadFromSku($sku_string);
      if (!empty($sku_data)) {
        // Get media data for the sku.
        $media_data = $sku_data->get('media')->getString();
        if (!empty($media_data)) {
          $media_data = unserialize($media_data);
          foreach ($media_data as $data) {
            if (isset($data['fid'])) {
              $file = $fileStorage->load($data['fid']);
              if ($file instanceof FileInterface) {
                $url = $file->getFileUri();
                if (!file_exists($url)) {
                  $logger->notice('Fixing missing image having url @uri and file id @fid for sku @sku', [
                    '@fid' => $data['fid'],
                    '@uri' => $file->getFileUri(),
                    '@sku' => $sku_string,
                  ]);
                  $file->delete();
                  $flag = 1;
                }
              }
            }
          }
        }
        if ($flag == 1) {
          // Download files again for skus.
          $sku_data->getMedia();
        }
      }
    }
  }

  /**
   * Delete disabled products not modified in last X days.
   *
   * @command alshaya_acm_product:remove-disabled-products
   *
   * @aliases remove-disabled-products
   *
   * @usage remove-disabled-products
   *   Checks and delete products that are disabled and not modified
   *   since last X (default 7) days.
   */
  public function removeDisabledProducts() {
    // We expect this in days.
    $not_modified_since = Settings::get('remove_disabled_products_not_modified_since_last_x_days', 7);
    if (empty($not_modified_since)) {
      $this->drupalLogger->notice(dt('Not processing remove-disabled-products as setting remove_disabled_products_not_modified_since_last_x_days set to @value', [
        '@value' => serialize($not_modified_since),
      ]));

      return;
    }

    $query = $this->connection->select('acq_sku_field_data', 'asfd');
    $query->leftJoin(
      ProductProcessedManager::TABLE_NAME,
      'processed',
      'processed.sku = asfd.sku AND asfd.default_langcode = 1'
    );
    $query->addField('asfd', 'sku');
    $query->condition('default_langcode', 1);
    $query->isNull('processed.sku');

    // Find only those products which are not modified since last X days.
    $query->condition('changed', strtotime("-${not_modified_since} day"), '<');

    $skus = $query->execute()->fetchCol();

    foreach ($skus as $sku) {
      $sku_entity = SKU::loadFromSku($sku);
      if ($sku_entity instanceof SKUInterface) {
        $data = [
          'skipSkuDelete' => FALSE,
        ];
        $this->moduleHandler->alter('alshaya_acm_product_remove_disabled_products', $data, $sku_entity);
        if (!$data['skipSkuDelete']) {
          $this->drupalLogger->notice(dt('Deleting SKU @sku not modified since @changed', [
            '@sku' => $sku,
            '@changed' => $sku_entity->getChangedTime(),
          ]));
          $sku_entity->delete();
        }
        else {
          $this->drupalLogger->notice(dt('Skipping deletion of SKU @sku', [
            '@sku' => $sku,
          ]));
        }
      }
    }
  }

  /**
   * Sync products having only one translation.
   *
   * @command alshaya_acm_product:sync-single-trnaslation-products
   *
   * @aliases sync-single-translation-products
   * @usage sync-single-translation-products 6
   */
  public function syncSingleTranslationProduct($page_size = 3) {
    $query = $this->connection->select('node_field_data', 'nf');
    $query->join('node__field_skus', 'ns', 'ns.entity_id = nf.nid');
    $query->addExpression('min(ns.field_skus_value)', 'field_skus_value');
    $query->addExpression('min(nf.langcode)', 'langcode');
    $query->condition('nf.type', 'acq_product');
    $query->groupBy('nf.nid');
    $query->having('count(nf.nid) = 1');
    $result = $query->execute()->fetchAllKeyed(0, 1);

    if (empty($result)) {
      $this->drupalLogger->info('All the products have 2 translations.');
      return;
    }

    $count = count($result);
    $this->drupalLogger->info('The number of the products having only one translation are @count.', ['@count' => $count]);

    $default_count = Settings::get('single_translation_product_process_limit', 100);
    if ($count > $default_count) {
      if (!$this->io()->confirm(dt('The number of the products having only one translation are more than @count, Do you want to sync them?', ['@count' => $default_count]))) {
        throw new UserAbortException();
      }
    }

    $lang_store_mapping = $this->i18nHelper->getStoreLanguageMapping();
    $skus_grouped_by_store = [];
    foreach ($result as $sku => $langcode) {
      $store_id = ($langcode == 'ar') ? $lang_store_mapping['en'] : $lang_store_mapping['ar'];
      $skus_grouped_by_store[$store_id][] = $sku;
    }

    foreach ($lang_store_mapping as $langcode => $store_id) {
      if (!empty($skus_grouped_by_store[$store_id])) {
        foreach (array_chunk($skus_grouped_by_store[$store_id], $page_size) as $chunk) {
          $this->ingestApi->productFullSync($store_id, $langcode, implode(',', $chunk), '', $page_size);
        }
        $this->drupalLogger->info('All products having single translation for language @langcode have been re-synced. SKUs: @skus', [
          '@langcode' => $langcode,
          '@skus' => implode(',', $skus_grouped_by_store[$store_id]),
        ]);
      }
    }
    $this->drupalLogger->info('All products having single translation have been re-synced.');
  }

}
