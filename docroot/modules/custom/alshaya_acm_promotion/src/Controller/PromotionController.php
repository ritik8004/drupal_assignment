<?php

namespace Drupal\alshaya_acm_promotion\Controller;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_commerce\UpdateCartErrorEvent;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\alshaya_acm\CartData;
use Drupal\alshaya_acm_product\SkuImagesHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_promotion\AlshayaPromotionsManager;
use Drupal\alshaya_acm_promotion\AlshayaPromoLabelManager;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\node\NodeInterface;
use http\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\alshaya_acm_product\AlshayaRequestContextManager;

/**
 * Class Promotion Controller.
 */
class PromotionController extends ControllerBase {

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
   * Promotions Manager.
   *
   * @var \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager
   */
  protected $promotionsManager;

  /**
   * Cart Storage.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * Alshaya Promotions Label Manager.
   *
   * @var \Drupal\alshaya_acm_promotion\AlshayaPromoLabelManager
   */
  protected $promoLabelManager;

  /**
   * Sku images helper.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesHelper
   */
  protected $skuImagesHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('alshaya_acm_product.sku_images_manager'),
      $container->get('alshaya_acm_promotion.manager'),
      $container->get('acq_cart.cart_storage'),
      $container->get('event_dispatcher'),
      $container->get('alshaya_acm_promotion.label_manager'),
      $container->get('alshaya_acm_product.sku_images_helper')
    );
  }

  /**
   * PromotionController constructor.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity Repository.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $images_manager
   *   Images Manager.
   * @param \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager $promotions_manager
   *   Promotions Manager.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   Cart Storage.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event Dispatcher.
   * @param \Drupal\alshaya_acm_promotion\AlshayaPromoLabelManager $alshayaPromoLabelManager
   *   Alshaya Promo Label Manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesHelper $images_helper
   *   Sku images helper.
   */
  public function __construct(EntityRepositoryInterface $entity_repository,
                              SkuManager $sku_manager,
                              SkuImagesManager $images_manager,
                              AlshayaPromotionsManager $promotions_manager,
                              CartStorageInterface $cart_storage,
                              EventDispatcherInterface $dispatcher,
                              AlshayaPromoLabelManager $alshayaPromoLabelManager,
                              SkuImagesHelper $images_helper) {
    $this->entityRepository = $entity_repository;
    $this->skuManager = $sku_manager;
    $this->imagesManager = $images_manager;
    $this->promotionsManager = $promotions_manager;
    $this->cartStorage = $cart_storage;
    $this->dispatcher = $dispatcher;
    $this->promoLabelManager = $alshayaPromoLabelManager;
    $this->skuImagesHelper = $images_helper;
  }

  /**
   * Page title callback for displaying free gifts list.
   */
  public function listFreeGiftsTitle(NodeInterface $node) {
    $node = $this->entityRepository->getTranslationFromContext($node);
    return $node->get('field_acq_promotion_label')->getString();
  }

  /**
   * Page callback for displaying free gifts list.
   */
  public function listFreeGiftsBody(Request $request, NodeInterface $node) {
    $build = [];

    $build['#cache']['tags'] = $node->getCacheTags();
    $node = $this->entityRepository->getTranslationFromContext($node);

    $free_gifts = $this->promotionsManager->getFreeGiftSkuEntitiesByPromotionId($node->id());

    $items = [];

    /** @var \Drupal\acq_sku\Entity\SKU $free_gift */
    foreach ($free_gifts as $free_gift) {
      $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'], $free_gift->getCacheTags());

      $item = [];
      $item['#title']['#markup'] = $free_gift->label();
      $item['#url'] = Url::fromRoute(
        'alshaya_acm_promotion.free_gift_modal',
        ['acq_sku' => $free_gift->id()],
        [
          'query' => [
            'promotion_id' => $node->id(),
            'coupon' => $request->query->get('coupon'),
            'back' => 1,
          ],
        ]
      );

      $item['#theme'] = 'free_gift_item';
      $parent_sku = $this->skuManager->getParentSkuBySku($free_gift->getSku());

      switch ($free_gift->bundle()) {
        case 'simple':
          if (!$this->skuManager->getStockQuantity($free_gift)) {
            continue 2;
          }

          $sku_media = $this->imagesManager->getFirstImage($free_gift, 'plp', TRUE);

          // Getting the promo rule id.
          $promo_rule_id = $node->get('field_acq_promotion_rule_id')->getString();

          if ($sku_media) {
            $item['#image'] = $this->skuImagesHelper->getSkuImage(
              $sku_media,
              SkuImagesHelper::STYLE_PRODUCT_TEASER
            );
          }

          $item['#select_link'] = Link::createFromRoute(
            $this->t('select'),
            'alshaya_acm_promotion.select_free_gift',
            [],
            [
              'attributes' => [
                'class' => ['select-free-gift'],
                'id' => 'select-add-free-gift',
                'data-variant-sku' => $free_gift->getSku(),
                'data-sku-type' => $free_gift->bundle(),
                'data-coupon' => $request->query->get('coupon'),
                'data-parent-sku' => $parent_sku ? $parent_sku->getSku() : $free_gift->getSku(),
                'data-promo-rule-id' => $promo_rule_id ?? NULL,
              ],
            ]
          );

          break;

        case 'configurable':
          if (!$this->promotionsManager->getAvailableFreeGiftChildren($free_gift)) {
            continue 2;
          }
          $sku_for_gallery = $this->promotionsManager->getSkuForFreeGiftGallery($free_gift);
          $sku_media = $this->imagesManager->getFirstImage($sku_for_gallery, 'plp', TRUE);
          if ($sku_media) {
            $item['#image'] = $this->skuImagesHelper->getSkuImage(
              $sku_media,
              SkuImagesHelper::STYLE_PRODUCT_TEASER
            );
          }

          break;

        default:
          // We support only specific types for now.
          continue 2;
      }

      $items[] = $item;
    }

    $build['items'] = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];

    $build['#attached']['library'][] = 'alshaya_acm_product/add_free_gift_promotions';

    if ($request->query->get('replace')) {
      $response = new AjaxResponse();
      $response->addCommand(new HtmlCommand('#drupal-modal', $build));
      return $response;
    }

    return $build;
  }

  /**
   * Page callback to select free gift and apply coupon.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Response.
   */
  public function selectFreeGift(Request $request) {
    $coupon = $request->request->get('coupon') ?? $request->query->get('coupon');
    $sku = SKU::loadFromSku($request->request->get('sku') ?? $request->query->get('sku'));
    $promotion_id = $request->request->get('promotion_id') ?? $request->query->get('promotion_id');

    if (empty($coupon) || empty($promotion_id) || !($sku instanceof SKUInterface)) {
      throw new InvalidArgumentException();
    }

    $promotion = $this->entityTypeManager()->getStorage('node')->load($promotion_id);
    if (!($promotion instanceof NodeInterface)) {
      throw new InvalidArgumentException();
    }

    $cart = $this->cartStorage->getCart(FALSE);
    if (empty($cart)) {
      throw new InvalidArgumentException();
    }

    try {
      $cart->setCoupon($coupon);
      $updated_cart = $this->cartStorage->updateCart(FALSE);

      $response_message = $updated_cart->get('response_message');

      // We will have type of message like error or success. key '0' contains
      // the response message string while key '1' contains the response
      // message context/type like success or coupon.
      if (!empty($response_message[1])) {
        // If its success.
        if ($response_message[1] == 'success') {
          $this->messenger()->addMessage($response_message[0]);

          if ($sku->bundle() == 'configurable') {
            $tree = Configurable::deriveProductTree($sku);
            $options = [];
            foreach ($request->request->get('configurations') as $id => $value) {
              $options[] = [
                'option_id' => $tree['configurables'][$id]['attribute_id'],
                'option_value' => $value,
              ];
            }

            // Allow other modules to update the options info sent to ACM.
            // Duplicate here for now, done in Drupal\alshaya_acm\CartHelper.
            $this->moduleHandler()->alter('acq_sku_configurable_cart_options', $options, $sku);

            $updated_cart->addRawItemToCart([
              'name' => $sku->label(),
              'sku' => $sku->getSKU(),
              'qty' => 1,
              'product_option' => [
                'extension_attributes' => [
                  'configurable_item_options' => $options,
                ],
              ],
              'extension_attributes' => [
                'promo_rule_id' => $promotion->get('field_acq_promotion_rule_id')->getString(),
              ],
            ]);
          }
          else {
            $updated_cart->addRawItemToCart([
              'name' => $sku->label(),
              'sku' => $sku->getSku(),
              'qty' => 1,
              'extension_attributes' => [
                'promo_rule_id' => $promotion->get('field_acq_promotion_rule_id')->getString(),
              ],
            ]);
          }

          $updated_cart->setExtension('do_direct_call', 1);
          $this->cartStorage->updateCart(FALSE);
        }
        elseif ($response_message[1] == 'error_coupon') {
          $this->messenger()->addError($response_message[0]);
          $this->cartStorage->restoreCart($cart->id());
        }
      }
    }
    catch (\Exception $e) {
      $this->messenger()->addError($e->getMessage());

      // Dispatch event so action can be taken.
      $event = new UpdateCartErrorEvent($e);
      $this->dispatcher->dispatch(UpdateCartErrorEvent::SUBMIT, $event);
    }

    $response = new AjaxResponse();
    $response->addCommand(new RedirectCommand(Url::fromRoute('acq_cart.cart')->toString()));
    return $response;
  }

  /**
   * Get Promotions dynamic label for specific product.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   * @param string $sku
   *   SKU.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax command to update promo label.
   */
  public function getPromotionDynamicLabelForProduct(Request $request, string $sku) {
    $sku = SKU::loadFromSku($sku);

    // Add cache metadata.
    $cache_array = [
      'tags' => ['node_type:acq_promotion'],
      'contexts' => [
        'session',
        'languages',
        'url.query_args:context',
      ],
    ];

    try {
      if (!($sku instanceof SKUInterface)) {
        throw new InvalidArgumentException();
      }

      $get = $request->query->all();
      $cart = CartData::createFromArray($get);
    }
    catch (\InvalidArgumentException) {
      $response = new CacheableJsonResponse([]);
      $response->addCacheableDependency(CacheableMetadata::createFromRenderArray(['#cache' => $cache_array]));
      return $response;
    }

    Cache::mergeTags($cache_array['tags'], $sku->getCacheTags());
    Cache::mergeTags($cache_array['tags'], $cart->getCacheTags());

    // We use app as default here as we have updated web code and APP
    // code will be updated later to pass the value all the time.
    // So if someone invokes this without the context, we use app as default.
    AlshayaRequestContextManager::updateDefaultContext('app');
    $label = $this->promoLabelManager->getSkuPromoDynamicLabel($sku);
    $response = new CacheableJsonResponse(['label' => $label]);
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray(['#cache' => $cache_array]));
    return $response;
  }

  /**
   * Get Promotions dynamic labels for both product and cart level.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   */
  public function getPromotionDynamicLabelForCart(Request $request) {
    $get = $request->query->all();
    return $this->getPromotionDynamicLabelForCartHelper($get);
  }

  /**
   * Helper function to get dynamic label data.
   */
  protected function getPromotionDynamicLabelForCartHelper($get) {
    $cache_array = [
      'tags' => ['node_type:acq_promotion'],
      'contexts' => ['url.query_args', 'languages'],
    ];

    try {
      $cart = CartData::createFromArray($get);
    }
    catch (\InvalidArgumentException) {
      $response = new CacheableJsonResponse([]);
      $response->addCacheableDependency(CacheableMetadata::createFromRenderArray(['#cache' => $cache_array]));
      return $response;
    }

    // Add cache metadata from cart.
    Cache::mergeTags($cache_array['tags'], $cart->getCacheTags());
    // We use app as default here as we have updated web code and APP
    // code will be updated later to pass the value all the time.
    // So if someone invokes this without the context, we use app as default.
    AlshayaRequestContextManager::updateDefaultContext('app');
    $productLabels = [];
    foreach ($cart->getItems() as $item) {
      $productLabels[$item['sku']]['sku'] = $item['sku'];
      $productLabels[$item['sku']]['labels'] = $this->promoLabelManager->getCurrentSkuPromos($item['entity'], 'api');
    }

    $cartLabels = [
      'qualified' => [],
      'next_eligible' => [],
      // Node objects array of the promotion discounts applicable for the cart.
      'applied_rules' => [],
      // Node objects array of the promotion discounts applied in the cart.
      'applied_rules_with_discounts' => [],
      'shipping_free' => FALSE,
    ];

    foreach ($this->promotionsManager->getCartPromotions() ?? [] as $rule_id => $promotion) {
      $description = $promotion->get('field_acq_promotion_description')->first()
        ? $promotion->get('field_acq_promotion_description')->first()->getValue()['value']
        : '';
      $cartLabels['applied_rules'][$rule_id] = [
        'label' => $promotion->get('field_acq_promotion_label')->getString(),
        'description' => $description,
      ];

      if ($promotion->get('field_alshaya_promotion_subtype')->getString() === 'free_shipping_order') {
        $cartLabels['shipping_free'] = TRUE;
      }

      $promotion_data = $this->promotionsManager->getPromotionData($promotion);
      if (!empty($promotion_data)) {
        $cartLabels['qualified'][$rule_id] = [
          'rule_id' => $rule_id,
          'type' => $promotion_data['type'],
          'label' => $promotion_data['label'],
        ];
      }
    }

    $cartLabels['applied_rules_with_discounts'] = $this->promotionsManager->getAppliedCartDiscounts();

    $applicableInactivePromotion = $this->promotionsManager->getInactiveCartPromotion();
    if ($applicableInactivePromotion instanceof NodeInterface) {
      $rule_id = $applicableInactivePromotion->get('field_acq_promotion_rule_id')->getString();
      $promotion_data = $this->promotionsManager->getPromotionData($applicableInactivePromotion, FALSE);

      if (!empty($promotion_data)) {
        $cartLabels['next_eligible'] = [
          'rule_id' => (int) $rule_id,
          'type' => $promotion_data['type'],
          'label' => $promotion_data['label'],
          'threshold_reached' => !empty($promotion_data['threshold_reached']),
          'coupon' => $promotion_data['coupon'] ?? '',
          'couponDiscount' => (int) $promotion_data['couponDiscount'] ?? 0,
        ];
      }
    }

    $response = new CacheableJsonResponse([
      'cart_labels' => $cartLabels,
      'products_labels' => $productLabels,
    ]);
    // Enures we get empty object '{}' instead of an empty array '[]'.
    $response->setEncodingOptions(JSON_FORCE_OBJECT);

    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray(['#cache' => $cache_array]));
    return $response;
  }

}
