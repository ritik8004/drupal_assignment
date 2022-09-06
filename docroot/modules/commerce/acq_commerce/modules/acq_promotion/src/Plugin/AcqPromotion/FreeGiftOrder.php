<?php

namespace Drupal\acq_promotion\Plugin\AcqPromotion;

use Drupal\acq_promotion\AcqPromotionBase;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_promotion\AlshayaPromotionsManager;

/**
 * Provides the Free Gift cart level promotion.
 *
 * @ACQPromotion(
 *   id = "free_gift_order",
 *   label = @Translation("Free Gift Order"),
 *   status = TRUE,
 * )
 */
class FreeGiftOrder extends AcqPromotionBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Alshaya Promotions Manager.
   *
   * @var \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager
   */
  protected $alshayaPromotionsManager;

  /**
   * Free Gift constructor.
   *
   * @param array $configuration
   *   Configurations.
   * @param string $plugin_id
   *   Plugin Id.
   * @param mixed $plugin_definition
   *   Plugin Definition.
   * @param \Drupal\node\NodeInterface $promotionNode
   *   Promotion Node.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager $alshayaPromotionsManager
   *   Alshaya Promotions Manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              NodeInterface $promotionNode,
                              SkuManager $sku_manager,
                              AlshayaPromotionsManager $alshayaPromotionsManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $promotionNode);
    $this->skuManager = $sku_manager;
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
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('alshaya_acm_promotion.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getInactiveLabel() {
    $label = parent::getInactiveLabel();
    $promotion_id = $this->promotionNode->id();
    $promotion_data = unserialize($this->promotionNode->get('field_acq_promotion_data')->getString());

    if ($promotion_data['extension']['promo_type'] == SkuManager::FREE_GIFT_SUB_TYPE_ONE_SKU) {
      $free_skus = $this->alshayaPromotionsManager->getFreeGiftSkuEntitiesByPromotionId($promotion_id);
      $free_sku_entity = reset($free_skus);
      $threshold_price = $this->alshayaPromotionsManager->getPromotionThresholdPrice($promotion_data);
      $link = $this->getFreeGiftLink($free_sku_entity);

      $label = $this->t('Shop for @threshold and get a @gift FREE', [
        '@threshold' => alshaya_acm_price_get_formatted_price($threshold_price),
        '@gift' => $link,
      ]);
     }

    return $label;
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveLabel() {
    $free_skus = $this->alshayaPromotionsManager->getFreeGiftSkuEntitiesByPromotionId($this->promotionNode->id());
    $free_sku_entity = reset($free_skus);

    $link = $this->getFreeGiftLink($free_sku_entity);
    $label = $this->t('Your Free Gift @gift has been added to the cart', [
      '@gift' => $link,
    ]);

    return $label;
  }

  /**
   * Get link of free gift to open in modal.
   *
   * @param object $free_sku_entity
   *   Free gift sku entity.
   *
   * @return object
   *   Link object for free gift.
   */
  public function getFreeGiftLink($free_sku_entity) {
    if (empty($free_sku_entity)) {
      return NULL;
    }

    $link = Link::createFromRoute(
      $free_sku_entity->name->getString(),
      'alshaya_acm_promotion.free_gift_modal',
      [
        'acq_sku' => $free_sku_entity->id(),
        'js' => 'nojs',
      ],
      [
        'attributes' => [
          'class' => ['use-ajax', 'cart-promotion-label-gift'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => '{"width":"auto"}',
        ],
      ]
    )->toString();

    return $link;
  }

}
