<?php

namespace Drupal\alshaya_acm_promotion\Form;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\alshaya_acm_product\Service\AddToCartFormHelper;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_promotion\AlshayaPromotionsManager;
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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm_promotion.manager'),
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('alshaya_acm_product.add_to_cart_form_helper')
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
   */
  public function __construct(AlshayaPromotionsManager $promotions_manager,
                              SkuManager $sku_manager,
                              AddToCartFormHelper $add_to_cart_form_helper) {
    $this->promotionsManager = $promotions_manager;
    $this->skuManager = $sku_manager;
    $this->formHelper = $add_to_cart_form_helper;
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

    if (!($sku instanceof SKUInterface)) {
      return $form;
    }

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
      '#ajax' => [
        'url' => Url::fromRoute('alshaya_acm_promotion.select_free_gift'),
      ],
      '#weight' => 100,
      '#attributes' => [
        'class' => ['select-free-gift'],
      ],
      '#access' => !empty($coupon) && !empty($promotion_id),
    ];

    // Required for common js to get applied.
    $form['#attributes']['data-sku'] = $sku->getSku();
    $form['#attributes']['class'][] = 'sku-base-form';

    if ($sku->bundle() == 'configurable') {
      $form['select']['#attributes']['disabled'] = 'disabled';
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

        $form['configurations'][$attribute_code] = [
          '#type' => 'select',
          '#title' => $configurable['label'],
          '#options' => $options,
          '#required' => TRUE,
          '#code' => $attribute_code,
        ];

        $this->formHelper->alterConfigurableFormItem($sku, $form['configurations'][$attribute_code], $swatch_processed);
      }

      $form['#attached']['library'][] = 'alshaya_acm_promotion/free_gift';
      $form['#attached']['drupalSettings']['configurableCombinations'][$sku->getSku()]['bySku'] = $combinations['bySku'];
      $form['#attached']['drupalSettings']['configurableCombinations'][$sku->getSku()]['byAttribute'] = $combinations['by_attribute'];

      $display_settings = $this->config('alshaya_acm_product.display_settings');
      $form['#attached']['drupalSettings']['show_configurable_boxes_after'] = $display_settings->get('show_configurable_boxes_after');
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
    parent::submitForm($form, $form_state);
  }

}
