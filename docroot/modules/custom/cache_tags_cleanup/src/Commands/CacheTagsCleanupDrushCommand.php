<?php

namespace Drupal\cache_tags_cleanup\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class contains drush command to clean up cache tags.
 */
class CacheTagsCleanupDrushCommand extends DrushCommands {

  /**
   * Logger Channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

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
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityManager;

  /**
   * CacheTagsCleanupDrushCommand constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   Logger factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   Entity manager.
   */
  public function __construct(Connection $connection, LoggerChannelFactoryInterface $logger_channel_factory, EntityTypeManagerInterface $entity_manager) {
    $this->connection = $connection;
    $this->drupalLogger = $logger_channel_factory->get('cache_tags_cleanup');
    $this->entityManager = $entity_manager;
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
    $chunk_size = (int) $options['chunk_size'];

    // Getting all entity types.
    $entity_types = array_keys($this->entityManager->getDefinitions());

    foreach ($entity_types as $entity_type) {
      $deleted_entities = [];
      $entity_ids = [];
      $cachetags_ids = [];

      // Query to get all cachetags of a particulae entity type.
      $query = $this->connection->select('cachetags', 'ct');
      $query->addField('ct', 'tag');
      $query->condition('tag', $entity_type . ':%', 'LIKE');
      $result = $query->execute()->fetchAll();

      // Separate ids from cachetags and process them.
      foreach ($result as $cache_tag) {
        $split = explode(":", $cache_tag->tag);
        $id = $split[1];
        $cachetags_ids[$id] = $id;
      }

      // Get all entity ids from a entity type.
      $entity_ids = $this->entityManager->getStorage($entity_type)->getQuery()->execute();
      // Find all entities which exists in cachetags
      // but not in entity table.
      $deleted_entities = array_diff($cachetags_ids, $entity_ids);
      if (!empty($deleted_entities)) {
        $this->drupalLogger->notice(dt('Total @count cache tags found for deleted entities of type @type.', [
          '@count' => count($deleted_entities),
          '@type' => $entity_type,
        ]));
      }

      $cache_tags_chunks = array_chunk($deleted_entities, $chunk_size);
      $cachetags_collection = [];
      foreach ($cache_tags_chunks as $entity_chunk) {
        foreach ($entity_chunk as $entity_value) {
          $cachetags_collection['cachetags'][] = $entity_type . ':' . $entity_value;
          if ($verbose) {
            $this->drupalLogger->notice(dt('Cache tag entry found for deleted entity with id @id and entity type @type.', [
              '@id' => $entity_value,
              '@type' => $entity_type,
            ]));
          }
        }
      }

      try {
        // Delete collection of cachetags together.
        if (!empty($cachetags_collection)) {
          $query = $this->connection->delete('cachetags');
          $query->condition('tag', $cachetags_collection['cachetags'], 'IN');
          $query->execute();
          $this->drupalLogger->notice(dt('Cachetag entries deleted for all deleted entities of type @type.', [
            '@type' => $entity_type,
          ]));
        }
      }
      catch (\Exception $e) {
        $this->drupalLogger->error('Exception while deleting entry from cache_tags table. Message: @message.', [
          '@message' => $e->getMessage(),
        ]);
      }
    }
    $this->drupalLogger->notice('Cache tags table does not have any entries anymore for deleted entities.');
  }

}
