<?php

namespace Drupal\cache_tags_cleanup\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Core\Database\Connection;
use Drush\Exceptions\UserAbortException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

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
   * CacheTagsCleanupDrushCommand constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   Logger factory.
   */
  public function __construct(Connection $connection, LoggerChannelFactoryInterface $logger_channel_factory) {
    $this->connection = $connection;
    $this->logger = $logger_channel_factory->get('cache_tags_cleanup');
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
  public function deleteCacheTagsEntriesforEntities(array $options = ['chunk_size' => 25]) {
    $query = $this->connection->select('cache_tags_cleanup_queue', 'de');
    $query->addField('de', 'entity_id');
    $query->addField('de', 'entity_type');
    $query->innerJoin('cachetags', 'ct', "ct.tag=concat(de.entity_type, ':', de.entity_id)");
    $entities = $query->execute()->fetchAll();

    $verbose = $options['verbose'] ?? FALSE;

    if (empty($entities)) {
      $this->logger->notice('No entity found in cache_tags_cleanup_queue which matches entry in cachetags table.');
      return;
    }

    if ($verbose) {
      foreach ($entities as $entity) {
        $this->logger->notice(dt('Cache tags found for deleted entity:@entity_id and entity_type:@entity_type', [
          '@entity_id' => $entity->entity_id,
          '@entity_type' => $entity->entity_type,
        ]));
      }
    }
    if (!$this->io()->confirm(dt('Total @count cache tags entries found for deleted entities. Do you want to delete them?', ['@count' => count($entities)]))) {
      throw new UserAbortException();
    }

    $chunk_size = (int) $options['chunk_size'];
    $entity_chunks = array_chunk($entities, $chunk_size);

    foreach ($entity_chunks as $entity_chunk) {
      // Cleaning up each entity chunk one by one.
      $cachetags_collection = [];
      foreach ($entity_chunk as $entity) {
        $cachetags_collection['entity_ids'][] = $entity->entity_id;
        $cachetags_collection['entity_types'][] = $entity->entity_type;
        $cachetags_collection['cachetags'][] = $entity->entity_type . ':' . $entity->entity_id;
        $this->logger->notice(dt('Deleting cache tag entry for entity id:@id and entity type:@type.', [
          '@id' => $entity->entity_id,
          '@type' => $entity->entity_type,
        ]));
      }
      try {
        // Delete obsolete cachetags.
        $query = $query = $this->connection->delete('cachetags');
        $query->condition('tag', $cachetags_collection['cachetags'], 'IN');
        $query->execute();
        // Delete entries from cache_tags_cleanup_queue table.
        $query = $this->connection->delete('cache_tags_cleanup_queue');
        $query->condition('entity_id', $cachetags_collection['entity_ids'], 'IN');
        $query->condition('entity_type', $cachetags_collection['entity_types'], 'IN');
        $query->execute();
      }
      catch (\Exception $e) {
        $this->logger->error('Exception while deleting data from cache_tags table. Message: @message.', [
          '@message' => $e->getMessage(),
        ]);
      }
    }

    $message = 'Cache tags clean up completed for all deleted entities.';
    $this->logger->notice($message);
  }

}
