<?php

namespace Drupal\acq_promotion\Plugin\AcqPromotion;

use Drupal\acq_cart\CartInterface;
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
 *   status = TRUE,
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
    $coupon = $this->promotionNode->get('field_coupon_code')->getString();
    if (!empty($coupon)) {
      $classes = 'promotion-coupon-code';
      if (!empty($promotion_data) && !empty($promotion_data['discount']) && $this->checkThresholdReached($promotion_data)) {
        $label = $this->t('Your order qualifies for @percent% OFF', [
          '@percent' => $promotion_data['discount'],
        ]);

        $classes .= ' available';

        $label .= '<span class="promotion-coupon-details promotion-available-code"> ';
        $label .= $this->t('Use the code:')->__toString();
        $label .= '<span class="' . $classes . '" data-coupon-code="' . $coupon . '">' . $coupon . '</span>';
        $label .= '</span>';
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
  public function checkThresholdReached(array $promotion_data = NULL) {
    if (is_null($promotion_data)) {
      $promotion_data = $this->promotionNode->get('field_acq_promotion_data')->getString();
      $promotion_data = unserialize($promotion_data);
    }

    $reached = FALSE;
    $cart = $this->cartStorage->getCart(FALSE);

    // Compare Cart sub against promotion condition of Subtotal as threshold.
    if ($cart instanceof CartInterface
      && !empty($cartTotals = $cart->totals())) {
      $cartValue = $cartTotals['sub'];
      $threshold_price = $this->alshayaPromotionsManager->getPromotionThresholdPrice($promotion_data);

      if ((isset($cartValue) && isset($threshold_price)) && $cartValue >= $threshold_price) {
        $reached = TRUE;
      }
    }

    return $reached;
  }

  /**
   * {@inheritdoc}
   */
  public function getPromotionCartStatus() {
    return $this->checkThresholdReached() ? self::STATUS_CAN_BE_APPLIED : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPromotionCodeLabel() {
    $label = '';
    if ($this->checkThresholdReached()) {
      $coupon = $this->promotionNode->get('field_coupon_code')->getString();

      $label = '<div class="promotion-coupon-code available" data-coupon-code="' . $coupon . '">' . $coupon . '</div>';
      $promotion_data = $this->promotionNode->get('field_acq_promotion_data')->getString();
      $promotion_data = unserialize($promotion_data);

      if (!empty($promotion_data) && !empty($promotion_data['discount'])) {
        $label .= '<span class="code-desc">' . $this->t('Use and get @percent% off', [
          '@percent' => $promotion_data['discount'],
        ])->__toString() . '</span>';
      }
    }

    return $label;
  }

}
