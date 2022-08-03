<?php

namespace Drupal\alshaya_rcs_promotion\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class to help with migration of acq promotion to rcs promotion nodes.
 */
class AlshayaRcsPromotionMigrationCommands extends DrushCommands {

  /**
   * Entity query.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $nodeQuery;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

  /**
   * AlshayaRcsPromotionCommands constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger channel factory.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->nodeQuery = $entity_type_manager->getStorage('node')->getQuery();
    $this->drupalLogger = $logger_factory->get('alshaya_rcs_promotion');
  }

  /**
   * Batch operation for deleting acq promotion nodes.
   *
   * @param array $nids
   *   Node ids.
   * @param int $count
   *   Number of nodes to delete.
   */
  public static function deleteAcqPromotionNodes(array $nids, $count) {
    $context = [];
    // Initialized node count to zero.
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = $count;
    }

    /** @var \Drupal\node\Entity\Node */
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $nodes = $node_storage->loadMultiple($nids);
    $node_storage->delete($nodes);
    $context['sandbox']['progress']++;

    if ($context['sandbox']['progress'] !== $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Batch finish operation for deletion of acq promotion nodes.
   */
  public static function acqPromotionNodesDeletionFinished($success, $results, $operations) {
    if ($success) {
      $message = dt('Batch processs completed successfully.');
    }
    else {
      $error_operation = reset($operations);
      $message = dt('An error occurred while processing %error_operation.', [
        '%error_operation' => $error_operation[0],
      ]);
    }
    \Drupal::logger('alshaya_rcs_promotion')->notice($message);
  }

  /**
   * Deletes all acq promotion nodes from the system.
   *
   * @command alshaya_rcs_promotion:delete-acq-promotion-nodes
   * @aliases arpdelpromonodes
   *
   * @usage alshaya_rcs_promotion:delete-acq-promotion_nodes
   *   Deletes all acq promotion nodes.
   *
   * @usage alshaya_rcs_promotion:delete-acq-promotion_nodes --batch-size 50
   *   Deletes all acq promotion nodes and sets batch size to 50.
   */
  public function deleteAcqPromotionNodesBatch(array $options = ['batch-size' => NULL]) {
    $this->drupalLogger->notice('Starting batch process to delete acq promotion nodes.');
    // Delete all acq_promotion nodes from the system.
    $acq_promotion_nids = $this->nodeQuery
      ->condition('type', 'acq_promotion')
      ->execute();
    // Delete all promotion nodes from the system.
    $nodes_to_delete = is_countable($acq_promotion_nids) ? count($acq_promotion_nids) : 0;

    if (!$nodes_to_delete) {
      $this->drupalLogger->notice('There are no promotion nodes to delete! Exiting!');
      return;
    }
    else {
      $this->drupalLogger->notice(dt('There are @count promotion nodes to delete!', [
        '@count' => $nodes_to_delete,
      ]));
    }

    $batch = [
      'title' => 'Delete acq promotion nodes',
      'finished' => [self::class, 'acqPromotionNodesDeletionFinished'],
    ];

    $batch_size = $options['batch-size'] ?? 20;
    foreach (array_chunk($acq_promotion_nids, $batch_size) as $chunk) {
      $batch['operations'][] = [
        [self::class, 'deleteAcqPromotionNodes'],
        [
          $chunk,
          is_countable($acq_promotion_nids) ? count($acq_promotion_nids) : 0,
        ],
      ];
    }

    batch_set($batch);
    drush_backend_batch_process();
  }

}
