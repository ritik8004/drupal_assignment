<?php

namespace Drupal\cache_tags_cleanup\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Class contains drush command to clean up cache tags.
 */
class CacheTagsCleanupDrushCommand extends DrushCommands {

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * Logger channel factory instance.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  private $entityManager;

  /**
   * CacheTagsCleanupDrushCommand constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   Logger factory.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   Entity manager.
   */
  public function __construct(Connection $connection, LoggerChannelFactoryInterface $logger_channel_factory, EntityManagerInterface $entityManager) {
    $this->connection = $connection;
    $this->logger = $logger_channel_factory->get('cache_tags_cleanup');
    $this->entityManager = $entityManager;
  }

  /**
   * Command to remove cachetags of deleted entities.
   *
   * @param array $options
   *   Command options.
   *
   *   We are storing entity info on delete operation in custom table
   *   alshaya_deleted_entity_info. We will be using that to delete
   *   cachetags entries for all the deleted entities through a drush command.
   *
   * @command delete-entity-cachetags
   *
   * @option chunk_size
   *   Chunk size.
   *
   * @usage drush delete-entity-cachetags
   *   Remove entity cachetags from table.
   */
  public function deleteEntityCacheTags(array $options = ['chunk_size' => 50]) {

    $verbose = $options['verbose'] ?? FALSE;
    // Getting all entity types.
    $entity_types = array_keys($this->entityManager->getDefinitions());
    $batch = [
      'operations' => [],
      'init_message' => dt('Processing all cachetags for which entities are deleted.'),
      'error_message' => dt('Failed to check for cachetags data.'),
    ];
    foreach ($entity_types as $entity_type) {
      $count = 0;
      $cache_tags = [];
      $query = $this->connection->select('cachetags', 'ct');
      $query->addField('ct', 'tag');
      $query->condition('tag', $entity_type . ':%', 'LIKE');
      $result = $query->execute()->fetchAll();
      foreach ($result as $res) {
        $cache_tags[] = $res->tag;
      }

      if (!empty($cache_tags)) {
        $chunk_size = (int) $options['chunk_size'];
        $cache_tags_chunks = array_chunk($cache_tags, $chunk_size);

        foreach ($cache_tags_chunks as $chunk) {
          $batch['operations'][] = [
            [__CLASS__, 'deleteCacheTagsforDeletedEntities'],
            [$chunk, $verbose],
          ];
        }
      }
    }
    batch_set($batch);

    // Process the batch.
    drush_backend_batch_process();
    if ($count == 0) {
      $this->logger->notice('Cache tags table does not have any entries anymore for deleted entities.');
    }
  }

  /**
   * Batch callback for deleteCacheTagsforDeletedEntities.
   *
   * @param array $chunk
   *   Cachetags entities to process.
   * @param string $verbose
   *   Verbose adds details to command execution.
   */
  public static function deleteCacheTagsforDeletedEntities(array $chunk, $verbose) {
    $entity_manager = \Drupal::entityManager();
    $count = 0;
    foreach ($chunk as $cache_tag) {
      $split = explode(":", $cache_tag);
      $id = $split[1];
      if (is_numeric($id)) {
        $type = $split[0];
        $entitystorage = $entity_manager->getStorage($type);
        $entity = $entitystorage->load($id);
        if (!isset($entity)) {
          $count++;
          try {
            // Delete obsolete cachetags.
            $query = $query = \Drupal::database()->delete('cachetags');
            $query->condition('tag', $cache_tag, '=');
            $query->execute();
            if ($verbose) {
              \Drupal::logger('cache_tags_cleanup')->notice(dt('Cachetag entry deleted for entity @id and type @type.', [
                '@id' => $id,
                '@type' => $type,
              ]));
            }
          }
          catch (\Exception $e) {
            \Drupal::logger('cache_tags_cleanup')->error('Exception while deleting data from cache_tags table. Message: @message.', [
              '@message' => $e->getMessage(),
            ]);
          }
        }
      }
    }
  }

}
