<?php

namespace Drupal\acq_promotion\Plugin\AcqPromotion;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_promotion\AcqPromotionBase;
use Drupal\alshaya_acm_promotion\AlshayaPromotionsManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the Buy X Get Y Free cart level promotion.
 *
 * @ACQPromotion(
 *   id = "fixed_percentage_discount_order",
 *   label = @Translation("Get Y% discount on order over KWD X"),
 * )
 */
class FixedPercentageDiscountOrder extends AcqPromotionBase implements ContainerFactoryPluginInterface {

  /**
   * Cart Storage.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Alshaya Promotions Manager.
   *
   * @var \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager
   */
  protected $alshayaPromotionsManager;

  /**
   * BuyXgetYfree constructor.
   *
   * @param array $configuration
   *   Configurations.
   * @param string $plugin_id
   *   Plugin Id.
   * @param mixed $plugin_definition
   *   Plugin Definition.
   * @param \Drupal\node\NodeInterface $promotionNode
   *   Promotion Node.
   * @param \Drupal\acq_cart\CartStorageInterface $cartStorage
   *   Cart Session Storage.
   * @param \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager $alshayaPromotionsManager
   *   Alshaya Promotions Manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              NodeInterface $promotionNode,
                              CartStorageInterface $cartStorage,
                              AlshayaPromotionsManager $alshayaPromotionsManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $promotionNode);
    $this->cartStorage = $cartStorage;
    $this->alshayaPromotionsManager = $alshayaPromotionsManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, $promotionNode = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $promotionNode,
      $container->get('acq_cart.cart_storage'),
      $container->get('alshaya_acm_promotion.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getInactiveLabel() {
    $label = parent::getInactiveLabel();
    $promotion_data = $this->promotionNode->get('field_acq_promotion_data')->getString();
    $promotion_data = unserialize($promotion_data);

    // Override label to include coupon if threshold has reached.
    if (!empty($promotion_data) && $this->checkThresholdReached($promotion_data)) {
      $percent = NULL;

      if (!empty($promotion_data) && !empty($promotion_data['discount'])) {
        $percent = $promotion_data['discount'];
      }

      $coupon = $this->promotionNode->get('field_coupon_code')->getString();
      if (!empty($coupon)) {
        $label = $this->t(
          'Your order qualifies for @percent% OFF <div class="promotion-coupon-details"> Use code: <div class="promotion-coupon-code">@code</div></div>',
          [
            '@percent' => $percent,
            '@code' => $coupon,
          ]
        );
      }
    }

    return $label;
  }

  /**
   * Compare cart sub total and promotion threshold price.
   *
   * @param array $promotion_data
   *   Promotion Data.
   *
   * @return bool
   *   Flag if threshold value reached.
   */
  protected function checkThresholdReached(array $promotion_data) {
    $reached = FALSE;
    $cart = $this->cartStorage->getCart(FALSE);
    if ($cart instanceof CartStorageInterface
      && !empty($cartTotals = $cart->totals())) {
      $cartValue = $cartTotals['sub'];
      $threshold_price = $this->alshayaPromotionsManager->getPromotionThresholdPrice($promotion_data);

      if ((isset($cartValue) && isset($threshold_price)) && $cartValue >= $threshold_price) {
        $reached = TRUE;
      }
    }

    return $reached;
  }

}
