<?php

namespace Drupal\alshaya_acm_promotion;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManager;
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
   * Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * Entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * AlshayaPromotionsManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Entity Manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger service.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   The language manager service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The Entity repository service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerChannelFactoryInterface $logger, LanguageManager $languageManager, EntityRepositoryInterface $entityRepository) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->logger = $logger;
    $this->languageManager = $languageManager;
    $this->entityRepository = $entityRepository;
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
      $langcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
      // Get the promotion with language fallback, if it did not have a
      // translation for $langcode.
      $node = $this->entityRepository->getTranslationFromContext($node, $langcode);
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

  /**
   * Helper function to promoton SubType.
   *
   * @param array $promotion
   *   The promotion array.
   *
   * @return string
   *   String containing the type of promotion.
   */
  public function getSubType(array $promotion) {
    if (empty($promotion)) {
      return '';
    }

    if (
      (!isset($promotion['product_discounts']) || empty($promotion['product_discounts'])) &&
      (!isset($promotion['action_condition']['conditions']) || empty($promotion['action_condition']['conditions'])) &&
      (isset($promotion['condition']['conditions'][0]['attribute_name']) && $promotion['condition']['conditions'][0]['attribute_name'] == 'base_subtotal')
    ) {
      if (!$promotion['apply_to_shipping']) {
        if ($promotion['simple_action'] == 'by_percent') {
          return 'fixed_percentage_discount_order';
        }
        elseif ($promotion['simple_action'] == 'cart_fixed') {
          return 'fixed_amount_discount_order';
        }
      }
      elseif (isset($promotion['simple_free_shipping']) && $promotion['simple_free_shipping'] == 2) {
        return 'free_shipping_order';
      }
    }
    return 'other';
  }

}
