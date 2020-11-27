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

    if (empty($entities)) {
      $this->yell('No entity found in cache_tags_cleanup_queue which matches entry in cachetags table.');
      return;
    }

    if (!$this->io()->confirm(dt('Do you want to delete entries from cachetags for all deleted entities?'))) {
      throw new UserAbortException();
    }

    $chunk_size = (int) $options['chunk_size'];

    $entity_chunks = array_chunk($entities, $chunk_size);

    foreach ($entity_chunks as $key => $entity_chunk) {
      // Cleaning up each entity chunk one by one.
      foreach ($entity_chunk as $entity) {
        $cache_tag = $entity->entity_type . ':' . $entity->entity_id;
        // Delete obsolete cachetags.
        $query = $this->connection->delete('cachetags');
        $query->condition('tag', $cache_tag, 'like');
        $query->execute();
        // Delete entries from cache_tags_cleanup_queue table.
        $query = $this->connection->delete('cache_tags_cleanup_queue');
        $query->condition('entity_id', $entity->entity_id, 'like');
        $query->condition('entity_type', $entity->entity_type, 'like');
        $query->execute();
        $this->logger->info('Deleted cache tag entry for entity id @id and entity type @type.', [
          '@id' => $entity->entity_id,
          '@type' => $entity->entity_type,
        ]);
      }
      $this->io()->writeln(dt('Deleted chunk @key of @total entity cache tags',
        [
          '@key' => $key + 1,
          '@total' => count($entity_chunks),
        ]
      ));
    }

    $message = dt('Cache tags clean up completed for all deleted entities.');

    $this->io()->writeln($message);
  }

}
