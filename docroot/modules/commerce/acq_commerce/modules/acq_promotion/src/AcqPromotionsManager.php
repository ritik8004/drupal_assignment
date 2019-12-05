<?php

namespace Drupal\acq_promotion;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_commerce\I18nHelper;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Class AcqPromotionsManager.
 */
class AcqPromotionsManager {

  const ACQ_PROMOTIONS_BUNDLE = 'acq_promotion';

  use StringTranslationTrait;

  /**
   * Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * Sku Entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $skuStorage;

  /**
   * Entity Repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Node Entity Storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  private $nodeStorage;

  /**
   * The api wrapper.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * Queue Factory service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queue;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Database connection service.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $connection;

  /**
   * I18n Helper.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  private $i18nHelper;

  /**
   * Constructs a new AcqPromotionsManager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   The api wrapper.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   Language Manager service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   Entity Repository service.
   * @param \Drupal\Core\Queue\QueueFactory $queue
   *   Queue factory service.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Config factory service.
   * @param \Drupal\Core\Database\Driver\mysql\Connection $connection
   *   Database connection service.
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18nHelper object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              APIWrapper $api_wrapper,
                              LoggerChannelFactoryInterface $logger_factory,
                              LanguageManager $languageManager,
                              EntityRepositoryInterface $entityRepository,
                              QueueFactory $queue,
                              ConfigFactory $configFactory,
                              Connection $connection,
                              I18nHelper $i18n_helper) {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->skuStorage = $entity_type_manager->getStorage('acq_sku');
    $this->apiWrapper = $api_wrapper;
    $this->logger = $logger_factory->get('acq_promotion');
    $this->languageManager = $languageManager;
    $this->entityRepository = $entityRepository;
    $this->queue = $queue;
    $this->configFactory = $configFactory;
    $this->connection = $connection;
    $this->i18nHelper = $i18n_helper;
  }

  /**
   * Synchronize promotions through the API.
   *
   * @param mixed $types
   *   The type of promotion to synchronize.
   */
  public function syncPromotions($types = ['category', 'cart']) {
    $types = is_array($types) ? $types : [$types];
    $ids = [];
    $fetched_promotions = [];

    foreach ($types as $type) {
      $promotions = $this->apiWrapper->getPromotions($type);

      foreach ($promotions as $promotion) {
        // Add type to $promotion array, to be saved later.
        $promotion['promotion_type'] = $type;
        $fetched_promotions[] = $promotion;
        $ids[] = $promotion['rule_id'];
      }
    }

    if (!empty($fetched_promotions)) {
      $this->processPromotions($fetched_promotions);
    }

    // Delete promotions, which are not part of API response.
    $this->deletePromotions($types, $ids);
  }

  /**
   * Delete Promotion nodes, not part of API Response.
   *
   * @param array $types
   *   Promotion types.
   * @param array $validIDs
   *   Valid Rule ID's from API.
   */
  protected function deletePromotions(array $types, array $validIDs = []) {
    $query = $this->nodeStorage->getQuery();
    $query->condition('type', self::ACQ_PROMOTIONS_BUNDLE);
    $query->condition('field_acq_promotion_type', $types, 'IN');

    if ($validIDs) {
      $query->condition('field_acq_promotion_rule_id', $validIDs, 'NOT IN');
    }

    $nids = $query->execute();

    foreach ($nids as $nid) {
      /* @var $node \Drupal\node\Entity\Node */
      $node = $this->nodeStorage->load($nid);

      if ($node instanceof Node) {
        $rule_id = $node->get('field_acq_promotion_rule_id')->getString();
        $attached_promotion_skus = $this->getSkusForPromotion($node->id());

        $this->queueItemsInBatches(
          $this->queue->get('acq_promotion_detach_queue'),
          $node->id(),
          $attached_promotion_skus,
          $rule_id,
          'detach'
        );

        $node->delete();
        $this->logger->notice('Deleted orphan promotion node:@nid title:@promotion having rule_id:@rule_id.', [
          '@nid' => $node->id(),
          '@promotion' => $node->label(),
          '@rule_id' => $node->get('field_acq_promotion_rule_id')->getString(),
        ]);
      }
    }
  }

  /**
   * Helper function to fetch promotion node givern rule id.
   *
   * @param int $rule_id
   *   Rule id of the promotion to load.
   * @param string $rule_type
   *   Rule type of the promotion to load.
   *
   * @return \Drupal\node\Entity\Node|null
   *   Return node if a promotion found associated with the rule id else Null.
   */
  public function getPromotionByRuleId($rule_id, $rule_type) {
    $query = $this->nodeStorage->getQuery();
    $query->condition('type', 'acq_promotion');
    $query->condition('field_acq_promotion_rule_id', $rule_id);
    $query->condition('field_acq_promotion_type', $rule_type);
    $nids = $query->execute();

    if (empty($nids)) {
      return NULL;
    }
    else {
      // Log a message for admin to check errors in data.
      if (count($nids) > 1) {
        $this->logger->critical('Multiple nodes found for rule id @rule_id', ['@rule_id' => $rule_id]);
        return NULL;
      }

      // We only load the first node.
      /* @var $node \Drupal\node\Entity\Node */
      $node = $this->nodeStorage->load(reset($nids));
      $langcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
      // Get the promotion with language fallback, if it did not have a
      // translation for $langcode.
      $node = $this->entityRepository->getTranslationFromContext($node, $langcode);
      return $node;
    }
  }

  /**
   * Helper function to get skus attached with a promotion.
   *
   * @param mixed $promotion_id
   *   Promotion node for which we need to find skus.
   *
   * @return array
   *   Array of sku objects attached with the promotion.
   */
  public function getSkusForPromotion($promotion_id) {
    // Backwards compatibility, it was first expecting Node object.
    $promotion_id = $promotion_id instanceof NodeInterface ? $promotion_id->id() : $promotion_id;

    $query = $this->connection->select('acq_sku__field_acq_sku_promotions', 'fasp');
    $query->join('acq_sku_field_data', 'asfd', 'asfd.id = fasp.entity_id');
    $query->condition('fasp.field_acq_sku_promotions_target_id', $promotion_id);
    $query->fields('asfd', ['id', 'sku']);
    $query->distinct();
    $skus = $query->execute()->fetchAllKeyed(0, 1);

    return $skus;
  }

  /**
   * Helper function to create Promotion node from conductor response.
   *
   * @param array $promotion
   *   Promotion response from Conductor.
   * @param \Drupal\node\Entity\Node $promotion_node
   *   Promotion node in case we need to update Promotion.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Promotion node.
   */
  public function syncPromotionWithMiddlewareResponse(array $promotion,
                                                      Node $promotion_node = NULL) {
    if (!$promotion_node) {
      $promotion_node = $this->nodeStorage->create([
        'type' => 'acq_promotion',
      ]);
    }

    $promotions_labels = $promotion['labels'];
    $promotion_label_languages = [];
    $site_default_langcode = $this->languageManager->getDefaultLanguage()->getId();

    foreach ($promotions_labels as $promotion_label) {
      $promotion_label_language = $this->i18nHelper->getLangcodeFromStoreId($promotion_label['store_id']);

      // Magento might have stores that what we don't support.
      if (empty($promotion_label_language)) {
        continue;
      }

      $promotion_label_languages[$promotion_label_language] = $promotion_label['store_label'];
    }

    $promotion_node->get('title')->setValue($promotion['name']);

    // Set the description.
    $promotion_node->get('field_acq_promotion_description')->setValue(['value' => $promotion['description'], 'format' => 'rich_text']);

    // Set promotion rule_id.
    $promotion_node->get('field_acq_promotion_rule_id')->setValue($promotion['rule_id']);

    // Set the status.
    $promotion_node->setPublished((bool) $promotion['status']);

    // Store everything as serialized string in DB.
    // Before that remove products key, as we are not using it anywhere, and
    // that is creating unnecessary load on promotion node load.
    unset($promotion['products']);
    $promotion_node->get('field_acq_promotion_data')->setValue(serialize($promotion));

    // Set the Promotion type.
    $promotion_node->get('field_acq_promotion_type')->setValue($promotion['promotion_type']);

    // Set promotion coupon code.
    $promotion_node->get('field_coupon_code')->setValue($promotion['coupon_code']);

    // Set promotion sort order.
    $promotion_node->get('field_acq_promotion_sort_order')->setValue($promotion['order']);

    // Set the Promotion label.
    if (isset($promotion_label_languages[$site_default_langcode])) {
      $promotion_node->get('field_acq_promotion_label')->setValue($promotion_label_languages[$site_default_langcode]);
    }

    // Set promotion type to percent & discount value depending on the promotion
    // being imported.
    if (($promotion['type'] === 'NO_COUPON') && isset($promotion['action']) && ($promotion['action'] === 'by_percent')) {
      $promotion_node->get('field_acq_promotion_disc_type')->setValue('percentage');
      $promotion_node->get('field_acq_promotion_discount')->setValue($promotion['discount']);
    }

    // Check promotion action type & store in Drupal.
    if (!empty($promotion['action'])) {
      $promotion_node->get('field_acq_promotion_action')->setValue($promotion['action']);
    }

    // Invoke the alter hook to allow modules to update the node from API data.
    \Drupal::moduleHandler()->alter('acq_promotion_promotion_node', $promotion_node, $promotion);

    $status = $promotion_node->save();
    // Create promotion translations based on the language codes available in
    // promotion labels.
    foreach ($promotion_label_languages as $langcode => $promotion_label_language) {
      if ($langcode !== $site_default_langcode) {
        if ($promotion_node->hasTranslation($langcode)) {
          $promotion_node->removeTranslation($langcode);
        }

        $node_translation = $promotion_node->addTranslation($langcode, $promotion_node->toArray());

        $node_translation->get('field_acq_promotion_label')->setValue($promotion_label_languages[$langcode]);
        $node_translation->save();
      }
    }

    if ($status) {
      return $promotion_node;
    }
    else {
      $this->logger->critical('An error occurred while creating promotion node for rule id: @rule_id.', ['@rule_id' => $promotion['rule_id']]);
      return NULL;
    }
  }

  /**
   * Helper function to process Promotions obtained from middleware.
   *
   * @param array $promotions
   *   List of promotions to sync.
   */
  public function processPromotions(array $promotions = []) {
    $promotion_detach_queue = $this->queue->get('acq_promotion_detach_queue');
    $promotion_attach_queue = $this->queue->get('acq_promotion_attach_queue');

    // Clear any outstanding items in attach queue before starting promotion
    // import to avoid duplicate processing. For delete queue we want it to
    // be processed for deleted promotions.
    $promotion_attach_queue->deleteQueue();

    foreach ($promotions as $promotion) {
      // Alter hook to allow other moduled to pre-process promotion data.
      \Drupal::moduleHandler()->alter('acq_promotion_data', $promotion);

      $fetched_promotion_skus = [];
      $fetched_promotion_sku_attach_data = [];

      // Extract list of sku text attached with the promotion passed.
      $products = $promotion['products'];

      foreach ($products as $product) {
        if (!in_array($product['product_sku'], array_keys($fetched_promotion_skus))) {
          $fetched_promotion_skus[$product['product_sku']] = $product['product_sku'];

          $fetched_promotion_sku_attach_data[$product['product_sku']] = [
            'sku' => $product['product_sku'],
          ];

          if (($promotion['promotion_type'] === 'category') && isset($product['final_price'])) {
            $fetched_promotion_sku_attach_data[$product['product_sku']]['final_price'] = $product['final_price'];
          }
        }
      }

      // Check if this promotion exists in Drupal.
      // Assuming rule_id is unique across a promotion type.
      $promotion_node = $this->getPromotionByRuleId($promotion['rule_id'], $promotion['promotion_type']);

      // If promotion exists, we update the related skus & final price.
      if ($promotion_node) {
        // Update promotion metadata.
        $this->syncPromotionWithMiddlewareResponse($promotion, $promotion_node);
        $attached_promotion_skus = $this->getSkusForPromotion($promotion_node->id());
        $detach_promotion_skus = [];

        // Get list of skus for which promotions should be detached.
        if (!empty($attached_promotion_skus)) {
          $detach_promotion_skus = array_diff($attached_promotion_skus, $fetched_promotion_skus);
        }

        // Create a queue for removing promotions from skus.
        $this->queueItemsInBatches(
          $promotion_detach_queue,
          $promotion_node->id(),
          $detach_promotion_skus,
          $promotion['rule_id'],
          'detach'
        );
      }
      else {
        // Create promotions node using Metadata from Promotions Object.
        $promotion_node = $this->syncPromotionWithMiddlewareResponse($promotion);
      }

      // Attach promotions to skus.
      if ($promotion_node && (!empty($fetched_promotion_sku_attach_data))) {
        if ($promotion['promotion_type'] == 'cart' && !empty($attached_promotion_skus)) {
          foreach ($attached_promotion_skus as $sku) {
            unset($fetched_promotion_sku_attach_data[$sku]);
          }
        }

        // Filter skus which not exists in system before attaching to queue.
        if (!empty($fetched_promotion_sku_attach_data)) {
          $query = $this->connection->select('acq_sku_field_data', 'sku');
          $query->fields('sku', ['sku']);
          $query->condition('sku.sku', array_keys($fetched_promotion_sku_attach_data), 'IN');
          $query->condition('sku.default_langcode', 1);
          $fetched_promotion_sku_attach_data = $query->execute()->fetchAllAssoc('sku', \PDO::FETCH_ASSOC);
        }

        $this->queueItemsInBatches(
          $promotion_attach_queue,
          $promotion_node->id(),
          $fetched_promotion_sku_attach_data,
          $promotion['rule_id'],
          'attach'
        );
      }

      $this->logger->notice($this->t('Promotion `@node` having rule_id:@rule_id created or updated successfully with @attach items in attach queue and @detach items in detach queue.', [
        '@node' => $promotion_node->getTitle(),
        '@rule_id' => $promotion['rule_id'],
        '@attach' => !empty($fetched_promotion_sku_attach_data) ? count($fetched_promotion_sku_attach_data) : 0,
        '@detach' => !empty($detach_promotion_skus) ? count($detach_promotion_skus) : 0,
      ]));
    }
  }

  /**
   * Removes the given promotion from SKU entity.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   * @param int $nid
   *   Promotion node id.
   */
  public function removeOrphanPromotionFromSku(SKU $sku, int $nid) {
    $promotion_detach_item[] = ['target_id' => $nid];
    $sku_promotions = $sku->get('field_acq_sku_promotions')->getValue();

    $updated_sku_promotions = array_udiff($sku_promotions, $promotion_detach_item, function ($array1, $array2) {
      return $array1['target_id'] - $array2['target_id'];
    });

    if (count($updated_sku_promotions) == count($sku_promotions)) {
      // Nothing to remove.
      return;
    }

    $sku->get('field_acq_sku_promotions')->setValue($updated_sku_promotions);
    $sku->save();
  }

  /**
   * Wrapper function to Queue items in batches.
   *
   * @param \Drupal\Core\Queue\QueueInterface $queue
   *   Queue to queue items to.
   * @param int|string $promotion_nid
   *   Promotion Node ID.
   * @param array $data
   *   Queue data.
   * @param int|string $rule_id
   *   Rule ID.
   * @param string $op
   *   Operation attach / detach.
   */
  private function queueItemsInBatches(QueueInterface $queue, $promotion_nid, array $data, $rule_id, string $op) {
    if (empty($data)) {
      return;
    }

    static $batch_size;

    if (empty($batch_size)) {
      $batch_size = $this->configFactory
        ->get('acq_promotion.settings')
        ->get('promotion_attach_batch_size');
    }

    foreach (array_chunk($data, $batch_size) as $chunk) {
      $item = [];
      $item['promotion'] = $promotion_nid;
      $item['skus'] = $chunk;
      $queue->createItem($item);
    }

    $first = reset($data);
    $flat_skus = is_array($first) ? array_column($data, 'sku') : $data;
    $this->logger->notice('SKUs @skus queued up to @operation promotion rule: @rule_id', [
      '@skus' => implode(',', $flat_skus),
      '@rule_id' => $rule_id,
      '@operation' => $op,
    ]);
  }

}
