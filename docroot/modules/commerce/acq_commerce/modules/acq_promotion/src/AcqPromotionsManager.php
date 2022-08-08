<?php

namespace Drupal\acq_promotion;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_promotion\Event\PromotionMappingUpdatedEvent;
use Drupal\Core\Config\ConfigFactory;
use Drupal\mysql\Driver\Database\mysql\Connection;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Acq Promotions Manager.
 */
class AcqPromotionsManager {

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
   * @var \Drupal\mysql\Driver\Database\mysql\Connection
   */
  protected $connection;

  /**
   * I18n Helper.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  protected $i18nHelper;

  /**
   * Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * Database lock service.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

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
   * @param \Drupal\mysql\Driver\Database\mysql\Connection $connection
   *   Database connection service.
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18nHelper object.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event Dispatcher.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   Database lock service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              APIWrapper $api_wrapper,
                              LoggerChannelFactoryInterface $logger_factory,
                              LanguageManager $languageManager,
                              EntityRepositoryInterface $entityRepository,
                              QueueFactory $queue,
                              ConfigFactory $configFactory,
                              Connection $connection,
                              I18nHelper $i18n_helper,
                              EventDispatcherInterface $dispatcher,
                              LockBackendInterface $lock) {
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
    $this->dispatcher = $dispatcher;
    $this->lock = $lock;
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
    $query->condition('type', 'acq_promotion');
    $query->condition('field_acq_promotion_type', $types, 'IN');

    if ($validIDs) {
      $query->condition('field_acq_promotion_rule_id', $validIDs, 'NOT IN');
    }

    $nids = $query->execute();
    foreach ($nids as $nid) {
      /** @var \Drupal\node\Entity\Node $node */
      $node = $this->nodeStorage->load($nid);

      if ($node instanceof Node) {
        $rule_id = $node->get('field_acq_promotion_rule_id')->getString();
        // Update Skus for algolia indexing.
        $skus = $this->getSkusForPromotion($rule_id);
        $event = new PromotionMappingUpdatedEvent($skus);
        $this->dispatcher->dispatch(PromotionMappingUpdatedEvent::EVENT_NAME, $event);
        // Delete rule_id, sku mapping.
        $this->deleteCartPromotionMappings($rule_id, []);
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
      if ((is_countable($nids) ? count($nids) : 0) > 1) {
        $this->logger->critical('Multiple nodes found for rule id @rule_id', ['@rule_id' => $rule_id]);
      }

      // We only load the first node.
      /** @var \Drupal\node\Entity\Node $node */
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
   * @param int|string $rule_id
   *   Promotion commerce id.
   *
   * @return array
   *   Array of skus attached with the promotion.
   */
  public function getSkusForPromotion($rule_id) {
    $query = $this->connection->select('acq_sku_promotion');
    $query->fields('acq_sku_promotion', ['sku']);
    $query->condition('rule_id', $rule_id);
    return $query->execute()->fetchAllKeyed(0, 0);
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

      // Pass the labels in the $promotion['processed_promo_labels'] array so
      // that it may be used in hooks.
      $promotion_label_languages[$promotion_label_language]
        = $promotion['processed_promo_labels'][$promotion_label_language]
          = $promotion_label['store_label'];
    }

    $promotion_node->get('title')->setValue($promotion['name']);

    // Set the description.
    $promotion_node->get('field_acq_promotion_description')->setValue([
      'value' => $promotion['description'],
      'format' => 'rich_text',
    ]);

    // Set promotion rule_id.
    $promotion_node->get('field_acq_promotion_rule_id')->setValue($promotion['rule_id']);

    // Set promotion context.
    $promotion_node->get('field_acq_promotion_context')->setValue($promotion['extension']['channel']);

    // Set the status.
    $promotion_node->setPublished((bool) $promotion['status']);

    // Set the Promotion type.
    $promotion_node->get('field_acq_promotion_type')->setValue($promotion['promotion_type']);

    // Set promotion coupon code.
    $promotion_node->get('field_coupon_code')->setValue($promotion['coupon_code']);

    // Set promotion sort order.
    $promotion_node->get('field_acq_promotion_sort_order')->setValue($promotion['order']);

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

    // Store everything as serialized string in DB.
    // Before that remove products key, as we are not using it anywhere, and
    // that is creating unnecessary load on promotion node load.
    unset($promotion['products']);
    $promotion_node->get('field_acq_promotion_data')->setValue(serialize($promotion));

    // Set the Promotion label.
    if (isset($promotion_label_languages[$site_default_langcode])) {
      $promotion_node->get('field_acq_promotion_label')->setValue($promotion_label_languages[$site_default_langcode]);
    }
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
    $promotion_attach_queue = $this->queue->get('acq_promotion_attach_queue');

    // Clear any outstanding items in attach queue before starting promotion
    // import to avoid duplicate processing.
    $promotion_attach_queue->deleteQueue();

    foreach ($promotions as $promotion) {
      // Alter hook to allow other moduled to pre-process promotion data.
      \Drupal::moduleHandler()->alter('acq_promotion_data', $promotion);

      // Extract list of sku text attached with the promotion passed.
      $products = $promotion['products'] ?? [];
      $promotion_skus = array_unique(array_column($products, 'product_sku'));

      if (Settings::get('promotions_log_data_for_investigations', 0)) {
        $this->logger->notice('products in promotion id @rule_id of @type: @data', [
          '@type' => $promotion['promotion_type'],
          '@rule_id' => $promotion['rule_id'],
          '@data' => implode(',', $promotion_skus),
        ]);
      }

      $promotion_skus_existing = array_values($this->getSkusForPromotion($promotion['rule_id']));
      $attached = array_diff($promotion_skus, $promotion_skus_existing);
      $detached = array_diff($promotion_skus_existing, $promotion_skus);

      $lock_key = 'processPromotion' . $promotion['rule_id'];

      // Acquire lock to ensure parallel processes are executed
      // sequentially.
      // @todo These 8 lines might be duplicated in multiple places. We
      // may want to create a utility service in alshaya_performance.
      do {
        $lock_acquired = $this->lock->acquire($lock_key);

        // Sleep for half a second before trying again.
        // @todo Move this 0.5s to a config variable.
        if (!$lock_acquired) {
          usleep(500000);
        }
      } while (!$lock_acquired);
      // Check if this promotion exists in Drupal.
      // Assuming rule_id is unique across a promotion type.
      $promotion_node = $this->getPromotionByRuleId($promotion['rule_id'], $promotion['promotion_type']);

      // Extract the promotion contexts attached with the promotion passed.
      $promotion_context = $promotion['extension']['channel'] ?? [];
      $promotion_context_existing = [];
      // Release the lock if the promotion_node already present.
      if ($promotion_node instanceof NodeInterface) {
        $this->lock->release($lock_key);
        unset($lock_key);

        // Extract the promotion context from the existing promotion node.
        $promotion_context_existing = array_column($promotion_node->get('field_acq_promotion_context')->getValue(), 'value');
      }

      $promotion_node = $this->syncPromotionWithMiddlewareResponse($promotion, $promotion_node);
      // Release the lock if not released.
      if (isset($lock_key)) {
        $this->lock->release($lock_key);
      }

      // Attach promotions to skus.
      if ($promotion_node) {
        $is_promotion_context_changed = FALSE;
        // Checking the difference between the context of the promotion
        // present in Drupal and the promotion response fetched
        // from Magento.
        if (array_diff($promotion_context, $promotion_context_existing)
          || array_diff($promotion_context_existing, $promotion_context)) {
          $is_promotion_context_changed = TRUE;
          $attached = $promotion_skus;
        }

        if ($promotion['promotion_type'] === 'cart' && $attached) {
          // On promotion context change, we re-add skus to the mapping table.
          if ($is_promotion_context_changed) {
            $this->logger->notice('Deleting existing rule @rule_id due to promotion context change. Existing: @existing_context New: @new_context', [
              '@rule_id' => $promotion['rule_id'],
              '@existing_context' => json_encode($promotion_context_existing, JSON_THROW_ON_ERROR),
              '@new_context' => json_encode($promotion_context, JSON_THROW_ON_ERROR),
            ]);
            $this->deleteCartPromotionMappings($promotion['rule_id'], $attached);
          }
          $this->addCartPromotionMapping($promotion['rule_id'], $attached);

          $event = new PromotionMappingUpdatedEvent($attached);
          $this->dispatcher->dispatch(PromotionMappingUpdatedEvent::EVENT_NAME, $event);
        }
        elseif ($promotion['promotion_type'] === 'category' && $attached) {
          // Final price may come updated next time, we don't store mapping
          // for catalog rule for this reason. This means it will be queued
          // for all the products all the time.
          $attach_data = [];
          foreach ($products as $product) {
            if (in_array($product['product_sku'], $attached)) {
              $attach_data[$product['product_sku']] = [
                'sku' => $product['product_sku'],
                'final_price' => $product['final_price'],
              ];
            }
          }

          if ($attach_data) {
            $this->queueItemsInBatches(
              $promotion_attach_queue,
              $attach_data,
              $promotion['rule_id'],
              'attach'
            );
          }
        }

        if ($detached) {
          $this->deleteCartPromotionMappings($promotion['rule_id'], $detached);

          $event = new PromotionMappingUpdatedEvent($detached);
          $this->dispatcher->dispatch(PromotionMappingUpdatedEvent::EVENT_NAME, $event);
        }
        $this->logger->notice('Promotion @node having rule_id: @rule_id created or updated successfully with @attach items in attach queue.', [
          '@node' => $promotion_node->getTitle(),
          '@rule_id' => $promotion['rule_id'],
          '@attach' => !empty($attach_data) ? count($attach_data) : 0,
        ]);
      }
    }
  }

  /**
   * Wrapper function to Queue items in batches.
   *
   * @param \Drupal\Core\Queue\QueueInterface $queue
   *   Queue to queue items to.
   * @param array $data
   *   Queue data.
   * @param int|string $rule_id
   *   Rule ID.
   * @param string $op
   *   Operation attach / detach.
   */
  private function queueItemsInBatches(QueueInterface $queue, array $data, $rule_id, string $op) {
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
      $item['skus'] = $chunk;
      $item['rule_id'] = $rule_id;
      $queue->createItem($item);
    }

    $first = reset($data);
    $flat_skus = is_array($first) ? array_column($data, 'sku') : $data;
    $this->logger->notice('SKUs queued up for @operation on promotion rule: @rule_id - @skus', [
      '@skus' => implode(',', $flat_skus),
      '@rule_id' => $rule_id,
      '@operation' => $op,
    ]);
  }

  /**
   * Wrapper function to create cart/promotions mapping.
   *
   * @param int|string $rule_id
   *   Rule ID.
   * @param array $skus
   *   SKUs to attach.
   */
  protected function addCartPromotionMapping($rule_id, array $skus) {
    // Log before inserting to ensure we have message even if query fails.
    $this->logger->notice('Creating promotion mapping for rule @rule_id and skus: @skus', [
      '@rule_id' => $rule_id,
      '@skus' => implode(',', $skus),
    ]);

    foreach ($skus as $sku) {
      try {
        $insert = $this->connection->insert('acq_sku_promotion');
        $insert->fields(['rule_id', 'sku']);
        $insert->values([
          'rule_id' => $rule_id,
          'sku' => $sku,
        ]);
        $insert->execute();
      }
      catch (\Exception $e) {
        $this->logger->error('Error occurred while creating promotion mappings for rule_id @rule_id and sku @sku, message: @message', [
          '@rule_id' => $rule_id,
          '@sku' => $sku,
          '@message' => $e->getMessage(),
        ]);
      }
    }
  }

  /**
   * Wrapper function to delete cart/promotions mapping.
   *
   * @param int|string $rule_id
   *   Rule ID.
   * @param array $skus
   *   SKUs to attach.
   */
  protected function deleteCartPromotionMappings($rule_id, array $skus) {
    // Log before deleting to ensure we have message even if query fails.
    $this->logger->notice('Deleting promotion mapping for rule @rule_id and skus: @skus', [
      '@rule_id' => $rule_id,
      '@skus' => implode(',', $skus),
    ]);

    $delete = $this->connection->delete('acq_sku_promotion');
    $delete->condition('rule_id', $rule_id);

    if ($skus) {
      $delete->condition('sku', $skus, 'IN');
    }

    $delete->execute();
  }

}
