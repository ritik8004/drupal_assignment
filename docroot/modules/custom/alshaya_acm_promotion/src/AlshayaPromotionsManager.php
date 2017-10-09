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
   * Denotes the fixed_percentage_discount_order promotion subtype.
   */
  const SUBTYPE_FIXED_PERCENTAGE_DISCOUNT_ORDER = 'fixed_percentage_discount_order';

  /**
   * Denotes the fixed_amount_discount_order promotion subtype.
   */
  const SUBTYPE_FIXED_AMOUNT_DISCOUNT_ORDER = 'fixed_amount_discount_order';

  /**
   * Denotes the free_shipping_order promotion subtype.
   */
  const SUBTYPE_FREE_SHIPPING_ORDER = 'free_shipping_order';

  /**
   * Denotes other promotion subtype.
   */
  const SUBTYPE_OTHER = 'other';

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
   *
   * @param array $conditions
   *   An array of associative array containing conditions, to be used in query,
   *   with following elements:
   *   - 'field': Name of the field being queried.
   *   - 'value': The value for field.
   *   - 'operator': Possible values like '=', '<>', '>', '>=', '<', '<='.
   *
   * @return array
   *   Array of node objects.
   *
   * @see \Drupal\Core\Entity\Query\QueryInterface
   */
  public function getAllPromotions(array $conditions = []) {
    $nodes = [];
    $query = $this->nodeStorage->getQuery();
    $query->condition('type', 'acq_promotion');
    foreach ($conditions as $condition) {
      if (!empty($condition['field']) && !empty($condition['value'])) {
        $condition['operator'] = empty($condition['operator']) ? '=' : $condition['operator'];
        $query->condition($condition['field'], $condition['value'], $condition['operator']);
      }
    }

    $nids = $query->execute();
    if (!empty($nids)) {
      $nodes = Node::loadMultiple($nids);
    }

    return $nodes;
  }

  /**
   * Helper function to fetch promotion SubType.
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
      (isset($promotion['condition']['conditions'][0]['attribute']) && $promotion['condition']['conditions'][0]['attribute'] == 'base_subtotal')
    ) {
      if (!$promotion['apply_to_shipping']) {
        if ($promotion['action'] == 'by_percent') {
          return self::SUBTYPE_FIXED_PERCENTAGE_DISCOUNT_ORDER;
        }
        elseif ($promotion['action'] == 'cart_fixed') {
          return self::SUBTYPE_FIXED_AMOUNT_DISCOUNT_ORDER;
        }
      }
      elseif (isset($promotion['free_shipping']) && $promotion['free_shipping'] == 2) {
        return self::SUBTYPE_FREE_SHIPPING_ORDER;
      }
    }
    return self::SUBTYPE_OTHER;
  }

}
