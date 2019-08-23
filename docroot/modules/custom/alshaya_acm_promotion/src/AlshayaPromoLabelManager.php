<?php

namespace Drupal\alshaya_acm_promotion;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\views_ajax_get\CacheableAjaxResponse;

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
   * AlshayaPromoLabelManager constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type MAanager.
   */
  public function __construct(SkuManager $sku_manager,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->skuManager = $sku_manager;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
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

    if (!empty($eligiblePromotions)) {
      return self::ALSHAYA_PROMOTIONS_DYNAMIC_PROMO;
    }
    else {
      return self::ALSHAYA_PROMOTIONS_STATIC_PROMO;
    }
  }

  /**
   * Fetch Promotions and corresponding labels.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Product SKU.
   * @param \Drupal\acq_cart\CartStorageInterface $cartStorage
   *   Cart Session Storage.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku Manager.
   *
   * @return string
   *   Dynamic Promotion Label.
   */
  public function getCurrentSkuPromoLabel(SKU $sku, CartStorageInterface $cartStorage, SkuManager $skuManager) {
    $labels = [];
    $promotion_nodes = $this->skuManager->getSkuPromotions($sku, ['cart']);
    $eligiblePromotions = $this->filterEligiblePromotions($promotion_nodes);

    foreach ($eligiblePromotions as $eligiblePromotion) {
      $eligiblePromotionLabel = $this->getPromotionLabel($eligiblePromotion, $sku, $cartStorage, $skuManager);
      if (!empty($eligiblePromotionLabel)) {
        // Generate Link.
        try {
          $labels[] = $eligiblePromotion
            ->toLink($eligiblePromotionLabel)
            ->toString()
            ->getGeneratedLink();
        }
        catch (\Exception $exception) {
          watchdog_exception('alshaya_acm_promotion', $exception);
        }

      }
    }

    $labels = implode('<br>', $labels);
    return '<div>' . $labels . '</div>';
  }

  /**
   * Get Dynamic Promotion label based on cart status.
   *
   * @param \Drupal\node\NodeInterface $promotion
   *   Promotion Node.
   * @param \Drupal\acq_sku\Entity\SKU $currentSKU
   *   Product SKU.
   * @param \Drupal\acq_cart\CartStorageInterface $cartStorage
   *   Cart Session Storage.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku Manager.
   *
   * @return string|mixed
   *   Return dynamic promo label.
   */
  private function getPromotionLabel(NodeInterface $promotion, SKU $currentSKU, CartStorageInterface $cartStorage, SkuManager $skuManager) {
    $label = $promotion->get('field_acq_promotion_label')->getString();
    $cartSKUs = $cartStorage->getCartSkus();
    $eligibleSKUs = $skuManager->getSkutextsForPromotion($promotion);

    // If cart is not empty and has matching products.
    if (!empty($cartSKUs)
      && in_array($currentSKU->getSku(), $eligibleSKUs)
      && !empty(array_intersect($eligibleSKUs, $cartSKUs))) {
      $this->overridePromotionLabel($label, $promotion, $eligibleSKUs, $cartStorage);
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
   * @param \Drupal\acq_cart\CartStorageInterface $cartStorage
   *   Cart Session Storage.
   */
  private function overridePromotionLabel(&$label, NodeInterface $promotion, $eligibleSKUs, CartStorageInterface $cartStorage) {
    // Calculate cart quantity.
    $eligible_cart_qty = 0;
    $cart_items = $cartStorage->getCart(FALSE)->items();
    foreach ($cart_items as $item) {
      if (in_array($item['sku'], $eligibleSKUs)) {
        $eligible_cart_qty += $item['qty'];
      }
    }
    // Calculate X and Y.
    $promotion_data = unserialize($promotion->get('field_acq_promotion_data')->getString());
    if (isset($promotion_data['discount_step']) && isset($promotion_data['discount_amount'])) {
      $discount_step = $promotion_data['discount_step'];
      $discount_amount = $promotion_data['discount_amount'];
      $z = ($discount_step + $discount_amount) - $eligible_cart_qty;
      // Apply z-logic to generate label.
      if ($z >= 1) {
        $label = $this->t('Add @Z more to get FREE item', ['@Z' => $z]);
      }
      else {
        $label = $this->t('Add more and keep saving');
      }
    }
  }

  /**
   * Prepare or update response commands.
   *
   * @param string $label
   *   Label HTML.
   * @param \Drupal\Core\Ajax\AjaxResponse|null $response
   *   Ajax Response.
   *
   * @return \Drupal\views_ajax_get\CacheableAjaxResponse
   *   Ajax Response.
   */
  public function prepareResponse($label, $response = NULL) {
    if (empty($response)) {
      $response = new CacheableAjaxResponse();
    }

    if ($response instanceof AjaxResponse) {
      $response->addCommand(new HtmlCommand('.acq-content-product .promotions div', $label));
      $response->addCommand(new InvokeCommand('.acq-content-product .promotions div', 'removeClass', ['hidden']));
    }

    return $response;
  }

}
