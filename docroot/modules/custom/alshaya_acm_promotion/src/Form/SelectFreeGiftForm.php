<?php

namespace Drupal\alshaya_acm_promotion\Form;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\alshaya_acm_product\Service\AddToCartFormHelper;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_promotion\AlshayaPromotionsManager;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SelectFreeGiftForm.
 *
 * @package Drupal\alshaya_acm_promotion\Form
 */
class SelectFreeGiftForm extends FormBase {

  /**
   * Promotions Manager.
   *
   * @var \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager
   */
  private $promotionsManager;

  /**
   * Sku Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * Add to cart form helper.
   *
   * @var \Drupal\alshaya_acm_product\Service\AddToCartFormHelper
   */
  private $formHelper;

  /**
   * Node storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * SKU Info Helper.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuInfoHelper
   */
  protected $skuInfoHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm_promotion.manager'),
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('alshaya_acm_product.add_to_cart_form_helper'),
      $container->get('entity_type.manager'),
      $container->get('alshaya_acm_product.sku_info')
    );
  }

  /**
   * SelectFreeGiftForm constructor.
   *
   * @param \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager $promotions_manager
   *   Promotions Manager.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   Sku Manager.
   * @param \Drupal\alshaya_acm_product\Service\AddToCartFormHelper $add_to_cart_form_helper
   *   Add to cart form helper.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\alshaya_acm_product\Service\SkuInfoHelper $sku_info_helper
   *   SKU Info Helper.
   */
  public function __construct(AlshayaPromotionsManager $promotions_manager,
                              SkuManager $sku_manager,
                              AddToCartFormHelper $add_to_cart_form_helper,
                              EntityTypeManagerInterface $entity_type_manager,
                              SkuInfoHelper $sku_info_helper) {
    $this->promotionsManager = $promotions_manager;
    $this->skuManager = $sku_manager;
    $this->formHelper = $add_to_cart_form_helper;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->skuInfoHelper = $sku_info_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'select_free_gift';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#cache']['contexts'][] = 'route';

    $storage = $form_state->getStorage();
    $coupon = $storage['coupon'] ?? '';
    $sku = SKU::loadFromSku($storage['sku'] ?? '');
    $promotion_id = $storage['promotion_id'] ?? '';

    if (!($sku instanceof SKUInterface) || empty($coupon) || empty($promotion_id)) {
      // Return empty form if coupon, promotion_id or sku is missing.
      // Typically used for modal window of free gift from cart page.
      return $form;
    }

    if ($promotion_node = $this->nodeStorage->load($promotion_id)) {
      // phpcs:ignore
      $data = unserialize($promotion_node->get('field_acq_promotion_data')->getString());
      $promo_type = $data['extension']['promo_type'] ?? SkuManager::FREE_GIFT_SUB_TYPE_ALL_SKUS;
      $promo_rule_id = $promotion_node->get('field_acq_promotion_rule_id')->getString();

      // Return empty form for single auto add free gift.
      if ($promo_type != SkuManager::FREE_GIFT_SUB_TYPE_ONE_SKU) {
        return $form;
      }
    }

    $parent_sku = $this->skuManager->getParentSkuBySku($sku->getSku());

    $form['coupon'] = [
      '#type' => 'hidden',
      '#value' => $coupon,
    ];

    $form['sku'] = [
      '#type' => 'hidden',
      '#value' => $sku->getSku(),
    ];

    $form['promotion_id'] = [
      '#type' => 'hidden',
      '#value' => $promotion_id,
    ];

    $form['select'] = [
      '#type' => 'button',
      '#value' => $this->t('ADD FREE GIFT'),
      '#weight' => 100,
      '#attributes' => [
        'class' => ['select-free-gift'],
        'id' => 'add-free-gift',
        'data-variant-sku' => $sku->getSku(),
        'data-sku-type' => $sku->bundle(),
        'data-promo-type' => $promo_type,
        'data-coupon' => $coupon,
        'data-parent-sku' => $parent_sku ? $parent_sku->getSku() : $sku->getSku(),
        'data-promo-rule-id' => $promo_rule_id ?? null,
      ],
    ];

    $form['#attached']['library'][] = 'alshaya_acm_product/add_free_gift_promotions';

    // Required for common js to get applied.
    $form['#attributes']['data-sku'] = $sku->getSku();
    $form['#attributes']['class'][] = 'sku-base-form';

    $is_sku_configurable = ($sku->bundle() === 'configurable');
    if ($is_sku_configurable) {
      // @see alshaya_acm_product_acq_sku_configurable_variants_alter().
      $is_free_gift_being_processed = &drupal_static('is_free_gift_being_processed', TRUE);
      $configurables = Configurable::getSortedConfigurableAttributes($sku);

      $form['selected_variant_sku'] = [
        '#type' => 'hidden',
      ];

      $form['configurations'] = [
        '#type' => 'container',
        '#tree' => TRUE,
      ];

      $attributes = array_keys($configurables);
      $children = $this->promotionsManager->getAvailableFreeGiftChildren($sku);

      $combinations = [];

      foreach ($children as $child) {
        $form['#attached']['drupalSettings']['productInfo'][$sku->getSku()]['variants'][$child->getSku()] = $this->skuInfoHelper->getVariantInfo($child, 'pdp', $sku);

        $child_attributes = array_column($child->get('attributes')->getValue(), 'value', 'key');
        foreach ($attributes as $attribute) {
          $combinations['by_sku'][$child->getSku()][$attribute] = $child_attributes[$attribute];
          $combinations['attribute_sku'][$attribute][$child_attributes[$attribute]][] = $child->getSku();
        }
      }

      $this->formHelper->updateCombinations($combinations, $attributes);

      $swatch_processed = FALSE;
      foreach ($configurables as $configurable) {
        $attribute_code = $configurable['code'];

        $options = [];

        foreach ($configurable['values'] as $value) {
          if (isset($combinations['attribute_sku'][$attribute_code][$value['value_id']])) {
            $options[$value['value_id']] = $value['label'];
          }
        }

        // Do not build select list if it has no options.
        if (empty($options)) {
          continue;
        }

        $form['configurations'][$attribute_code] = [
          '#type' => 'select',
          '#title' => $configurable['label'],
          '#options' => $options,
          '#required' => TRUE,
          '#code' => $attribute_code,
        ];

        $this->formHelper->alterConfigurableFormItem($sku, $form['configurations'][$attribute_code], $swatch_processed);

        $combinations['combinations'] = [];

        foreach ($combinations['by_sku'] as $options) {
          $combinations['combinations'] = NestedArray::mergeDeepArray([$combinations['combinations'], $this->skuManager->getCombinationArray($options)], TRUE);
        }
      }

      $form['#attached']['library'][] = 'alshaya_acm_product/product_detail';
      $form['#attached']['drupalSettings']['configurableCombinations'][$sku->getSku()]['bySku'] = $combinations['by_sku'];
      $form['#attached']['drupalSettings']['configurableCombinations'][$sku->getSku()]['byAttribute'] = $combinations['by_attribute'];
      $form['#attached']['drupalSettings']['configurableCombinations'][$sku->getSku()]['combinations'] = $combinations['combinations'] ?? [];
      if ($is_sku_configurable) {
        $form['#attached']['drupalSettings']['configurableCombinations'][$sku->getSku()]['configurables'] = $configurables;
      }

      $display_settings = $this->config('alshaya_acm_product.display_settings');
      $form['#attached']['drupalSettings']['show_configurable_boxes_after'] = $display_settings->get('show_configurable_boxes_after');
      $is_free_gift_being_processed = FALSE;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Do nothing.
  }

}
