<?php

namespace Drupal\alshaya_acm_promotion;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm\CartData;
use Drupal\alshaya_acm_product\SkuImagesHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\alshaya_acm_product\AlshayaRequestContextManager;

/**
 * Class Alshaya Promo Label Manager.
 *
 * @package Drupal\alshaya_acm_promotion
 */
class AlshayaPromoLabelManager {

  use StringTranslationTrait;

  public const DYNAMIC_PROMOTION_ELIGIBLE_ACTIONS = [
    'buy_x_get_y_cheapest_free',
    'groupn',
    'groupn_fixdisc',
    'groupn_disc',
    'ampromo_cart',
    'ampromo_items',
  ];
  public const ALSHAYA_PROMOTIONS_STATIC_PROMO = 0;
  public const ALSHAYA_PROMOTIONS_DYNAMIC_PROMO = 1;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity Repository.
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
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Promotions Manager.
   *
   * @var \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager
   */
  protected $promoManager;

  /**
   * Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * Alshaya Request Context Manager.
   *
   * @var \Drupal\alshaya_acm_product\AlshayaRequestContextManager
   */
  protected $requestContextManager;

  /**
   * Sku images helper.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesHelper
   */
  protected $skuImagesHelper;

  /**
   * AlshayaPromoLabelManager constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $images_manager
   *   Images Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity Repository.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config Factory.
   * @param \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager $promotions_manager
   *   Promotions Manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager.
   * @param \Drupal\alshaya_acm_product\AlshayaRequestContextManager $alshayaRequestContextManager
   *   Alshaya Request Context Manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesHelper $sku_images_helper
   *   Sku images helper.
   */
  public function __construct(SkuManager $sku_manager,
                              SkuImagesManager $images_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              EntityRepositoryInterface $entity_repository,
                              ConfigFactoryInterface $configFactory,
                              AlshayaPromotionsManager $promotions_manager,
                              RendererInterface $renderer,
                              LanguageManagerInterface $language_manager,
                              AlshayaRequestContextManager $alshayaRequestContextManager,
                              SkuImagesHelper $sku_images_helper) {
    $this->skuManager = $sku_manager;
    $this->imagesManager = $images_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
    $this->configFactory = $configFactory;
    $this->promoManager = $promotions_manager;
    $this->renderer = $renderer;
    $this->languageManager = $language_manager;
    $this->requestContextManager = $alshayaRequestContextManager;
    $this->skuImagesHelper = $sku_images_helper;
  }

  /**
   * Get Node Storage.
   *
   * @return \Drupal\node\NodeStorageInterface
   *   Node Storage.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getNodeStorage() {
    // Avoid loading objects during constructor.
    return $this->entityTypeManager->getStorage('node');
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
        $promotionNode = $this->getNodeStorage()->load($promotionNode);
      }

      if (!($promotionNode instanceof NodeInterface)) {
        continue;
      }

      if ($this->isPromotionLabelDynamic($promotionNode)) {
        $eligiblePromotions[] = $promotionNode;
      }
    }

    return $eligiblePromotions;
  }

  /**
   * Checks if promotion node has dynamic label or not.
   *
   * @param \Drupal\node\NodeInterface $promotionNode
   *   Promotion Node.
   *
   * @return bool
   *   Promotion label dynamic or not.
   */
  private function isPromotionLabelDynamic(NodeInterface $promotionNode) {
    $dynamic = FALSE;

    $promotion_action = $promotionNode->get('field_acq_promotion_action')->getString();
    if (in_array($promotion_action, self::DYNAMIC_PROMOTION_ELIGIBLE_ACTIONS)) {
      $dynamic = TRUE;
    }

    return $dynamic;
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
   *
   * @return string
   *   Dynamic Promotion Label or NULL.
   */
  public function getSkuPromoDynamicLabel(SKU $sku) {
    $promos = $this->getCurrentSkuPromos($sku, 'links');
    return is_array($promos) ? implode('<br>', $promos) : '';
  }

  /**
   * Fetch current SKU Dynamic Promos.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Product SKU.
   * @param string $view_mode
   *   Links or default.
   *
   * @return array
   *   List of promotions.
   */
  public function getCurrentSkuPromos(SKU $sku, $view_mode) {
    $promos = [];
    $context = $this->requestContextManager->getContext();

    $promotion_nodes = $this->skuManager->getSkuPromotions($sku, ['cart'], $context);

    foreach ($promotion_nodes as $promotion_node) {
      if (is_numeric($promotion_node)) {
        $promotion_node = $this->getNodeStorage()->load($promotion_node);
      }

      if (!($promotion_node instanceof NodeInterface)) {
        continue;
      }

      // Get promotion in SKU language.
      $promotion_node = $this->entityRepository->getTranslationFromContext(
        $promotion_node,
        $sku->language()->getId()
      );

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
        case 'api':
          if (!empty($promotionLabel['dynamic_label'])) {
            $promoDisplay = [
              'link' => $promotion->toUrl()->toString(TRUE)->getGeneratedUrl(),
              'promotion_nid' => (int) $promotion->get('field_acq_promotion_rule_id')->getString(),
              'label' => $promotionLabel['dynamic_label'],
            ];
          }
          break;

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

          // phpcs:ignore
          $data = unserialize($promotion->get('field_acq_promotion_data')->getString());
          foreach ($data['condition']['conditions'][0]['conditions'] ?? [] as $condition) {
            if ($condition['attribute'] === 'quote_item_qty') {
              $promoDisplay['condition_value'] = $condition['value'];
            }
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
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Product SKU.
   *
   * @return array|mixed
   *   Return original and dynamic promo label.
   */
  private function getPromotionLabel(NodeInterface $promotion, SKU $sku) {
    $label = [
      'original_label' => $promotion->get('field_acq_promotion_label')->getString(),
      'dynamic_label' => '',
    ];

    if (!empty($this->isDynamicLabelsEnabled()) && $this->isPromotionLabelDynamic($promotion)) {
      $cart = CartData::getCart();
      $cartSKUs = ($cart instanceof CartData) ? $cart->getSkus() : [];

      // If cart is not empty and has matching products.
      if (!empty($cartSKUs)) {
        $eligibleSKUs = $this->getPromoEligibleSkus($promotion, $cartSKUs);

        if (in_array($sku->getSku(), $eligibleSKUs) && !empty(array_intersect($eligibleSKUs, $cartSKUs))) {
          $this->overridePromotionLabel($label, $promotion, $eligibleSKUs);
        }
      }
    }

    return $label;
  }

  /**
   * Get Promo Eligible SKUs.
   *
   * @param \Drupal\node\NodeInterface $promotion
   *   Promotion Node.
   * @param array $cartSKUs
   *   SKUs.
   *
   * @return array
   *   List of eligible SKUs.
   */
  public function getPromoEligibleSkus(NodeInterface $promotion, array $cartSKUs) {
    $eligibleSKUs = $this->skuManager->getSkutextsForPromotion($promotion);

    // Get children of eligible parents in cart.
    $parents = $this->skuManager->getParentSkus($cartSKUs);
    $eligible_parents = array_intersect($parents, $eligibleSKUs);
    if (!empty($eligible_parents)) {
      $eligible_children = $this->skuManager->fetchChildSkuTexts($eligible_parents);
      $eligibleSKUs = array_merge($eligibleSKUs, $eligible_children);
    }

    return $eligibleSKUs;
  }

  /**
   * Overrides the promo label.
   *
   * @param string|mixed $label
   *   Default Label.
   * @param \Drupal\node\NodeInterface $promotion
   *   Promotion Node.
   * @param array $eligibleSKUs
   *   Eligible SKUs as per promotion.
   */
  private function overridePromotionLabel(&$label, NodeInterface $promotion, array $eligibleSKUs) {
    // Calculate cart quantity.
    $eligible_cart_qty = 0;
    $cart = CartData::getCart();
    $cart_items = ($cart instanceof CartData) ? $cart->getItems() : [];

    foreach ($cart_items as $item) {
      if (in_array($item['sku'], $eligibleSKUs)) {
        $quantity = $item['quantity'] ?? $item['qty'];
        $eligible_cart_qty += $quantity;
      }
    }

    $promotion_subtype = $promotion->get('field_acq_promotion_action')->getString();
    // phpcs:ignore
    $promotion_data = unserialize($promotion->get('field_acq_promotion_data')->getString());

    if (!empty($promotion_subtype) && isset($promotion_data['step']) && isset($promotion_data['discount'])) {
      // Calculate X and Y.
      $discount_step = $promotion_data['step'];
      $discount_amount = $promotion_data['discount'];
      $z = NULL;

      // Generate dynamic promotion label based on promotion subtype.
      switch ($promotion_subtype) {
        case 'buy_x_get_y_cheapest_free':
          $z = ($discount_step + $discount_amount) - $eligible_cart_qty;

          // Apply z-logic to generate label.
          if ($z >= 1) {
            $label['dynamic_label'] = $this->t('Add @z more to get FREE item', ['@z' => $z]);
          }
          break;

        case 'groupn':
          $z = $discount_step - $eligible_cart_qty;
          $amount = $this->getPlainTextAmount($discount_amount);

          if ($z >= 1) {
            $label['dynamic_label'] = $this->t(
              'Add @z more to get @step items for @amount',
              [
                '@z' => $z,
                '@step' => $discount_step,
                '@amount' => $amount,
              ]
            );
          }
          break;

        case 'groupn_fixdisc':
          $z = $discount_step - $eligible_cart_qty;
          $amount = $this->getPlainTextAmount($discount_amount);

          if ($z >= 1) {
            $label['dynamic_label'] = $this->t(
              'Add @z more to get @amount off',
              [
                '@z' => $z,
                '@amount' => $amount,
              ]
            );
          }
          break;

        case 'groupn_disc':
          $z = $discount_step - $eligible_cart_qty;

          if ($z >= 1) {
            $label['dynamic_label'] = $this->t(
              'Add @z more to get @amount% off',
              [
                '@z' => $z,
                '@amount' => $discount_amount,
              ]
            );
          }
          break;

        case 'ampromo_cart':
        case 'ampromo_items':
          foreach ($promotion_data['condition']['conditions'][0]['conditions'] ?? [] as $condition) {
            if ($condition['attribute'] === 'quote_item_qty') {
              $condition_value = $condition['value'];
              $z = $condition_value - $eligible_cart_qty;

              // Apply z-logic to generate label.
              if ($z >= 1) {
                $label['dynamic_label'] = $this->t('Add @z more to get FREE item', ['@z' => $z]);
              }
            }
          }
          break;
      }

      // Default label if promotion is applied.
      if (isset($z) && ($z < 1)) {
        $label['dynamic_label'] = $this->t('Add more and keep saving');
      }
    }
  }

  /**
   * Get promotion label data for product detail (full/modal).
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Product.
   * @param string $view_mode
   *   View mode.
   * @param string $context
   *   Context - mapp/web.
   *
   * @return array
   *   Promotion render data (generic/dynamic/free).
   */
  public function getPromotionLabelForProductDetail(SKU $sku, string $view_mode, $context = '') {
    $build = [];
    // Get promotions for the product.
    $promotion_nodes = $this->skuManager->getSkuPromotions($sku, ['cart'], $context);
    $displayMode = $view_mode === 'api' ? 'api' : 'links';
    $promotions = $this->skuManager->preparePromotionsDisplay($sku, $promotion_nodes, $displayMode, ['cart'], 'full', TRUE, $context);

    // Return early if no promotions found for product in context.
    if (empty($promotions)) {
      return [];
    }

    $free_gift_promotions = $generic_promotions = [];

    // Split promotions array into 2 parts, since we need to render
    // free gift promotions in a different way.
    foreach ($promotions as $promotion_id => $promotion) {
      if (empty($promotion['skus'])) {
        $generic_promotions[$promotion_id] = $promotion;
      }
      else {
        $free_gift_promotions[$promotion_id] = $promotion;
      }
    }

    if ($view_mode === 'api') {
      $apiPromotions = [];

      foreach ($generic_promotions as $nid => $promotion) {
        $url = Url::fromRoute('entity.node.canonical', ['node' => $nid])
          ->toString(TRUE)
          ->getGeneratedUrl();

        $url = str_replace(
          '/' . $this->languageManager->getCurrentLanguage()->getId() . '/',
          '',
          $url
        );

        $apiPromotions[] = [
          'type' => 'generic',
          'text' => $promotion['text'],
          'promo_web_url' => $url,
          'promo_node' => (int) $promotion['rule_id'],
        ];
      }

      return $apiPromotions;
    }
    elseif ($view_mode === 'free_gift') {
      if (empty($free_gift_promotions)) {
        return [];
      }

      $free_promotion = $this->getFreeGiftPromotionData($free_gift_promotions, $context);
      if (empty($free_promotion)) {
        return [];
      }

      if ($context == 'app') {
        return $free_promotion;
      }

      $coupon = $free_promotion['#promo_code'] ?? '';
      $coupon = $coupon[0]['value'] ?? $coupon;

      $free_promotion += [
        'coupon' => $coupon,
        'promo_title' => $free_promotion['#free_sku_title_raw'] ?? $this->renderer->renderPlain($free_promotion['#title']),
        'promo_web_url' => $free_promotion['#promo_url']->toString(TRUE)->getGeneratedUrl(),
      ];

      unset($free_promotion['#promo_url']);

      return $free_promotion;
    }

    if (!empty($generic_promotions)) {
      $build['promotions'] = [
        '#markup' => implode('</br>', $generic_promotions),
      ];
    }

    // Process free gift promotions only for full view mode.
    if (in_array($view_mode, ['full']) && !empty($free_gift_promotions)) {
      $build['free_gift_promotions'] = $this->getFreeGiftPromotionData($free_gift_promotions);
    }

    // If promotions are eligible for dynamic promo label.
    if ($this->isDynamicLabelsEnabled()
      && $this->checkPromoLabelType($promotion_nodes) === self::ALSHAYA_PROMOTIONS_DYNAMIC_PROMO) {

      switch ($view_mode) {
        case 'full':
          // Add a flag to update promo label dynamically.
          $build['promotions']['#attached']['library'][] = 'alshaya_acm_promotion/label_manager';

          // We process promotions for magazine v2 layout in react itself.
          if ($this->skuManager->getPdpLayout($sku, 'pdp') === SkuManager::PDP_LAYOUT_MAGAZINE_V2) {
            break;
          }

          // Add container for dynamic promotion display.
          $build['promotions']['dynamic_label'] = [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#value' => '',
          ];
          $build['promotions']['dynamic_label']['#attributes']['class'][] = 'promotions-dynamic-label sku-' . $sku->id() . ' hidden' . ' mobile-only-dynamic-promotion';
          break;

        case 'modal':
          // Directly add dynamic promotion labels.
          $promoDynamicLabels = $this->getSkuPromoDynamicLabel($sku);
          if (!empty($promoDynamicLabels)) {
            $build['promotions']['dynamic_label'] = [
              '#type' => 'html_tag',
              '#tag' => 'div',
              '#value' => $promoDynamicLabels,
              '#attributes' => [
                'class' => 'promotions-dynamic-label sku-' . $sku->id(),
              ],
            ];
          }
          break;
      }
    }

    return $build ?? [];
  }

  /**
   * Get the free gift promotion data to display.
   *
   * @param int|string $promotion_id
   *   Promotion ID.
   * @param array $free_gift_promotion
   *   Free gift promotion processed data.
   * @param \Drupal\acq_sku\Entity\SKU[] $free_skus
   *   Array of Free SKUs.
   * @param string $context
   *   Context - app/web.
   *
   * @return array
   *   Render array.
   */
  protected function getFreeGiftDisplay($promotion_id, array $free_gift_promotion, array $free_skus, $context = '') {
    $coupon = $free_gift_promotion['coupon_code'][0]['value'] ?? '';

    // Promo type 0 => All SKUs below, 1 => One of the SKUs below.
    // This is for PDP. On PDP we display the list only if there are
    // more than one free gift available.
    if (
      $free_gift_promotion['promo_type'] == SkuManager::FREE_GIFT_SUB_TYPE_ONE_SKU
      && count($free_skus) > 1
    ) {

      // This will be covered in AB-3710 for mapp.
      $url = Url::fromRoute(
        'alshaya_acm_promotion.free_gifts_list',
        [
          'node' => $promotion_id,
        ],
        [
          'query' => ['coupon' => $coupon],
          'attributes' => [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => '{"width":"auto"}',
          ],
        ]
      );

      $link = Link::fromTextAndUrl($free_gift_promotion['text'], $url)->toString();
      if ($context == 'app') {
        // Generate promo url.
        $url_obj = $url->toString(TRUE);
        $url = $url_obj->getGeneratedUrl();
      }

      $message = $this->t('One item of choice from @link with this product', [
        '@link' => $link,
      ]);

      foreach ($free_skus as $free_sku) {
        if ($this->imagesManager->hasMedia($free_sku)) {
          $free_sku_media = $this->imagesManager->getFirstImage($free_sku);
          $free_sku_image = $this->skuImagesHelper->getSkuImage(
            $free_sku_media,
            SkuImagesHelper::STYLE_PRODUCT_TEASER
          );
          $free_sku_image['#url'] = $this->skuImagesHelper->getImageStyleUrl(
            $free_sku_media,
            SkuImagesHelper::STYLE_PRODUCT_TEASER
          );
          break;
        }
      }

      if (!empty($free_gift_promotion['condition_value'])) {
        $free_gift_box_title = $this->t('Buy @condition_value to get a free gift', [
          '@condition_value' => $free_gift_promotion['condition_value'],
        ]);
      }

      $return = [
        '#theme' => 'free_gift_promotion_list',
        '#message' => [
          '#type' => 'markup',
          '#markup' => $message,
        ],
        '#title' => [
          '#type' => 'markup',
          '#markup' => $free_gift_box_title ?? $this->t('Free Gift'),
        ],
        '#promo_url' => $url,
        '#promo_code' => $coupon,
        '#free_sku_code' => $free_sku->getSku(),
        '#free_sku_type' => $free_sku->bundle(),
        '#image' => $free_sku_image ?? NULL,
        '#promo_type' => 'FREE_GIFT_SUB_TYPE_ONE_SKU',
      ];

      if (!empty($free_gift_promotion['coupon_code'])) {
        $return['#coupon'] = [
          '#type' => 'markup',
          '#markup' => $this->t('Use code <span class="coupon-code">@coupon</span> in basket', [
            '@coupon' => $free_gift_promotion['coupon_code'][0]['value'],
          ]),
        ];
      }
    }
    else {
      $free_sku_entity = reset($free_skus);

      $url = Url::fromRoute(
        'alshaya_acm_promotion.free_gift_modal',
        [
          'acq_sku' => $free_sku_entity->id(),
        ],
        [
          'query' => [
            'promotion_id' => $promotion_id,
            'coupon' => $coupon,
          ],
        ]
      );

      $free_sku_title = $free_sku_image = [
        '#type' => 'link',
        '#url' => $url,
        '#attributes' => [
          'class' => ["use-ajax"],
          'data-dialog-type' => "modal",
          'data-dialog-options' => '{"width":"auto"}',
        ],
      ];

      $free_sku_title['#title'] = $free_sku_entity->get('name')->getString();
      $free_sku_title = $this->renderer->renderPlain($free_sku_title);

      // Get sku title & image.
      $return = [
        '#theme' => 'free_gift_promotions',
        '#free_sku_entity_id' => $free_sku_entity->id(),
        '#free_sku_code' => $free_sku_entity->getSku(),
        '#free_sku_type' => $free_sku_entity->bundle(),
        '#free_sku_title' => $free_sku_title,
        '#free_sku_title_raw' => $free_sku_entity->get('name')->getString(),
        '#promo_url' => $url,
        '#promo_title' => $free_gift_promotion['text'],
        '#promo_code' => $free_gift_promotion['coupon_code'],
        '#promo_type' => 'FREE_GIFT_SUB_TYPE_ALL_SKUS',
      ];

      $free_sku_media = $this->imagesManager->getFirstImage($free_sku_entity);

      // If free gift sku has no media, then we check for the default image
      // from the configuration.
      if (empty($free_sku_media) && !empty($default_image = $this->imagesManager->getProductDefaultImage())) {
        $free_sku_media = [
          'label' => $free_sku_entity->label(),
          'file' => $default_image,
          'drupal_uri' => $default_image->getFileUri(),
          'pims_image' => [
            'styles' => [
              SkuImagesHelper::STYLE_PRODUCT_TEASER => $default_image->getFileUri(),
            ],
          ],
        ];
        $free_sku_image_url = file_create_url($default_image->getFileUri());
      }

      if ($free_sku_media) {
        $free_sku_image['#title'] = $this->skuImagesHelper->getSkuImage(
          $free_sku_media,
          SkuImagesHelper::STYLE_PRODUCT_TEASER
        );
        $return['#sku_image'] = $this->renderer->renderPlain($free_sku_image);
      }

      // Context to expose free gift data in mobile api.
      if ($context == 'app') {

        // Use the default image if set already, else use the free-gift media.
        $free_sku_image_url ??= $this->skuImagesHelper->getImageStyleUrl(
          $free_sku_media,
          SkuImagesHelper::STYLE_PRODUCT_TEASER);

        $return = [
          'free_sku_code' => $free_sku_entity->getSku(),
          'free_sku_title' => $free_sku_entity->get('name')->getString(),
          'promo_title' => $free_gift_promotion['text'],
          'promo_code' => $free_gift_promotion['coupon_code'],
          'sku_image' => $free_sku_image_url,
          'coupon' => $coupon,
        ];
      }
    }

    return $return;
  }

  /**
   * Processes the amount through template and returns in plain text.
   *
   * @param string $amount
   *   The amount to process.
   *
   * @return string
   *   The amount with currency in plaintext format.
   */
  private function getPlainTextAmount(string $amount) {
    $amount = strip_tags(alshaya_acm_price_format($amount));
    // Remove extra spaces from beginning/end/middle of string which
    // appear due to removal of html tags.
    return preg_replace(['/^\s+/', '/\s+$/', '/\s+/u'], ['', '', ' '], $amount);
  }

  /**
   * Get free gift promotion data from available promotions.
   *
   * @param array $free_gift_promotions
   *   Free Gift promotions.
   * @param string $context
   *   Context - app/web.
   *
   * @return array
   *   Promotion data for first available free gift promotion.
   */
  private function getFreeGiftPromotionData(array $free_gift_promotions, $context = '') {
    // For free gift promotions, the promo needs to be rendered in a
    // different way.
    foreach ($free_gift_promotions as $promotion_id => $free_gift_promotion) {
      $free_skus = $this->promoManager->getFreeGiftSkuEntitiesByPromotionId((int) $promotion_id);

      // No free gift available for the promotion, return early.
      if (empty($free_skus)) {
        continue;
      }
      $data = $this->getFreeGiftDisplay($promotion_id, $free_gift_promotion, $free_skus, $context);

      // We support displaying only one free gift promotion for now.
      break;
    }

    return $data ?? [];
  }

}
