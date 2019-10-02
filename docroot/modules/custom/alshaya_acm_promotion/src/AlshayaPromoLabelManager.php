<?php

namespace Drupal\alshaya_acm_promotion;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\Core\Cache\CacheableAjaxResponse;

/**
 * Class AlshayaPromoLabelManager.
 *
 * @package Drupal\alshaya_acm_promotion
 */
class AlshayaPromoLabelManager {

  use StringTranslationTrait;

  const DYNAMIC_PROMOTION_ELIGIBLE_ACTIONS = ['buy_x_get_y_cheapest_free'];
  const ALSHAYA_PROMOTIONS_STATIC_PROMO = 0;
  const ALSHAYA_PROMOTIONS_DYNAMIC_PROMO = 1;

  /**
   * Node storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Cart Manager.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartManager;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AlshayaPromoLabelManager constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\acq_cart\CartStorageInterface $cartManager
   *   Cart Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config Factory.
   */
  public function __construct(SkuManager $sku_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              CartStorageInterface $cartManager,
                              ConfigFactoryInterface $configFactory) {
    $this->skuManager = $sku_manager;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->cartManager = $cartManager;
    $this->configFactory = $configFactory;
  }

  /**
   * Check if dynamic_labels functionality is enabled.
   *
   * @return array|mixed|null
   *   Flag to check dynamic_labels config is enabled or not.
   */
  public function isDynamicLabelsEnabled() {
    return $this->configFactory->get('alshaya_acm_promotion.settings')->get('dynamic_labels');
  }

  /**
   * For a sku, filter dynamic promo label eligible promotions.
   *
   * @param array|\Drupal\Core\Entity\EntityInterface[] $promotionNodes
   *   List of promotion nodes.
   *
   * @return array|mixed
   *   List of Eligible Promotions.
   */
  private function filterEligiblePromotions($promotionNodes) {
    // Get SKU Promotions.
    $eligiblePromotions = [];

    foreach ($promotionNodes as $promotionNode) {
      if (is_numeric($promotionNode)) {
        $promotionNode = $this->nodeStorage->load($promotionNode);
      }

      if (!($promotionNode instanceof NodeInterface)) {
        continue;
      }

      $promotion_action = $promotionNode->get('field_acq_promotion_action')->getString();
      if (in_array($promotion_action, self::DYNAMIC_PROMOTION_ELIGIBLE_ACTIONS)) {
        $eligiblePromotions[] = $promotionNode;
      }
    }

    return $eligiblePromotions;
  }

  /**
   * Check if dynamic promotion label applies.
   *
   * @param array|\Drupal\Core\Entity\EntityInterface[] $promotionNodes
   *   List of promotion nodes.
   *
   * @return int
   *   Promo Type Flag.
   */
  public function checkPromoLabelType($promotionNodes) {
    // Get SKU Promotions.
    $eligiblePromotions = $this->filterEligiblePromotions($promotionNodes);

    return !empty($eligiblePromotions)
      ? self::ALSHAYA_PROMOTIONS_DYNAMIC_PROMO
      : self::ALSHAYA_PROMOTIONS_STATIC_PROMO;
  }

  /**
   * Fetch promotion dynamic label.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Product SKU.
   * @param null|\Drupal\Core\Entity\EntityInterface[] $promotion_nodes
   *   List of promotion nodes.
   *
   * @return string
   *   Dynamic Promotion Label or NULL.
   */
  public function getSkuPromoDynamicLabel(SKU $sku, $promotion_nodes = NULL) {
    $labels = NULL;
    $promos = $this->getCurrentSkuPromos($sku, 'links', $promotion_nodes);
    if (!empty($promos)) {
      $labels = implode('<br>', $promos);
    }

    return $labels;
  }

  /**
   * Fetch current SKU Dynamic Promos.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Product SKU.
   * @param string $view_mode
   *   Links or default.
   * @param null|\Drupal\Core\Entity\EntityInterface[] $promotion_nodes
   *   List of promotion nodes.
   *
   * @return array
   *   List of promotions.
   */
  public function getCurrentSkuPromos(SKU $sku, $view_mode, $promotion_nodes = NULL) {
    // Fetch parent SKU for the current SKU.
    $parentSku = $this->skuManager->getParentSkuBySku($sku);
    if (!empty($parentSku)) {
      $sku = $parentSku;
    }

    $promos = [];

    if (is_null($promotion_nodes)) {
      $promotion_nodes = $this->skuManager->getSkuPromotions($sku, ['cart']);
    }

    foreach ($promotion_nodes as $promotion_node) {
      if (is_numeric($promotion_node)) {
        $promotion_node = $this->nodeStorage->load($promotion_node);
      }

      if (!($promotion_node instanceof NodeInterface)) {
        continue;
      }

      $promoDisplay = $this->preparePromoDisplay($promotion_node, $sku, $view_mode);
      if ($promoDisplay) {
        $promos[$promotion_node->id()] = $promoDisplay;
      }
    }

    return $promos;
  }

  /**
   * Prepare promotion display based on view_mode.
   *
   * @param \Drupal\node\NodeInterface $promotion
   *   Promotion Node.
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   * @param string $view_mode
   *   Links or default.
   *
   * @return array|string|null
   *   Return render array of Promos.
   */
  private function preparePromoDisplay(NodeInterface $promotion, SKU $sku, $view_mode) {
    $promoDisplay = FALSE;
    $promotionLabel = $this->getPromotionLabel($promotion, $sku);

    if (!empty($promotionLabel)) {
      switch ($view_mode) {
        case 'links':
          // In case of links just send dynamic label.
          try {
            if (!empty($promotionLabel['dynamic_label'])) {
              $promoDisplay = $promotion
                ->toLink(
                  $promotionLabel['dynamic_label'],
                  'canonical',
                  ['attributes' => ['class' => 'sku-dynamic-promotion-link']]
                )
                ->toString()
                ->getGeneratedLink();
            }
          }
          catch (\Exception $exception) {
            watchdog_exception('alshaya_acm_promotion', $exception);
          }
          break;

        default:
          $description = '';
          $description_item = $promotion->get('field_acq_promotion_description')->first();
          if ($description_item) {
            $description = $description_item->getValue();
          }

          $discount_type = $promotion->get('field_acq_promotion_disc_type')->getString();
          $discount_value = $promotion->get('field_acq_promotion_discount')->getString();

          if (!empty($promotionLabel['original_label'])) {
            $promoDisplay = [
              'text' => $promotionLabel['original_label'],
              'description' => $description,
              'discount_type' => $discount_type,
              'discount_value' => $discount_value,
              'rule_id' => $promotion->get('field_acq_promotion_rule_id')->getString(),
            ];

            if (!empty($promotionLabel['dynamic_label'])) {
              $promoDisplay['dynamic_label'] = [
                'text' => $promotionLabel['dynamic_label'],
              ];
            }
          }

          if (!empty($free_gift_skus = $promotion->get('field_free_gift_skus')->getValue())) {
            $promoDisplay['skus'] = $free_gift_skus;
          }

          if (!empty($coupon_code = $promotion->get('field_coupon_code')->getValue())) {
            $promoDisplay['coupon_code'] = $coupon_code;
          }
      }
    }

    return $promoDisplay;
  }

  /**
   * Get Dynamic Promotion label based on cart status.
   *
   * @param \Drupal\node\NodeInterface $promotion
   *   Promotion Node.
   * @param \Drupal\acq_sku\Entity\SKU $currentSKU
   *   Product SKU.
   *
   * @return array|mixed
   *   Return original and dynamic promo label.
   */
  private function getPromotionLabel(NodeInterface $promotion, SKU $currentSKU) {
    $label = [
      'original_label' => $promotion->get('field_acq_promotion_label')->getString(),
      'dynamic_label' => '',
    ];
    if (!empty($this->isDynamicLabelsEnabled())) {
      $cartSKUs = $this->cartManager->getCartSkus();
      $eligibleSKUs = $this->skuManager->getSkutextsForPromotion($promotion, TRUE);

      // If cart is not empty and has matching products.
      if (!empty($cartSKUs)
        && in_array($currentSKU->getSku(), $eligibleSKUs)
        && !empty(array_intersect($eligibleSKUs, $cartSKUs))) {
        $this->overridePromotionLabel($label, $promotion, $eligibleSKUs);
      }
    }

    return $label;
  }

  /**
   * Overrides the promo label.
   *
   * @param string|mixed $label
   *   Default Label.
   * @param \Drupal\node\NodeInterface $promotion
   *   Promotion Node.
   * @param array|mixed $eligibleSKUs
   *   Eligible SKUs as per promotion.
   */
  private function overridePromotionLabel(&$label, NodeInterface $promotion, $eligibleSKUs) {
    // Calculate cart quantity.
    $eligible_cart_qty = 0;
    $cart_items = $this->cartManager->getCart(FALSE)->items();
    foreach ($cart_items as $item) {
      if (in_array($item['sku'], $eligibleSKUs)) {
        $eligible_cart_qty += $item['qty'];
      }
    }
    // Calculate X and Y.
    $promotion_data = unserialize($promotion->get('field_acq_promotion_data')->getString());
    if (isset($promotion_data['step']) && isset($promotion_data['discount'])) {
      $discount_step = $promotion_data['step'];
      $discount_amount = $promotion_data['discount'];
      $z = ($discount_step + $discount_amount) - $eligible_cart_qty;
      // Apply z-logic to generate label.
      if ($z >= 1) {
        $label['dynamic_label'] = $this->t('Add @z more to get FREE item', ['@z' => $z]);
      }
      else {
        $label['dynamic_label'] = $this->t('Add more and keep saving');
      }
    }
  }

  /**
   * Prepare or update response commands.
   *
   * @param string $label
   *   Label HTML.
   * @param string $skuId
   *   Sku ID.
   * @param \Drupal\Core\Ajax\AjaxResponse|null $response
   *   Ajax Response.
   *
   * @return \Drupal\Core\Cache\CacheableAjaxResponse
   *   Ajax Response.
   */
  public function prepareResponse($label, $skuId, $response = NULL) {
    if (empty($response)) {
      $response = new CacheableAjaxResponse();
    }

    if ($response instanceof AjaxResponse) {
      $dynamic_label_selector = '.acq-content-product .promotions .promotions-dynamic-label.sku-' . $skuId;
      $response->addCommand(new HtmlCommand($dynamic_label_selector, $label));
    }

    return $response;
  }

}
