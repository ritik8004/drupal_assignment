<?php

namespace Drupal\alshaya_acm\EventSubscriber;

use Drupal\acq_sku\AcqSkuEvents;
use Drupal\acq_sku\Events\AcqSkuSyncCatEvent;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AlshayaCatSyncCompleteEventSubscriber.
 *
 * @package Drupal\alshaya_acm\EventSubscriber
 */
class AlshayaCatSyncCompleteEventSubscriber implements EventSubscriberInterface {

  /**
   * Product category vocabulary id.
   */
  const VOCAB_ID = 'acq_product_category';

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * AddToCartErrorEventSubscriber constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   DB connection.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory, EntityTypeManagerInterface $entity_type_manager, Connection $connection) {
    $this->logger = $logger_factory->get('alshaya_acm');
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AcqSkuEvents::CAT_SYNC_COMPLETE][] = ['catSyncComplete', 1];
    return $events;
  }

  /**
   * Event handler to delete the extra product category terms from drupal.
   *
   * @param \Drupal\acq_sku\Events\AcqSkuSyncCatEvent $event
   *   Event object.
   */
  public function catSyncComplete(AcqSkuSyncCatEvent $event) {
    $data = $event->getResponseData();
    $data = array_unique(array_merge($data['created'], $data['updated']));

    // Get all category terms with commerce id.
    $query = $this->connection->select('taxonomy_term_field_data', 'ttd');
    $query->fields('ttd', ['tid']);
    $query->leftJoin('taxonomy_term__field_commerce_id', 'tcid', 'ttd.tid=tcid.entity_id');
    $query->fields('tcid', ['field_commerce_id_value']);
    $query->condition('ttd.vid', self::VOCAB_ID);
    $result = $query->execute()->fetchAll();

    // Filter terms which are not in sync response.
    $result = array_filter($result, function ($val) use ($data) {
      return !in_array($val->field_commerce_id_value, $data);
    });

    // If there are any extra terms in drupal.
    if (!empty($result)) {
      foreach ($result as $rs) {
        // If department page node exists, don't delete it.
        if (!empty($dpt_nid = alshaya_advanced_page_is_department_page($rs->tid))) {
          $this->logger->info('Extra term having tid @tid with department node @nid exists in drupal but not in magento.', [
            '@tid' => $rs->tid,
            '@nid' => $dpt_nid,
          ]);
        }
        else {
          $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($rs->tid);
          if ($term instanceof TermInterface) {
            // Delete the term.
            $term->delete();
            $this->logger->info('Term: @term_name commerce_id:@commerce_id is deleted as extra term in drupal.', [
              '@term_name' => $term->label(),
              '@commerce_id' => $rs->field_commerce_id_value,
            ]);
          }
        }
      }
    }
  }

}
