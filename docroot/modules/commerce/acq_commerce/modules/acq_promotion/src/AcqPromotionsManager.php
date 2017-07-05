<?php

namespace Drupal\acq_promotion;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\Entity\Node;

/**
 * Class AcqPromotionsManager.
 */
class AcqPromotionsManager {

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
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, APIWrapper $api_wrapper, LoggerChannelFactoryInterface $logger_factory) {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->apiWrapper = $api_wrapper;
    $this->logger = $logger_factory->get('acq_promotion');
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

      if (serialize($promotion) == $node->get('field_acq_promotion_data')->getString()) {
        // Promotion data from API matches what is already stored, not updating.
        return;
      }

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

    // Add product NID's to promotion.
    if (!empty($promotion['products'])) {
      // Fetch all products NID's related to SKU ID's.
      $productNIDs = [];
      foreach ($promotion['products'] as $product) {
        $productQuery = $this->nodeStorage->getQuery();
        $productQuery->condition('type', 'acq_product');
        $productQuery->condition('field_skus', $product['product_id'], 'IN');
        $productNIDs += $productQuery->execute();
      }

      // Assign value to $node object.
      $productNIDs = array_values($productNIDs);
      if (!empty($productNIDs)) {
        foreach ($productNIDs as $delta => $productNID) {
          $node->get('field_acq_promotion_product')->set($delta, $productNID);
        }
      }
    }

    // Invoke the alter hook to allow modules to update the node from API data.
    \Drupal::moduleHandler()->alter('acq_promotion_promotion_node', $node, $promotion);

    $node->save();
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

}
