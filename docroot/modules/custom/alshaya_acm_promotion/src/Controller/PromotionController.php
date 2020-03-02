<?php

namespace Drupal\alshaya_acm_promotion\Controller;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_commerce\UpdateCartErrorEvent;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_promotion\AlshayaPromotionsManager;
use Drupal\alshaya_acm_promotion\AlshayaPromoLabelManager;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableAjaxResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use http\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PromotionController.
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
      $container->get('alshaya_acm_promotion.label_manager')
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
   */
  public function __construct(EntityRepositoryInterface $entity_repository,
                              SkuManager $sku_manager,
                              SkuImagesManager $images_manager,
                              AlshayaPromotionsManager $promotions_manager,
                              CartStorageInterface $cart_storage,
                              EventDispatcherInterface $dispatcher,
                              AlshayaPromoLabelManager $alshayaPromoLabelManager) {
    $this->entityRepository = $entity_repository;
    $this->skuManager = $sku_manager;
    $this->imagesManager = $images_manager;
    $this->promotionsManager = $promotions_manager;
    $this->cartStorage = $cart_storage;
    $this->dispatcher = $dispatcher;
    $this->promoLabelManager = $alshayaPromoLabelManager;
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
        ['acq_sku' => $free_gift->id(), 'js' => 'nojs'],
        [
          'query' => [
            'promotion_id' => $node->id(),
            'coupon' => $request->query->get('coupon'),
            'back' => 1,
          ],
        ]
      );

      $item['#theme'] = 'free_gift_item';

      switch ($free_gift->bundle()) {
        case 'simple':
          $sku_media = $this->imagesManager->getFirstImage($free_gift, 'plp', TRUE);
          if ($sku_media) {
            $item['#image'] = $this->skuManager->getSkuImage(
              $sku_media['drupal_uri'],
              $free_gift->label(),
              'product_teaser'
            );
          }

          $item['#select_link'] = Link::createFromRoute(
            $this->t('select'),
            'alshaya_acm_promotion.select_free_gift',
            [],
            [
              'attributes' => [
                'class' => ['use-ajax', 'select-free-gift'],
              ],
              'query' => [
                'promotion_id' => $node->id(),
                'coupon' => $request->query->get('coupon'),
                'sku' => $free_gift->getSku(),
              ],
            ]
          );

          break;

        case 'configurable':
          $sku_for_gallery = $this->promotionsManager->getSkuForFreeGiftGallery($free_gift);
          $sku_media = $this->imagesManager->getFirstImage($sku_for_gallery, 'plp', TRUE);
          if ($sku_media) {
            $item['#image'] = $this->skuManager->getSkuImage(
              $sku_media['drupal_uri'],
              $free_gift->label(),
              'product_teaser'
            );
          }

          break;

        default:
          // We support only specific types for now.
          continue;
      }

      $items[] = $item;
    }

    $build['items'] = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];

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
   * Get Promotion Label.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   Product SKU to get promo label for.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax command to update promo label.
   */
  public function getPromotionDynamicLabel(SKUInterface $sku) {
    $response = new CacheableAjaxResponse();

    // Add cache metadata.
    $cache_array = [
      'tags' => ['node_type:acq_promotion'],
      'contexts' => ['session', 'cookies:Drupal_visitor_acq_cart_id'],
    ];

    $cart_id = $this->cartStorage->getCartId(FALSE);
    if (!empty($cart_id)) {
      $cache_array['tags'][] = 'cart:' . $cart_id;
    }

    $label = $this->promoLabelManager->getSkuPromoDynamicLabel($sku);
    if (!empty($label)) {
      $response = $this->promoLabelManager->prepareResponse($label, $sku->id(), $response);
      $response->addCommand(new InvokeCommand('.promotions-dynamic-label', 'trigger', ['dynamic:promotion:label:ajax:complete']));
    }

    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray(['#cache' => $cache_array]));
    return $response;
  }

}
