<?php

namespace Drupal\alshaya_acm_promotion\Controller;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_commerce\UpdateCartErrorEvent;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityRepositoryInterface;
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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('alshaya_acm_product.sku_images_manager'),
      $container->get('acq_cart.cart_storage'),
      $container->get('event_dispatcher')
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
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   Cart Storage.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event Dispatcher.
   */
  public function __construct(EntityRepositoryInterface $entity_repository,
                              SkuManager $sku_manager,
                              SkuImagesManager $images_manager,
                              CartStorageInterface $cart_storage,
                              EventDispatcherInterface $dispatcher) {
    $this->entityRepository = $entity_repository;
    $this->skuManager = $sku_manager;
    $this->imagesManager = $images_manager;
    $this->cartStorage = $cart_storage;
    $this->dispatcher = $dispatcher;
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

    $free_gifts = [];
    foreach ($node->get('field_free_gift_skus')->getValue() as $free_gift) {
      $sku = SKU::loadFromSku($free_gift['value']);

      if ($sku instanceof SKUInterface) {
        $free_gifts[] = $sku;
        $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'], $sku->getCacheTags());
      }
    }

    $items = [];

    /** @var \Drupal\acq_sku\Entity\SKU $free_gift */
    foreach ($free_gifts as $free_gift) {
      $item = [];

      $item['#title']['#markup'] = $free_gift->label();
      $item['#url'] = Url::fromRoute(
        'alshaya_acm_product.sku_modal',
        ['acq_sku' => $free_gift->id(), 'js' => 'nojs'],
        [
          'query' => [
            'promotion_id' => $node->id(),
            'coupon' => $request->query->get('coupon'),
          ],
        ]
      );

      switch ($free_gift->bundle()) {
        case 'simple':
          $item['#theme'] = 'free_gift_item';

          $sku_media = $this->imagesManager->getFirstImage($free_gift);
          if ($sku_media) {
            $item['#image'] = $this->skuManager->getSkuImage(
              $sku_media['drupal_uri'],
              $free_gift->label(),
              'product_teaser'
            );
          }

          break;

        case 'configurable':
          // @TODO: CORE-10288.
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
    $coupon = $request->request->get('coupon') ?? '';
    $sku = SKU::loadFromSku($request->request->get('sku') ?? '');

    if (empty($coupon) || !($sku instanceof SKUInterface)) {
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

          $updated_cart->addItemToCart($sku->getSku(), 1);
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

}
