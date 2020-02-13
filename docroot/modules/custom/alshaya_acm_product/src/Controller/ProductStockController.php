<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\Event\AddToCartFormSubmitEvent;
use Drupal\alshaya_acm\CartHelper;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\Renderer;
use http\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Ajax\SettingsCommand;

/**
 * Class ProductStockController.
 *
 * @package Drupal\alshaya_acm_product\Controller
 */
class ProductStockController extends ControllerBase {

  /**
   * Renderer service object.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Cart Helper.
   *
   * @var \Drupal\alshaya_acm\CartHelper
   */
  protected $cartHelper;

  /**
   * Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('alshaya_acm.cart_helper'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * CustomerController constructor.
   *
   * @param \Drupal\Core\Render\Renderer $renderer
   *   Renderer service object.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\alshaya_acm\CartHelper $cart_helper
   *   Cart Helper.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   Event Dispatcher.
   */
  public function __construct(Renderer $renderer,
                              SkuManager $sku_manager,
                              CartHelper $cart_helper,
                              EventDispatcherInterface $eventDispatcher) {
    $this->renderer = $renderer;
    $this->skuManager = $sku_manager;
    $this->cartHelper = $cart_helper;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * Ajax submit - page callback for add to cart.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   SKU entity.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   AJAX Response with all commands.
   */
  public function addToCartSubmit(Request $request, EntityInterface $entity) {
    if (!($entity instanceof SKU)) {
      throw new InvalidArgumentException();
    }

    $data = $request->request->all();

    // Sanity check.
    if (empty($data)) {
      throw new NotFoundHttpException();
    }

    $return = new AjaxResponse();
    try {
      $response = $this->cartHelper->addItemToCart(
        $entity,
        $data
      );

      if ($response === TRUE) {
        $return->addCommand(new InvokeCommand('.sku-base-form[data-sku="' . $entity->getSku() . '"]', 'trigger', ['product-add-to-cart-success']));
        $this->moduleHandler()->alter('alshaya_acm_product_add_to_cart_submit_ajax_response', $return, $entity, $data);

        // Use the variant sku for event if configurable product added.
        $variant_sku = $data['selected_variant_sku'] ?? '';
        if (!empty($variant_sku)) {
          $variant = SKU::loadFromSku($variant_sku);
        }

        // Instantiate and Dispatch add_to_cart_submit event.
        $this->eventDispatcher->dispatch(
          AddToCartFormSubmitEvent::EVENT_NAME,
          new AddToCartFormSubmitEvent($entity, $return, $variant ?? NULL)
        );

        // Get product qty in cart for variant.
        $current_variant_in_cart_qty = $this->skuManager->getCartItemQtyLimit($variant_sku);
        // Get max sale qty for the variant being added.
        $plugin = $entity->getPluginInstance();
        $max_sale_qty = $plugin->getMaxSaleQty($variant_sku);

        // If items in cart is more than max_sale_qty then
        // disable ADD TO BAG and quantity dropdown.
        if ($max_sale_qty !== NULL && $current_variant_in_cart_qty >= $max_sale_qty) {
          $orderLimitData['#attached']['drupalSettings']['productInfo'][$data['selected_parent_sku']]['variants'][$variant_sku]['orderLimitMsg'] = $this->skuManager->maxSaleQtyMessage($max_sale_qty, TRUE);
          $orderLimitData['#attached']['drupalSettings']['productInfo'][$data['selected_parent_sku']]['variants'][$variant_sku]['orderLimitExceeded'] = TRUE;
          $return->addCommand(new SettingsCommand($orderLimitData, TRUE));
          $return->addCommand(new InvokeCommand(NULL, 'LimitExceededInCart', [$data['selected_parent_sku'], $variant_sku]));
        }
      }
      else {
        $class = '.error-container-' . strtolower(Html::cleanCssIdentifier($entity->getSku()));
        $error = [
          '#message' => $response,
          '#theme' => 'global_error',
        ];
        $return->addCommand(new HtmlCommand($class, $error));
        $return->addCommand(new InvokeCommand('.sku-base-form[data-sku="' . $entity->getSku() . '"]', 'trigger', ['product-add-to-cart-failed']));
      }
    }
    catch (\Exception $e) {
      $this->getLogger('AddToCartSubmit')->warning('Failed while trying to add to cart: @message', [
        '@message' => $e->getMessage(),
      ]);
    }

    $return->addCommand(new InvokeCommand(NULL, 'hideLoader'));

    return $return;
  }

}
