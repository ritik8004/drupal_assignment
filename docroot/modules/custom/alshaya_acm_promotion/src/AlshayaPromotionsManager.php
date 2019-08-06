<?php

namespace Drupal\alshaya_acm_promotion;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_promotion\AcqPromotionsManager;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;

/**
 * Class AlshayaPromotionsManager.
 *
 * @package Drupal\alshaya_acm_promotion
 */
class AlshayaPromotionsManager {

  use StringTranslationTrait;

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
   * @param \Drupal\acq_promotion\AcqPromotionsManager $acq_promotions_manager
   *   Promotions manager service object from commerce code.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager,
                              LoggerChannelFactoryInterface $logger,
                              LanguageManager $languageManager,
                              EntityRepositoryInterface $entityRepository,
                              AcqPromotionsManager $acq_promotions_manager) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->logger = $logger->get('alshaya_acm_promotion');
    $this->languageManager = $languageManager;
    $this->entityRepository = $entityRepository;
    $this->acqPromotionsManager = $acq_promotions_manager;
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
  public function getPromotionByRuleId($rule_id, $rule_type = 'cart') {
    return $this->acqPromotionsManager->getPromotionByRuleId($rule_id, $rule_type);
  }

  /**
   * Get free gift skus for a particular promotion rule.
   *
   * @param int $rule_id
   *   Rule id of promotion to load.
   *
   * @return array
   *   Free gift SKUs.
   */
  public function getFreeSkusByRuleId($rule_id) {
    static $free_gift_skus = [];

    if (isset($free_gift_skus[$rule_id])) {
      return $free_gift_skus[$rule_id];
    }

    $promotion = $this->acqPromotionsManager->getPromotionByRuleId($rule_id, 'cart');
    $free_skus = [];

    if ($promotion instanceof NodeInterface) {
      $free_skus = $promotion->get('field_free_gift_skus')->getValue();
      $free_skus = is_array($free_skus) ? array_column($free_skus, 'value') : [];
    }

    $free_gift_skus[$rule_id] = $free_skus;

    return $free_skus;
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
      $nodes = $this->nodeStorage->loadMultiple($nids);
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

  /**
   * Helper function to fetch all cart promotions.
   *
   * @param array $selected_promotions
   *   An array of selected promotions.
   * @param array $cartRulesApplied
   *   An array of rules applied on the cart.
   *
   * @return array
   *   Array of all cart promotions.
   */
  public function getAllCartPromotions(array $selected_promotions, array $cartRulesApplied) {
    $promotions = [];
    if (!empty($selected_promotions)) {
      foreach ($selected_promotions as $promotion_rule_id) {
        if ($promotion_rule_id) {
          $node = $this->getPromotionByRuleId($promotion_rule_id);

          if ($node instanceof NodeInterface && $node->isPublished()) {
            // Get translation if available.
            $node = $this->entityRepository->getTranslationFromContext($node);

            $message = $node->get('field_acq_promotion_label')->getString();

            if ($message) {
              $promotions[$promotion_rule_id] = ['#markup' => $message];
            }
          }
        }
      }
    }

    // Load all the promotions of the three specific types we check below.
    // We load only published promotions.
    $subTypePromotions = $this->getAllPromotions([
      [
        'field' => 'status',
        'value' => NodeInterface::PUBLISHED,
      ],
      [
        'field' => 'field_alshaya_promotion_subtype',
        'value' => [
          self::SUBTYPE_FIXED_PERCENTAGE_DISCOUNT_ORDER,
          self::SUBTYPE_FIXED_AMOUNT_DISCOUNT_ORDER,
          self::SUBTYPE_FREE_SHIPPING_ORDER,
        ],
        'operator' => 'IN',
      ],
    ]);

    foreach ($subTypePromotions as $subTypePromotion) {
      $message = '';

      $promotion_rule_id = $subTypePromotion->get('field_acq_promotion_rule_id')->getString();
      $sub_type = $subTypePromotion->get('field_alshaya_promotion_subtype')->getString();

      // Special condition for free shipping type promotion.
      if ($sub_type == self::SUBTYPE_FREE_SHIPPING_ORDER) {
        // For free shipping, we only show if it is applied.
        if (in_array($promotion_rule_id, $cartRulesApplied)) {
          $message = $this->t('Your order qualifies for free delivery.');
        }
      }
      // For the rest, we show only if they are not applied.
      elseif (!in_array($promotion_rule_id, $cartRulesApplied)) {
        // Get translation if available.
        $subTypePromotion = $this->entityRepository->getTranslationFromContext($subTypePromotion);

        // Get message from magento data stored in drupal.
        $message = $subTypePromotion->get('field_acq_promotion_label')->getString();
      }

      if ($message) {
        $promotions[$promotion_rule_id] = ['#markup' => $message];
      }
    }

    return array_filter($promotions);
  }

  /**
   * Get free gift sku entities for promotion id.
   *
   * @param int $promotion_id
   *   Promotion node id.
   *
   * @return array
   *   Free gift sku entities.
   */
  public function getFreeGiftSkuEntitiesByPromotionId(int $promotion_id) {
    $free_sku_entities = [];

    $promotion = $this->nodeStorage->load($promotion_id);

    if (!($promotion instanceof NodeInterface)) {
      return $free_sku_entities;
    }

    $free_skus = $promotion->get('field_free_gift_skus')->getValue();
    $free_skus = is_array($free_skus) ? array_column($free_skus, 'value') : [];

    foreach ($free_skus ?? [] as $free_sku) {
      $sku_entity = SKU::loadFromSku($free_sku);

      if ($sku_entity instanceof SKUInterface) {
        $free_sku_entities[] = $sku_entity;
      }
    }

    return $free_sku_entities;
  }

}
