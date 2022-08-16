<?php

namespace Drupal\alshaya_acm_promotion;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_promotion\AcqPromotionInterface;
use Drupal\acq_promotion\AcqPromotionPluginManager;
use Drupal\acq_promotion\AcqPromotionsManager;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\alshaya_acm\CartData;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;

/**
 * Class Alshaya Promotions Manager.
 *
 * @package Drupal\alshaya_acm_promotion
 */
class AlshayaPromotionsManager {

  use StringTranslationTrait;

  /**
   * Denotes the fixed_percentage_discount_order promotion subtype.
   */
  public const SUBTYPE_FIXED_PERCENTAGE_DISCOUNT_ORDER = 'fixed_percentage_discount_order';

  /**
   * Denotes the fixed_amount_discount_order promotion subtype.
   */
  public const SUBTYPE_FIXED_AMOUNT_DISCOUNT_ORDER = 'fixed_amount_discount_order';

  /**
   * Denotes the free_gift_order promotion subtype.
   */
  public const SUBTYPE_FREE_GIFT = 'free_gift_order';

  /**
   * Denotes the free_shipping_order promotion subtype.
   */
  public const SUBTYPE_FREE_SHIPPING_ORDER = 'free_shipping_order';

  /**
   * Denotes other promotion subtype.
   */
  public const SUBTYPE_OTHER = 'other';

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
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Images Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $imagesManager;

  /**
   * Acq Promotion Plugin Manager.
   *
   * @var \Drupal\acq_promotion\AcqPromotionPluginManager
   */
  protected $acqPromotionPluginManager;

  /**
   * Alshaya Acm Promotion Cache Manager.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $alshayaAcmPromotionCache;

  /**
   * AlshayaPromotionsManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Entity Manager service.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   The language manager service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The Entity repository service.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $images_manager
   *   Images Manager.
   * @param \Drupal\acq_promotion\AcqPromotionsManager $acq_promotions_manager
   *   Promotions manager service object from commerce code.
   * @param \Drupal\acq_promotion\AcqPromotionPluginManager $acqPromotionPluginManager
   *   Promotion Plugin Manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $alshayaAcmPromotionCache
   *   Alshaya Acm Promotion Cache Bin.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager,
                              LanguageManager $languageManager,
                              EntityRepositoryInterface $entityRepository,
                              SkuManager $sku_manager,
                              SkuImagesManager $images_manager,
                              AcqPromotionsManager $acq_promotions_manager,
                              AcqPromotionPluginManager $acqPromotionPluginManager,
                              CacheBackendInterface $alshayaAcmPromotionCache) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->languageManager = $languageManager;
    $this->entityRepository = $entityRepository;
    $this->skuManager = $sku_manager;
    $this->imagesManager = $images_manager;
    $this->acqPromotionsManager = $acq_promotions_manager;
    $this->acqPromotionPluginManager = $acqPromotionPluginManager;
    $this->alshayaAcmPromotionCache = $alshayaAcmPromotionCache;
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
   * @param array $sort_orders
   *   An array of associative array containing sort orders for query.
   *
   * @return array
   *   Array of node objects.
   *
   * @see \Drupal\Core\Entity\Query\QueryInterface
   */
  public function getAllPromotions(array $conditions = [], array $sort_orders = []) {
    $nodes = [];

    $query = $this->nodeStorage->getQuery();
    $query->condition('type', 'acq_promotion');
    foreach ($conditions as $condition) {
      if (!empty($condition['field']) && !empty($condition['value'])) {
        $condition['operator'] = empty($condition['operator']) ? '=' : $condition['operator'];
        $query->condition($condition['field'], $condition['value'], $condition['operator']);
      }
    }

    foreach ($sort_orders as $sort_order) {
      if (!empty($sort_order['field']) && !empty($sort_order['direction'])) {
        $query->sort($sort_order['field'], $sort_order['direction']);
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
        elseif ($promotion['action'] == 'ampromo_cart' || $promotion['action'] == 'ampromo_items') {
          return self::SUBTYPE_FREE_GIFT;
        }
      }
      elseif (isset($promotion['free_shipping']) && $promotion['free_shipping'] == 2) {
        return self::SUBTYPE_FREE_SHIPPING_ORDER;
      }
    }

    return self::SUBTYPE_OTHER;
  }

  /**
   * Get promotions threshold price.
   *
   * @param array $promotion_data
   *   Promotion node data.
   *
   * @return mixed|null
   *   Threshold Price.
   */
  public function getPromotionThresholdPrice(array $promotion_data) {
    $threshold_price = NULL;

    if (!empty($promotion_data['condition'])
      && !empty($promotion_data['condition']['conditions'])) {
      foreach ($promotion_data['condition']['conditions'] as $condition) {
        if ($condition['attribute'] === 'base_subtotal') {
          $threshold_price = $condition['value'];
        }
      }
    }

    return $threshold_price;
  }

  /**
   * Get Promotion condition operator.
   *
   * @param array $promotion_data
   *   Promotion node data.
   *
   * @return mixed|string
   *   Operator.
   */
  public function getPromotionOperator(array $promotion_data) {
    $operator = '';
    if (!empty($promotion_data['condition'])
      && !empty($promotion_data['condition']['conditions'])) {
      foreach ($promotion_data['condition']['conditions'] as $condition) {
        if ($condition['attribute'] === 'base_subtotal') {
          $operator = $condition['operator'];
        }
      }
    }
    return $operator;
  }

  /**
   * Helper function to fetch all cart promotions.
   *
   * @param array $selected_promotions
   *   An array of selected promotions.
   * @param array $cartRulesApplied
   *   An array of rules applied on the cart.
   * @param string $context
   *   Promotion context.
   *
   * @return array
   *   Array of all cart promotions.
   */
  public function getAllCartPromotions(array $selected_promotions, array $cartRulesApplied, $context = '') {
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

    $conditions = [
      [
        'field' => 'status',
        'value' => NodeInterface::PUBLISHED,
      ],
      [
        'field' => 'field_alshaya_promotion_subtype',
        'value' => [
          self::SUBTYPE_FIXED_PERCENTAGE_DISCOUNT_ORDER,
          self::SUBTYPE_FIXED_AMOUNT_DISCOUNT_ORDER,
          self::SUBTYPE_FREE_GIFT,
          self::SUBTYPE_FREE_SHIPPING_ORDER,
        ],
        'operator' => 'IN',
      ],
    ];

    if (!empty($context)) {
      $conditions[] = [
        'field' => 'field_acq_promotion_context',
        'value' => $context,
      ];
    }

    // Load all the promotions of the three specific types we check below.
    // We load only published promotions.
    $subTypePromotions = $this->getAllPromotions($conditions);

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
   * @return \Drupal\acq_sku\Entity\SKU[]
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

      if (!($sku_entity instanceof SKUInterface)) {
        continue;
      }

      switch ($sku_entity->bundle()) {
        case 'simple':
          if ($this->skuManager->isSkuFreeGift($sku_entity)) {
            $free_sku_entities[] = $sku_entity;
          }
          break;

        case 'configurable':
          // Add if we have at-least one variant available as free.
          foreach (Configurable::getChildSkus($sku_entity) as $child_sku) {
            $child = SKU::loadFromSku($child_sku);

            if ($child instanceof SKUInterface && $this->skuManager->isSkuFreeGift($child)) {
              $free_sku_entities[] = $sku_entity;
              break;
            }
          }
          break;
      }
    }

    return $free_sku_entities;
  }

  /**
   * Get promotions to show for particular sku in cart.
   *
   * @param string $sku
   *   SKU code.
   * @param string $applied_coupon
   *   Coupon already applied in cart.
   *
   * @return array
   *   Promotions array.
   */
  public function getPromotionsToShowForSkuInCart(string $sku, $applied_coupon = '') {
    $static = &drupal_static('getPromotionsToShowForSkuInCart', []);

    if (isset($static[$sku][$applied_coupon])) {
      return $static[$sku][$applied_coupon];
    }

    // For mobile, render free gift promotion as the last table column.
    // Get promotions for the SKU.
    $sku_entity = SKU::loadFromSku($sku);

    if (!($sku_entity instanceof SKUInterface)) {
      return [];
    }

    $line_item_promotions = $this->skuManager->getPromotionsFromSkuId($sku_entity, 'default', ['cart']);

    // Extract free gift promos.
    $free_gift_promos = [];
    foreach ($line_item_promotions as $promotion_id => $promotion) {
      $coupons = array_column($promotion['coupon_code'] ?? [], 'value');
      // If promo/free gift coupon is already applied on cart, don't show
      // it with the item on cart page.
      if (!empty($applied_coupon) &&  in_array($applied_coupon, $coupons)) {
        continue;
      }
      // If it is not free gift promotion, no need to process further.
      elseif (empty($promotion['skus'])) {
        continue;
      }

      $free_skus = $this->getFreeGiftSkuEntitiesByPromotionId($promotion_id);

      if (empty($free_skus)) {
        continue;
      }

      $free_gift_promos[$promotion_id] = $promotion;

      $route_parameters = [
        'node' => $promotion_id,
        'js' => 'nojs',
      ];

      $options = [
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => '{"width":"auto"}',
        ],
        'query' => [
          'coupon' => reset($coupons),
        ],
      ];

      // Promo type 0 => All SKUs below, 1 => One of the SKUs below.
      if ($promotion['promo_type'] == SkuManager::FREE_GIFT_SUB_TYPE_ONE_SKU) {
        $route_parameters['node'] = $promotion_id;
        $link_route = 'alshaya_acm_promotion.free_gifts_list';
        $message_arguments = [];
        $message = 'Click <span class="coupon-code">@coupon</span> to get a <span class="label">Free Gift</span> from @collection';

        // If only one product is available, display the detail view directly.
        if (count($free_skus) === 1) {
          $free_sku_entity = reset($free_skus);
          $route_parameters['acq_sku'] = $free_sku_entity->id();
          $link_route = 'alshaya_acm_promotion.free_gift_modal';
          $options['query']['promotion_id'] = $promotion_id;
          $promotion['text'] = $free_sku_entity->label();
          $message = 'Click <span class="coupon-code">@coupon</span> to get a <span class="label">Free Gift</span> @title';
        }

        $route_parameters['node'] = $promotion_id;
        $link_coupons = Link::createFromRoute(
          reset($coupons),
          $link_route,
          $route_parameters,
          $options
        )->toString();

        $link_collection = Link::createFromRoute(
          $promotion['text'],
          $link_route,
          $route_parameters,
          $options
        )->toString();

        $message_arguments['@coupon'] = $link_coupons;
        $message_arguments['@collection'] = $link_collection;
        $message_arguments['@title'] = $link_collection;

        // Ignoring variable in t() error, we prepare the variable above
        // and use as variable as there are two scenarios.
        // phpcs:ignore
        $free_gift_promos[$promotion_id]['link']['#markup'] = $this->t($message, $message_arguments);
      }
      else {
        $free_sku_entity = reset($free_skus);
        if ($free_sku_entity->bundle() == 'simple') {
          $free_gift_promos[$promotion_id]['sku_title'] = $free_sku_entity->get('name')->getString();
          $free_gift_promos[$promotion_id]['sku_entity_id'] = $free_sku_entity->id();
        }
        else {
          $route_parameters['acq_sku'] = $free_sku_entity->id();
          $options['query']['promotion_id'] = $promotion_id;
          $link = Link::createFromRoute(
            $promotion['text'],
            'alshaya_acm_promotion.free_gift_modal',
            $route_parameters,
            $options
          )->toString();

          $free_gift_promos[$promotion_id]['link']['#markup'] = $this->t('Click <span class="coupon-code">@coupon</span> to get a <span class="label">Free Gift</span> @title', [
            '@coupon' => $link,
            '@title' => $free_sku_entity->label(),
          ]);
        }

      }
    }

    $static[$sku][$applied_coupon] = $free_gift_promos;

    return $free_gift_promos;
  }

  /**
   * Helper function to fetch child skus of a configurable Sku.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   sku text or Sku object.
   *
   * @return \Drupal\acq_sku\Entity\SKU[]
   *   Array of child skus/ Child SKU when loading first child only.
   */
  public function getAvailableFreeGiftChildren(SKU $sku) {
    // Sanity check.
    if ($sku->getType() != 'configurable') {
      return [];
    }

    $children = [];
    foreach (Configurable::getChildSkus($sku) as $child_sku) {
      try {
        $child = SKU::loadFromSku($child_sku, $sku->language()->getId());

        // If child not available or is not a free gift, continue.
        if (!($child instanceof SKU) || !($this->skuManager->isSkuFreeGift($child))) {
          continue;
        }

        /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
        $plugin = $child->getPluginInstance();

        // We only want in-stock free gifts.
        if ($plugin->isProductInStock($child)) {
          $children[] = $child;
        }
      }
      catch (\Exception) {
        continue;
      }
    }

    return $children;
  }

  /**
   * Get sku to use for gallery.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   *
   * @return \Drupal\acq_sku\Entity\SKU
   *   SKU Entity for gallery.
   */
  public function getSkuForFreeGiftGallery(SKU $sku) {
    if ($this->imagesManager->hasMedia($sku)) {
      return $sku;
    }

    foreach ($this->getAvailableFreeGiftChildren($sku) as $child) {
      if ($this->imagesManager->hasMedia($child)) {
        return $child;
      }
    }

    return $sku;
  }

  /**
   * Get cart promotions.
   *
   * @return array
   *   List of promotions.
   */
  public function getCartPromotions() {
    $cartPromotionsApplied = &drupal_static(__FUNCTION__);

    if (!isset($cartPromotionsApplied)) {
      $cart = CartData::getCart();
      $appliedRules = ($cart instanceof CartData)
        ? $cart->getAppliedRules()
        : [];

      if (!empty($appliedRules)) {
        foreach ($appliedRules as $rule_id) {
          $promotion_node = $this->getPromotionByRuleId($rule_id);
          if ($promotion_node instanceof NodeInterface) {
            $cartPromotionsApplied[$rule_id] = $promotion_node;
          }
        }
      }
    }

    return $cartPromotionsApplied;
  }

  /**
   * Get sorted cart promotions.
   *
   * @return array
   *   List of promotions sorted by price and priority.
   */
  protected function getSortedCartPromotions() {
    $cid = 'alshaya_acm_promotions:cart:sorted';
    $cache = $this->alshayaAcmPromotionCache->get($cid);

    if (!empty($cache)) {
      return $cache->data;
    }

    // Get all cart promotions which are eligible for promotion label display.
    $allApplicablePromotions = $this->getAllPromotions(
      [
        [
          'field' => 'status',
          'value' => NodeInterface::PUBLISHED,
        ],
        [
          'field' => 'field_alshaya_promotion_subtype',
          'value' => array_keys($this->getAcqPromotionTypes()),
          'operator' => 'IN',
        ],
      ],
      [
        [
          'field' => 'field_acq_promotion_sort_order',
          'direction' => 'ASC',
        ],
      ]
    );

    // Prepare sorted list of cart promotions based on priority, base_subtotal.
    $cartPromotions = [];
    foreach ($allApplicablePromotions as $promotion) {
      if ($promotion instanceof NodeInterface) {
        $order = $promotion->get('field_acq_promotion_sort_order')->getString();
        $subtype = $promotion->get('field_alshaya_promotion_subtype')->getString();
        $promotion_data = $promotion->get('field_acq_promotion_data')->getString();
        // phpcs:ignore
        $promotion_data = unserialize($promotion_data);
        $threshold_price = $this->getPromotionThresholdPrice($promotion_data);

        // Free Shipping promotions needs to be prioritized for display.
        if ($subtype === self::SUBTYPE_FREE_SHIPPING_ORDER) {
          if (isset($order) && isset($threshold_price)) {
            $cartPromotions[self::SUBTYPE_FREE_SHIPPING_ORDER][$order][$threshold_price][] = $promotion->id();
          }
        }
        elseif (isset($order) && isset($threshold_price)) {
          $cartPromotions[$order][$threshold_price][] = $promotion->id();
        }
      }
    }

    $this->alshayaAcmPromotionCache->set($cid, $cartPromotions, Cache::PERMANENT, ['node_type:acq_promotion']);

    return $cartPromotions;
  }

  /**
   * Get all promotion plugin types.
   *
   * @return array
   *   Array of Promotion Plugin Types.
   */
  public function getAcqPromotionTypes() {
    $definitions = $this->acqPromotionPluginManager->getDefinitions();

    $types = [];
    foreach ($definitions as $definition) {
      if (!empty($definition['status'])) {
        switch ($definition['id']) {
          case 'buy_x_get_y_cheapest_free':
            $types[self::SUBTYPE_OTHER][] = $definition['label'];
            break;

          default:
            $types[$definition['id']] = $definition['label'];
        }
      }
    }

    if (!empty($types[self::SUBTYPE_OTHER])) {
      $types[self::SUBTYPE_OTHER] = $this->t('Others : @others', [
        '@others' => implode(', ', $types[self::SUBTYPE_OTHER]),
      ]);
    }

    return $types;
  }

  /**
   * Get promotion plugin type and active/inactive label.
   *
   * @param \Drupal\node\NodeInterface $promotion
   *   Promotion Node.
   * @param bool $status
   *   Active or Inactive Flag.
   *
   * @return array|null
   *   Promotion data.
   */
  public function getPromotionData(NodeInterface $promotion, $status = TRUE) {
    $data = NULL;
    $field_alshaya_promotion_subtype = $promotion->get('field_alshaya_promotion_subtype')->getString();
    $acqPromotionTypes = $this->getAcqPromotionTypes();

    // Get matching plugin type.
    if (!empty($acqPromotionTypes[$field_alshaya_promotion_subtype])) {
      try {
        $plugin_id = $field_alshaya_promotion_subtype;
        if ($field_alshaya_promotion_subtype === self::SUBTYPE_OTHER) {
          $plugin_id = $promotion->get('field_acq_promotion_action')->getString();
        }

        // Get translated version of promotion node.
        $promotion = $this->entityRepository->getTranslationFromContext($promotion);
        $promotionPlugin = $this->acqPromotionPluginManager->createInstance(
          $plugin_id,
          [],
          $promotion
        );
        if ($promotionPlugin instanceof AcqPromotionInterface) {
          $label = $status ? $promotionPlugin->getActiveLabel() : $promotionPlugin->getInactiveLabel();

          if (!empty($label)) {
            $data = [
              'type' => $field_alshaya_promotion_subtype,
              'label' => $label,
            ];

            // Add threshold_reached flag to update promotion label classes.
            $promotionCartStatus = $promotionPlugin->getPromotionCartStatus();
            if ($promotionCartStatus === AcqPromotionInterface::STATUS_CAN_BE_APPLIED) {
              $data['threshold_reached'] = TRUE;
              $data['coupon'] = $promotion->get('field_coupon_code')->getString();

              // Get Promotion Percentage.
              $promotion_data = $promotion->get('field_acq_promotion_data')->getString();
              // phpcs:ignore
              $promotion_data = unserialize($promotion_data);
              if (!empty($promotion_data) && !empty($promotion_data['discount'])) {
                $data['couponDiscount'] = $promotion_data['discount'];
              }
            }
          }
        }
      }
      catch (\Exception $exception) {
        watchdog_exception('alshaya_acm_promotion', $exception);
      }
    }

    return $data;
  }

  /**
   * Fetches Inactive Cart promotion.
   *
   * @return mixed|null
   *   Inactive promotion node.
   */
  public function getInactiveCartPromotion() {
    $inactiveCartPromotion = &drupal_static(__FUNCTION__);

    if (isset($inactiveCartPromotion)) {
      return $inactiveCartPromotion;
    }

    $allCartPromotions = $this->getSortedCartPromotions();
    $appliedPromotionIds = [];
    $cartPromotionsApplied = $this->getCartPromotions() ?? [];
    foreach ($cartPromotionsApplied as $promotion) {
      $appliedPromotionIds[] = $promotion->id();
    }

    // Extract next eligible cart free shipping promotion.
    if (!empty($allCartPromotions[self::SUBTYPE_FREE_SHIPPING_ORDER])) {
      foreach ($allCartPromotions[self::SUBTYPE_FREE_SHIPPING_ORDER] as $priceSortedPromotions) {
        ksort($priceSortedPromotions);

        foreach ($priceSortedPromotions as $promotions) {
          foreach ($promotions as $promotion) {
            if (!in_array($promotion, $appliedPromotionIds)) {
              $promotion = $this->nodeStorage->load($promotion);
              if ($promotion instanceof NodeInterface) {
                $inactiveCartPromotion = $promotion;
                return $promotion;
              }
            }
          }
        }
      }
      unset($allCartPromotions[self::SUBTYPE_FREE_SHIPPING_ORDER]);
    }

    // Extract next eligible cart promotion based on priority and price.
    foreach ($allCartPromotions as $priceSortedPromotions) {
      ksort($priceSortedPromotions);

      foreach ($priceSortedPromotions as $promotions) {
        foreach ($promotions as $promotion) {
          if (!in_array($promotion, $appliedPromotionIds)) {
            $promotion = $this->nodeStorage->load($promotion);
            if ($promotion instanceof NodeInterface) {
              $inactiveCartPromotion = $promotion;
              return $promotion;
            }
          }
        }
      }
    }

    return FALSE;
  }

  /**
   * Get available cart promotion code.
   *
   * @return string
   *   Promotion code label.
   */
  public function getAvailableCartCode(array $applied_rules) {
    $label = '';

    // Generate label for the applicable promotion.
    $applicablePromotion = $this->getInactiveCartPromotion();
    if ($applicablePromotion instanceof NodeInterface) {
      $label = $this->getPromotionCodeLabel($applicablePromotion);
    }

    return $label;
  }

  /**
   * Get promotion code label using AcqPromotion plugin.
   *
   * @param \Drupal\node\NodeInterface $promotion
   *   Promotion Node.
   *
   * @return mixed
   *   Promotion Cart Label.
   */
  private function getPromotionCodeLabel(NodeInterface $promotion) {
    $field_alshaya_promotion_subtype = $promotion->get('field_alshaya_promotion_subtype')->getString();
    $acqPromotionTypes = $this->getAcqPromotionTypes();
    $label = '';

    // Get matching plugin type.
    if (!empty($acqPromotionTypes[$field_alshaya_promotion_subtype])) {
      try {
        $plugin_id = $field_alshaya_promotion_subtype;
        if ($field_alshaya_promotion_subtype === self::SUBTYPE_OTHER) {
          $plugin_id = $promotion->get('field_acq_promotion_action')->getString();
        }

        $promotionPlugin = $this->acqPromotionPluginManager->createInstance(
          $plugin_id,
          [],
          $promotion
        );
        if ($promotionPlugin instanceof AcqPromotionInterface) {
          $label = $promotionPlugin->getPromotionCodeLabel();
        }
      }
      catch (\Exception $exception) {
        watchdog_exception('alshaya_acm_promotion', $exception);
      }
    }

    return $label;
  }

}
