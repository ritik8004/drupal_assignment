<?php

namespace Drupal\alshaya_acm_promotion;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\Core\Cache\CacheableAjaxResponse;

/**
 * Class AlshayaPromoLabelManager.
 *
 * @package Drupal\alshaya_acm_promotion
 */
class AlshayaPromoLabelManager {

  use StringTranslationTrait;

  const DYNAMIC_PROMOTION_ELIGIBLE_ACTIONS = [
    'buy_x_get_y_cheapest_free',
    'groupn',
    'groupn_fixdisc',
    'groupn_disc',
  ];
  const ALSHAYA_PROMOTIONS_STATIC_PROMO = 0;
  const ALSHAYA_PROMOTIONS_DYNAMIC_PROMO = 1;

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
   * @param \Drupal\acq_cart\CartStorageInterface $cartManager
   *   Cart Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config Factory.
   * @param \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager $promotions_manager
   *   Promotions Manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer.
   */
  public function __construct(SkuManager $sku_manager,
                              SkuImagesManager $images_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              EntityRepositoryInterface $entity_repository,
                              CartStorageInterface $cartManager,
                              ConfigFactoryInterface $configFactory,
                              AlshayaPromotionsManager $promotions_manager,
                              RendererInterface $renderer) {
    $this->skuManager = $sku_manager;
    $this->imagesManager = $images_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
    $this->cartManager = $cartManager;
    $this->configFactory = $configFactory;
    $this->promoManager = $promotions_manager;
    $this->renderer = $renderer;
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

    if (!empty($this->isDynamicLabelsEnabled())
      && $this->isPromotionLabelDynamic($promotion)) {
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

    $promotion_subtype = $promotion->get('field_acq_promotion_action')->getString();
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
          $amount = strip_tags(alshaya_acm_price_format($discount_amount));

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
          $amount = strip_tags(alshaya_acm_price_format($discount_amount));

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
      }

      // Default label if promotion is applied.
      if (isset($z) && ($z < 1)) {
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

  /**
   * Get promotion label data for product detail (full/modal).
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Product.
   * @param string $view_mode
   *   View mode.
   *
   * @return array
   *   Promotion render data (generic/dynamic/free).
   */
  public function getPromotionLabelForProductDetail(SKU $sku, string $view_mode) {
    // Get promotions for the product.
    $promotion_nodes = $this->skuManager->getSkuPromotions($sku, ['cart']);
    $promotions = $this->skuManager->preparePromotionsDisplay($sku, $promotion_nodes, 'links', ['cart'], 'full');

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

    if (!empty($generic_promotions)) {
      $build['promotions'] = [
        '#markup' => implode('</br>', $generic_promotions),
      ];
    }

    // Process free gift promotions only for full view mode.
    if (in_array($view_mode, ['full']) && !empty($free_gift_promotions)) {
      // For free gift promotions, the promo needs to be rendered in a
      // different way.
      foreach ($free_gift_promotions as $promotion_id => $free_gift_promotion) {
        $free_skus = $this->promoManager->getFreeGiftSkuEntitiesByPromotionId((int) $promotion_id);

        // No free gift available for the promotion, return early.
        if (empty($free_skus)) {
          continue;
        }

        $build['free_gift_promotions'] = $this->getFreeGiftDisplay($promotion_id, $free_gift_promotion, $free_skus);

        // We support displaying only one free gift promotion for now.
        break;
      }
    }

    // If promotions are eligible for dynamic promo label.
    if (!isset($build['free_gift_promotions'])
      && $this->isDynamicLabelsEnabled()
      && $this->checkPromoLabelType($promotion_nodes) === self::ALSHAYA_PROMOTIONS_DYNAMIC_PROMO) {

      switch ($view_mode) {
        case 'full':
          // Add a flag to update promo label dynamically.
          $build['promotions']['#attached']['library'][] = 'alshaya_acm_promotion/label_manager';
          $build['promotions']['#attached']['drupalSettings']['alshayaAcmPromotions'][$sku->id()] = TRUE;

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
          $promoDynamicLabels = $this->getSkuPromoDynamicLabel($sku, $promotion_nodes);
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

    return $build;
  }

  /**
   * Get free gift promotion data to display.
   *
   * @param int|string $promotion_id
   *   Promotion ID.
   * @param array $free_gift_promotion
   *   Free gift promotion processed data.
   * @param \Drupal\acq_sku\Entity\SKU[] $free_skus
   *   Array of Free SKUs.
   *
   * @return array
   *   Render array.
   */
  protected function getFreeGiftDisplay($promotion_id, array $free_gift_promotion, array $free_skus) {
    // More than one available, show list modal.
    if (count($free_skus) > 1) {
      $link = Link::createFromRoute(
        $free_gift_promotion['text'],
        'alshaya_acm_promotion.free_gifts_list',
        [
          'node' => $promotion_id,
          'js' => 'nojs',
        ],
        [
          'attributes' => [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => '{"width":"auto"}',
          ],
        ]
      )->toString();

      $message = $this->t('One item of choice from @link with this product', [
        '@link' => $link,
      ]);

      foreach ($free_skus as $free_sku) {
        if ($this->imagesManager->hasMedia($free_sku)) {
          $free_sku_media = $this->imagesManager->getFirstImage($free_sku);
          $free_sku_image = $this->skuManager->getSkuImage($free_sku_media['drupal_uri'], $free_sku->label(), '192x168');
          break;
        }
      }

      $return = [
        '#theme' => 'free_gift_promotion_list',
        '#message' => [
          '#type' => 'markup',
          '#markup' => $message,
        ],
        '#title' => [
          '#type' => 'markup',
          '#markup' => $this->t('Free Gift'),
        ],
        '#image' => $free_sku_image ?? NULL,
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

      $free_sku_title = $free_sku_image = [
        '#type' => 'link',
        '#url' => Url::fromRoute('alshaya_acm_promotion.free_gift_modal',
          [
            'acq_sku' => $free_sku_entity->id(),
            'js' => 'nojs',
          ]),
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
        '#free_sku_title' => $free_sku_title,
        '#promo_title' => $free_gift_promotion['text'],
        '#promo_code' => $free_gift_promotion['coupon_code'],
      ];

      $free_sku_media = $this->imagesManager->getFirstImage($free_sku_entity);

      // If free gift sku has no media, then we check from the default
      // image from the configuration.
      if (empty($free_sku_media) && !empty($default_image = $this->imagesManager->getProductDefaultImage())) {
        $free_sku_media = [
          'label' => $free_sku_entity->label(),
          'file' => $default_image,
          'drupal_uri' => $default_image->getFileUri(),
        ];
      }

      if ($free_sku_media) {
        $free_sku_image['#title'] = $this->skuManager->getSkuImage($free_sku_media['drupal_uri'], $free_sku_entity->label(), '192x168');
        $return['#sku_image'] = $this->renderer->renderPlain($free_sku_image);
      }
    }

    return $return;
  }

}
