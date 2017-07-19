<?php

namespace Drupal\acq_promotion;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\node\Entity\Node;
use Drupal\acq_sku\Entity\SKU;
use GuzzleHttp\Client;

/**
 * Class AcqPromotionsManager.
 */
class AcqPromotionsManager {

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
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              APIWrapper $api_wrapper,
                              LoggerChannelFactoryInterface $logger_factory,
                              LanguageManager $languageManager,
                              EntityRepositoryInterface $entityRepository,
                              QueueFactory $queue,
                              ConfigFactory $configFactory) {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->skuStorage = $entity_type_manager->getStorage('acq_sku');
    $this->apiWrapper = $api_wrapper;
    $this->logger = $logger_factory->get('acq_promotion');
    $this->languageManager = $languageManager;
    $this->entityRepository = $entityRepository;
    $this->queue = $queue;
    $this->configFactory = $configFactory;
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
    $promotions = [];
    foreach ($types as $type) {
      $promotions = $this->apiWrapper->getPromotions($type);

      foreach ($promotions as $key => $promotion) {
        // Add type to $promotion array, to be saved later.
        $promotion['promotion_type'] = $type;
        $fetched_promotions[] = $promotion;
        $ids[] = $promotion['rule_id'];
      }
    }

    $this->processPromotions($fetched_promotions);

    // Unpublish promotions, which are not part of API response.
    $this->unpublishPromotions($ids);
  }

  /**
   * Unpublish Promotion nodes, not part of API Response.
   *
   * @param array $validIDs
   *   Valid Rule ID's from API.
   */
  protected function unpublishPromotions($validIDs = []) {
    $query = $this->nodeStorage->getQuery();
    $query->condition('type', 'acq_promotion');
    $query->condition('field_acq_promotion_rule_id', $validIDs, 'NOT IN');
    $nids = $query->execute();
    foreach ($nids as $nid) {
      /* @var $node \Drupal\node\Entity\Node */
      $node = $this->nodeStorage->load($nid);
      $node->setPublished(Node::NOT_PUBLISHED);
      $node->save();

      // Detach promotion from all skus.
      $attached_skus = $this->getSkusForPromotion($node);
      if ($attached_skus) {
        $data['skus'] = $attached_skus;
        $data['promotion'] = $node->id();
        $acq_promotion_detach_queue = $this->queue->get('acq_promotion_detach_queue');
        $acq_promotion_detach_queue->createItem($data);
      }
    }
  }

  /**
   * Helper function to fetch promotion node givern rule id.
   *
   * @param int $rule_id
   *   Rule id of the promotion to load.
   *
   * @return \Drupal\node\Entity\Node|null
   *   Return node if a promotion found associated with the rule id else Null.
   */
  public function getPromotionByRuleId($rule_id) {
    $query = $this->nodeStorage->getQuery();
    $query->condition('type', 'acq_promotion');
    $query->condition('field_acq_promotion_rule_id', $rule_id);
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
   * @param \Drupal\node\Entity\Node $promotion
   *   Promotion node for which we need to find skus.
   *
   * @return array
   *   Array of sku objects attached with the promotion.
   */
  public function getSkusForPromotion(Node $promotion) {
    $skus = [];
    $query = $this->skuStorage->getQuery();
    $query->condition('field_acq_sku_promotions', $promotion->id());
    $sku_ids = $query->execute();
    if (!empty($sku_ids)) {
      $skus = SKU::loadMultiple($sku_ids);
    }

    return $skus;
  }

  /**
   * Helper function to create Promotion node from conductor response.
   *
   * @param array $promotion
   *   Promotion response from Conductor.
   * @param null $promotion_node
   *   Promotion node in case we need to update Promotion.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Promotion node.
   */
  public function syncPromotionWithMiddlewareResponse(array $promotion,
                                                        $promotion_node = NULL) {
    if (!$promotion_node) {
      $promotion_node = $this->nodeStorage->create([
        'type' => 'acq_promotion',
      ]);
    }

    $promotions_labels = $promotion['labels'];
    $promotion_label_languages = [];
    $site_default_langcode = $this->languageManager->getDefaultLanguage()->getId();

    foreach ($promotions_labels as $promotion_label) {
      $promtion_label_language = acq_commerce_get_langcode_from_store_id($promotion_label['store_id']);
      $promotion_label_languages[$promtion_label_language] = $promotion_label['store_label'];
    }

    $promotion_node->get('title')->setValue($promotion['name']);

    // Set the description.
    $promotion_node->get('field_acq_promotion_description')->setValue(['value' => $promotion['description'], 'format' => 'rich_text']);

    // Set promotion rule_id.
    $promotion_node->get('field_acq_promotion_rule_id')->setValue($promotion['rule_id']);

    // Set the status.
    $promotion_node->setPublished((bool) $promotion['status']);

    // Store everything as serialized string in DB.
    $promotion_node->get('field_acq_promotion_data')->setValue(serialize($promotion));

    // Set the Promotion type.
    $promotion_node->get('field_acq_promotion_type')->setValue($promotion['promotion_type']);

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
          $node_translation = $promotion_node->getTranslation($langcode);
        }
        else {
          $node_translation = $promotion_node->addTranslation($langcode, $promotion_node->toArray());
        }
        $node_translation->get('field_acq_promotion_label')->setValue($promotion_label_languages[$langcode]);
        $node_translation->save();
      }
    }

    if ($status) {
      return $promotion_node;
    }
    else {
      $this->logger->critical('Error occured while creating Promotion node for rule id: @rule_id.', ['@rule_id' => $promotion['rule_id']]);
      return NULL;
    }
  }

  /**
   * Helper function to process Promotions obtained from middleware.
   *
   * @param array $promotions
   *   List of promotions to sync.
   *
   * @return array
   *   Messages around attach & detach queues.
   */
  public function processPromotions(array $promotions = []) {
    $output = [];
    $acq_promotion_attach_batch_size = $this->configFactory
      ->get('acq_promotion.settings')
      ->get('promotion_attach_batch_size');

    foreach ($promotions as $promotion) {
      $attached_promotion_skus = [];
      $fetched_promotion_skus = [];
      $fetched_promotion_sku_attach_data = [];

      // Extract list of sku text attached with the promotion passed.
      $products = $promotion['products'];
      foreach ($products as $product) {
        $fetched_promotion_skus[] = $product['product_sku'];
        $fetched_promotion_sku_attach_data[$product['product_sku']] = [
          'sku' => $product['product_sku'],
          'final_price' => $product['final_price'],
        ];
      }

      // Check if this promotion exists in Drupal.
      $promotion_node = $this->getPromotionByRuleId($promotion['rule_id']);

      // If promotion exists, we update the related skus & final price.
      if ($promotion_node) {
        // Update promotion metadata.
        $this->syncPromotionWithMiddlewareResponse($promotion, $promotion_node);
        $attached_skus = $this->getSkusForPromotion($promotion_node);

        // Extract sku text from sku objects.
        if (!empty($attached_skus)) {
          foreach ($attached_skus as $attached_sku) {
            $attached_promotion_skus[] = $attached_sku->getSku();
          }
        }

        // Get list of skus for which promotions should be detached.
        $detach_promotion_skus = array_diff($attached_promotion_skus, $fetched_promotion_skus);

        // Create a queue for removing promotions from skus.
        if (!empty($detach_promotion_skus)) {
          $promotion_detach_queue = $this->queue->get('acq_promotion_detach_queue');
          $data['promotion'] = $promotion_node->id();
          $data['skus'] = $detach_promotion_skus;
          $promotion_detach_queue->createItem($data);
          $output['detached_message'] = t('Skus @skus queued up to detach 
          promotion rule: @rule_id', [
             '@skus' => implode(',', $data['skus']),
             '@rule_id' => $promotion['rule_id'],
            ]
          );
        }
      }
      else {
        // Create promotions node using Metadata from Promotions Object.
        $promotion_node = $this->syncPromotionWithMiddlewareResponse($promotion);
      }

      // Attach promotions to skus.
      if ($promotion_node && (!empty($fetched_promotion_skus))) {
        $promotion_attach_queue = $this->queue->get('acq_promotion_attach_queue');
        $data['promotion'] = $promotion_node->id();
        $chunks = array_chunk($fetched_promotion_sku_attach_data, $acq_promotion_attach_batch_size);
        foreach ($chunks as $chunk) {
          $data['skus'] = $chunk;
          $promotion_attach_queue->createItem($data);
          $output['attached_message'] = t('Skus @skus queued up to attach promotion rule: @rule_id',
            ['@skus' => implode(',', array_keys($fetched_promotion_sku_attach_data)), '@rule_id' => $promotion['rule_id']]);
        }
      }
    }

    return $output;
  }

}
