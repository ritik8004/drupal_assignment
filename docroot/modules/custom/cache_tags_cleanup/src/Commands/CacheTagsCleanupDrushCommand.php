<?php

namespace Drupal\cache_tags_cleanup\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Core\Database\Connection;
use Drush\Exceptions\UserAbortException;

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
   * CacheTagsCleanupDrushCommand constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   */
  public function __construct(Connection $connection) {
    $this->fileSystem = $file_system;
    $this->connection = $connection;
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
   * @option batch_size
   *   Batch size.
   *
   * @usage drush delete-entity-cachetags
   *   Remove entity cachetags from table.
   */
  public function deleteCacheTagsEntriesforEntities(array $options = ['batch_size' => 10]) {
    $query = $this->connection->select('deleted_entity_info', 'de');
    $query->addField('de', 'id');
    $query->addField('de', 'entity_id');
    $query->addField('de', 'entity_type');
    $query->innerJoin('cachetags', 'ct', "ct.tag=concat(de.entity_type, ':', de.entity_id)");
    $entities = $query->execute()->fetchAll();

    if (empty($entities)) {
      $this->yell('No entity found in deleted_entity_info which matches entry in cachetags table.');
      return;
    }

    $message = dt('Found following entries in deleted_entity_info which still has cachetags entry. Entries: @entries', [
      '@entries' => print_r($entities, TRUE),
    ]);

    $this->io()->writeln($message);

    if (!$this->io()->confirm(dt('Do you want to delete entries from cachetags for all deleted entities?'))) {
      throw new UserAbortException();
    }

    $batch_size = (int) $options['batch_size'];

    $batch = [
      'title' => dt('Cleanup cache tags for deleted entities'),
      'error_message' => dt('Error occurred while deleting cache tags, please check logs.'),
    ];

    $entity_chunks = array_chunk($entities, $batch_size);
    // / print_r($entity_chunks);
    foreach ($entity_chunks as $entity_chunk) {
      $batch['operations'][] = [
        [__CLASS__, 'cacheTagsCleanupBatchProcess'],
        [$entity_chunk],
      ];
    }

    // Initialize the batch.
    batch_set($batch);

    // Start the batch process.
    drush_backend_batch_process();

    $message = dt('Cache tags clean up completed for all deleted entities.');

    $this->io()->writeln($message);
  }

  /**
   * Implements cache tags cleanup in batches.
   *
   * @param array $entity_chunk
   *   Array of entities.
   */
  public static function cacheTagsCleanupBatchProcess(array $entity_chunk) {
    foreach ($entity_chunk as $entity) {
      $db = \Drupal::database();
      $cache_tag = $entity->entity_type . ':' . $entity->entity_id;
      // Delete obsolete cachetags.
      $query = $db->delete('cachetags');
      $query->condition('tag', $cache_tag, 'like');
      $query->execute();
      // Delete entries from deleted_entity_info table.
      $query = $db->delete('deleted_entity_info');
      $query->condition('id', $entity->id, 'like');
      $query->execute();
      \Drupal::logger('cachetags_delete')->info('Deleted cache tag entry for entity id @id and entity type @type.', [
        '@id' => $entity->entity_id,
        '@type' => $entity->entity_type,
      ]);
    }
  }

}
