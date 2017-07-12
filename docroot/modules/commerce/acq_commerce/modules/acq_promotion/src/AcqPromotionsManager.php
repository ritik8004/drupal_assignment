<?php

namespace Drupal\acq_promotion;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\Entity\Node;
use Drupal\acq_sku\Entity\SKU;

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
   * Entity Manager service.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

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
   * @param \Drupal\Core\Entity\EntityManager $entityManager
   *   Entity Manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              APIWrapper $api_wrapper,
                              LoggerChannelFactoryInterface $logger_factory,
                              LanguageManager $languageManager,
                              EntityRepositoryInterface $entityRepository,
                              EntityManager $entityManager) {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->skuStorage = $entity_type_manager->getStorage('acq_sku');
    $this->apiWrapper = $api_wrapper;
    $this->logger = $logger_factory->get('acq_promotion');
    $this->languageManager = $languageManager;
    $this->entityRepository = $entityRepository;
    $this->entityManager = $entityManager;
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

    foreach ($types as $type) {
      $promotions = $this->apiWrapper->getPromotions($type);

      foreach ($promotions as $promotion) {
        // Add type to $promotion array, to be saved later.
        $promotion['promotion_type'] = $type;

        // @TODO: Add basic validations to remove junk data here.
        $this->syncPromotion($promotion);

        $ids[] = $promotion['rule_id'];
      }
    }

    // Unpublish promotions, which are not part of API response.
    $this->unpublishPromotions($ids);
  }

  /**
   * Create/Update promotion from API data to Drupal.
   *
   * @param array $promotion
   *   Promotion data from API.
   */
  protected function syncPromotion(array $promotion) {
    // Load associated product display node.
    $query = $this->nodeStorage->getQuery();
    $query->condition('type', 'acq_promotion');
    $query->condition('field_acq_promotion_rule_id', $promotion['rule_id']);

    $nids = $query->execute();

    $promotions_labels = $promotion['labels'];
    $promotion_label_languages = [];
    $site_default_langcode = $this->languageManager->getDefaultLanguage()->getId();

    foreach ($promotions_labels as $promotion_label) {
      $promtion_label_language = acq_commerce_get_langcode_from_store_id($promotion_label['store_id']);
      $promotion_label_languages[$promtion_label_language] = $promotion_label['store_label'];
    }

    // Create promotion.
    if (empty($nids)) {
      /* @var $node \Drupal\node\Entity\Node */
      $node = $this->nodeStorage->create([
        'type' => 'acq_promotion',
      ]);

      $node->get('field_acq_promotion_rule_id')->setValue($promotion['rule_id']);

      $this->logger->info('Creating promotion for rule id @rule_id', ['@rule_id' => $promotion['rule_id']]);
    }
    // Update promotion.
    else {
      // Log a message for admin to check errors in data.
      if (count($nids) > 1) {
        $this->logger->critical('Multiple nodes found for rule id @rule_id', ['@rule_id' => $promotion['rule_id']]);
      }

      // We will use only the first matching node.
      /* @var $node \Drupal\node\Entity\Node */
      $node = $this->nodeStorage->load(reset($nids));

      $this->logger->info('Updating promotion for rule id @rule_id', ['@rule_id' => $promotion['rule_id']]);
    }

    // Set the name into title.
    $node->get('title')->setValue($promotion['name']);

    // Set the description.
    $node->get('field_acq_promotion_description')->setValue(['value' => $promotion['description'], 'format' => 'rich_text']);

    // Set the status.
    $node->setPublished((bool) $promotion['status']);

    // Store everything as serialized string in DB.
    $node->get('field_acq_promotion_data')->setValue(serialize($promotion));

    // Set the Promotion type.
    $node->get('field_acq_promotion_type')->setValue($promotion['promotion_type']);

    // Set the Promotion label.
    if (isset($promotion_label_languages[$site_default_langcode])) {
      $node->get('field_acq_promotion_label')->setValue($promotion_label_languages[$site_default_langcode]);
    }

    // Set promotion type to percent & discount value depending on the promotion
    // being imported.
    if (($promotion['type'] === 'NO_COUPON') && isset($promotion['action']) && ($promotion['action'] === 'by_percent')) {
      $node->get('field_acq_promotion_disc_type')->setValue('percentage');
      $node->get('field_acq_promotion_discount')->setValue($promotion['discount']);
    }

    // Check promotion action type & store in Drupal.
    if (!empty($promotion['action'])) {
      $node->get('field_acq_promotion_action')->setValue($promotion['action']);
    }

    // Add SKU ID's to promotion.
    if (!empty($promotion['products'])) {
      // Assign value to $node object.
      $delta = 0;
      foreach ($promotion['products'] as $key => $product) {
        $sku = SKU::loadFromSku($product['product_sku']);
        if ($sku instanceof SKU) {
          $node->get('field_acq_promotion_sku')->set($delta, $sku->id());
          $delta++;
        }
      }
    }
    // Invoke the alter hook to allow modules to update the node from API data.
    \Drupal::moduleHandler()->alter('acq_promotion_promotion_node', $node, $promotion);

    $node->save();

    // Create promotion translations based on the language codes available in
    // promotion labels.
    foreach ($promotion_label_languages as $langcode => $promotion_label_language) {
      if ($langcode !== $site_default_langcode) {
        if ($node->hasTranslation($langcode)) {
          $node_translation = $node->getTranslation($langcode);
        }
        else {
          $node_translation = $node->addTranslation($langcode);
        }
        $node_translation->get('field_acq_promotion_label')->setValue($promotion_label_languages[$langcode]);
        $node_translation->save();
      }
    }
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
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Promotion node.
   */
  public function createPromotionFromConductorResponse(array $promotion) {
    $promotion_node = $this->nodeStorage->create([
      'type' => 'acq_promotion',
    ]);

    $site_default_langcode = $this->languageManager->getDefaultLanguage()->getId();

    $promotion_node->get('title')->setValue($promotion['name']);

    // Set the description.
    $promotion_node->get('field_acq_promotion_description')->setValue(['value' => $promotion['description'], 'format' => 'rich_text']);

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

    $status = $promotion_node->save();

    if ($status) {
      return $promotion_node;
    }
    else {
      $this->logger->critical('Error occured while creating Promotion node for rule id: @rule_id.', ['@rule_id' => $promotion['rule_id']]);
      return NULL;
    }
  }

}
