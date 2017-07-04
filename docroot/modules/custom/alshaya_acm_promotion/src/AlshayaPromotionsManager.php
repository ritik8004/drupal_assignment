<?php

namespace Drupal\alshaya_acm_promotion;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\Entity\Node;

/**
 * Class AlshayaPromotionsManager.
 *
 * @package Drupal\alshaya_acm_promotion
 */
class AlshayaPromotionsManager {

  /**
   * Entity Manager service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * AlshayaPromotionsManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Entity Manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerChannelFactoryInterface $logger) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->logger = $logger;
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
      }

      // We only load the first node.
      /* @var $node \Drupal\node\Entity\Node */
      $node = $this->nodeStorage->load(reset($nids));

      return $node;
    }
  }

  /**
   * Helper function to fetch all promotions.
   */
  public function getAllPromotions() {
    $nodes = [];
    $query = $this->nodeStorage->getQuery();
    $nids = $query->condition('type', 'acq_promotion')->execute();
    if (!empty($nids)) {
      $nodes = Node::loadMultiple($nids);
    }

    return $nodes;
  }

}
