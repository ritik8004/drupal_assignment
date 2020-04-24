<?php

namespace Drupal\alshaya_brand\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\file\FileInterface;
use Drush\Commands\DrushCommands;

/**
 * Class AlshayaBrandAssetsCommands.
 *
 * @package Drupal\alshaya_brand\Commands
 */
class AlshayaBrandAssetsCommands extends DrushCommands {
  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * AlshayaBrandAssetsCommands constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   Logger Channel Factory.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_channel_factory,
                              Connection $connection) {
    $this->logger = $logger_channel_factory->get('alshaya_brand');
    $this->connection = $connection;
  }

  /**
   * Delete unsed brand assets.
   *
   * @param array $options
   *   Command options.
   *
   * @command alshaya_brand:delete-unused-brand-assets
   *
   * @aliases duba delete-unused-brand-assets
   *
   * @usage drush delete-unused-brand-assets
   *   Deletes unused assets .
   */
  public function deleteUnusedBrandAssets(array $options = ['batch-size' => 50, 'dry-run' => FALSE]) {
    $dry_run = (bool) $options['dry-run'];
    $batch_size = (int) $options['batch-size'];

    $unused_assets = $this->getUnusedAssets();

    if (empty($unused_assets)) {
      $this->logger()->notice('No asset files to check.');
      return;
    }

    $batch = [
      'operations' => [],
      'init_message' => dt('Processing all files to check if they are still used...'),
      'progress_message' => dt('Completed @current step of @total.'),
      'error_message' => dt('Failed to check for unused media files.'),
    ];

    foreach (array_chunk($unused_assets, $batch_size) as $chunk) {
      $batch['operations'][] = [
        [__CLASS__, 'deleteUnusedBrandAssetsChunk'],
        [$chunk, $dry_run],
      ];
    }

    batch_set($batch);

    // Process the batch.
    drush_backend_batch_process();
  }

  /**
   * Batch callback for deleteUnusedBrandAssetsChunk.
   *
   * @param array $files
   *   Files to process.
   * @param bool $dry_run
   *   Dry run flag.
   */
  public static function deleteUnusedBrandAssetsChunk(array $files, $dry_run) {
    /** @var \Drupal\file\FileStorageInterface $fileStorage */
    $fileStorage = \Drupal::entityTypeManager()->getStorage('file');

    $logger = \Drupal::logger('AlshayaBrandAssetsCommands');

    foreach ($files as $file) {
      if (!file_exists($file->uri)) {
        $file = $fileStorage->load($file->fid);
        if ($file instanceof FileInterface) {
          $logger->notice('Delete file entity @fid with uri @uri', [
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

  /**
   * Lists unsed brand assets.
   *
   * @command alshaya_brand:list-unused-brand-assets
   *
   * @aliases luba list-unused-brand-assets
   *
   * @usage drush list-unused-brand-assets
   *   Lists unused assets .
   */
  public function listUnusedBrandAssets() {
    $unused_assets = $this->getUnusedAssets();

    if (empty($unused_assets)) {
      $this->logger()->notice('No unused asset available.');
      return;
    }
    drush_print(dt('List of unused assets (fid | URI):'));

    foreach ($unused_assets as $asset) {
      drush_print($asset->fid . ' | ' . $asset->uri);
    }
  }

  /**
   * Helper function to get unused brand assets.
   *
   * @return array
   *   List of unused assets.
   */
  private function getUnusedAssets() {
    $query = $this->connection->select('file_managed', 'fm');
    $query->fields('fm', ['fid', 'uri']);
    $query->leftJoin('file_usage', 'fu', 'fm.fid = fu.fid');
    $query->isNull('fu.fid');
    $query->condition('fm.uri', 'brand://%', 'LIKE');
    $result = $query->execute()->fetchAll();

    return $result;
  }

}
