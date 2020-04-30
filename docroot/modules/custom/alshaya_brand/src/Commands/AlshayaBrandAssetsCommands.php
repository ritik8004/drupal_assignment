<?php

namespace Drupal\alshaya_brand\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\file\FileInterface;
use Drush\Commands\DrushCommands;
use Consolidation\SiteAlias\SiteAliasManagerAwareInterface;
use Consolidation\SiteAlias\SiteAliasManagerAwareTrait;
use Symfony\Component\Yaml\Yaml;
use Consolidation\SiteProcess\ProcessManager;

/**
 * Class AlshayaBrandAssetsCommands.
 *
 * @package Drupal\alshaya_brand\Commands
 */
class AlshayaBrandAssetsCommands extends DrushCommands implements SiteAliasManagerAwareInterface {

  use SiteAliasManagerAwareTrait;

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
   * Delete unused and unavailable file entities.
   *
   * @param array $options
   *   Command options.
   *
   * @command alshaya_brand:delete-unused-unavailable-file-entities
   *
   * @aliases duufe delete-unused-unavailable-file-entities
   *
   * @usage drush delete-unused-unavailable-file-entities
   *   Deletes unused assets .
   */
  public function deleteUnusedUnavailableFileEntities(array $options = ['batch-size' => 50, 'dry-run' => FALSE]) {
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
        [__CLASS__, 'deleteUnusedUnavailableFileEntitiesChunk'],
        [$chunk, $dry_run],
      ];
    }

    batch_set($batch);

    // Process the batch.
    drush_backend_batch_process();
  }

  /**
   * Batch callback for deleteUnusedUnavailableFileEntities.
   *
   * @param array $files
   *   Files to process.
   * @param bool $dry_run
   *   Dry run flag.
   */
  public static function deleteUnusedUnavailableFileEntitiesChunk(array $files, $dry_run) {
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
   * Lists unused brand asset paths.
   *
   * @command alshaya_brand:list-unused-brand-asset-paths
   *
   * @aliases lubap list-unused-brand-asset-paths
   *
   * @usage drush list-unused-brand-asset-paths
   *   Lists unused asset paths .
   */
  public function listUnusedBrandAssetPaths() {
    $unused_assets = $this->getUnusedAssets();

    if (empty($unused_assets)) {
      $this->logger()->notice('No unused asset available.');
      return;
    }

    $this->io()->writeln(dt('List of unused assets (fid | URI):'));

    foreach ($unused_assets as $asset) {
      $this->io()->writeln($asset->fid . ' | ' . $asset->uri);
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

  /**
   * Delete unused assets across markets of a brand.
   *
   * @param array $options
   *   Command options.
   *
   * @command alshaya_brand:delete-unused-brand-assets-all-markets
   *
   * @aliases dubaam delete-unused-brand-assets-all-markets
   *
   * @usage drush delete-unused-brand-assets-all-markets
   *   Delete unused assets across markets of a brand.
   */
  public function deleteUnusedBrandAssetsAllMarkets(array $options = ['batch-size' => 50, 'dry-run' => FALSE]) {
    $dry_run = (bool) $options['dry-run'];
    $batch_size = (int) $options['batch-size'];
    $unused_brand_assets = [];

    $domains = $this->getBrandDomains();

    foreach ($domains ?? [] as $domain) {
      $command = sprintf('drush -l %s list-unused-brand-asset-paths', $domain[1]);
      $get_unused_brand_assets = $this->processManager()->process($command);
      $get_unused_brand_assets->mustRun();
      $data = $get_unused_brand_assets->getOutput();
      $data = explode(PHP_EOL, $data);

      foreach ($data as $line) {
        if (preg_match('/^\d/', $line) === 1) {
          $array = explode(' | ', $line);
          $unused_brand_assets[$domain[1]][$array[0]] = $array[1];
        }
      }
      // Batch operation to clean up unused file entities.
      $batch_operation_clean_up_unused_file_entities[] = [
        [__CLASS__, 'deleteUnusedUnavailableFileEntitiesForDomain'],
        $domain,
      ];
    }
    if (!empty($unused_brand_assets)) {
      // Get list of unused assets common in all the markets of a brand.
      $unused_brand_assets = call_user_func_array('array_intersect', $unused_brand_assets);
    }

    if (empty($unused_brand_assets)) {
      $this->logger()->notice('No unused brand assets found.');
      return;
    }

    $batch = [
      'operations' => [],
      'init_message' => dt('Processing all files to check if they are still used...'),
      'progress_message' => dt('Completed @current step of @total.'),
      'error_message' => dt('Failed to check for unused media files.'),
    ];

    foreach (array_chunk($unused_brand_assets, $batch_size, TRUE) as $chunk) {
      $batch['operations'][] = [
        [__CLASS__, 'deleteUnusedBrandAssetsAllMarketsChunk'],
        [$chunk, $dry_run],
      ];
    }

    // Adding operations for cleaning up file entities at the end of
    // operations to clean up unused asset files.
    $batch['operations'] = array_merge($batch['operations'], $batch_operation_clean_up_unused_file_entities);

    batch_set($batch);

    // Process the batch.
    drush_backend_batch_process();
  }

  /**
   * Batch callback for deleteUnusedUnavailableFileEntities.
   *
   * @param array $files
   *   Files to process.
   * @param bool $dry_run
   *   Dry run flag.
   */
  public static function deleteUnusedBrandAssetsAllMarketsChunk(array $files, $dry_run) {
    $logger = \Drupal::logger('AlshayaBrandAssetsCommands');
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');

    foreach ($files as $uri) {
      $logger->notice('Delete file with uri @uri', [
        '@uri' => $uri,
      ]);

      if (!$dry_run) {
        $file_system->delete($uri);
      }
    }
  }

  /**
   * Batch callback for deleteUnusedUnavailableFileEntities.
   *
   * @param string $domain
   *   Domain to process.
   */
  public static function deleteUnusedUnavailableFileEntitiesForDomain(string $domain) {
    $logger = \Drupal::logger('AlshayaBrandAssetsCommands');
    $processManager = ProcessManager::createDefault();

    $command = sprintf('drush -l %s delete-unused-unavailable-file-entities', $domain);
    $get_unused_brand_assets = $processManager->process($command);
    $get_unused_brand_assets->mustRun();

    $logger->notice('Brand asset clean up complete for @domain', [
      '@domain' => $domain,
    ]);
  }

  /**
   * Helper function to get all domains of a brand.
   *
   * @return array
   *   List of domains.
   */
  private function getBrandDomains() {
    // @codingStandardsIgnoreStart
    global $acsf_site_code;
    // @codingStandardsIgnoreEnd

    $selfRecord = $this->siteAliasManager()->getSelf();

    /** @var \Consolidation\SiteProcess\SiteProcess $atl */
    $atl = $this->processManager()->drush($selfRecord, 'acsf-tools-list', [], ['fields' => 'domains']);
    $atl->mustRun();
    $data = $atl->getOutput();
    $data = explode(PHP_EOL, $data);

    $yaml_data = '';
    $start_reading = FALSE;
    foreach ($data as $line) {
      if ((strpos($line, $acsf_site_code) > -1) && (strpos($line, ' ') !== 0)) {
        $start_reading = TRUE;
        $yaml_data .= $line . ':' . PHP_EOL;
        continue;
      }
      if ($start_reading) {
        if (strpos($line, ' ') !== 0) {
          break;
        }
        if (strpos($line, 'domains') > -1) {
          continue;
        }
        $yaml_data .= $line . PHP_EOL;
      }
    }

    $domains = Yaml::parse($yaml_data);

    return $domains;
  }

}
