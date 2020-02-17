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
use Drupal\alshaya_acm_product\Service\ProductOrderLimit;

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
   * Product Order Limit service object.
   *
   * @var \Drupal\alshaya_acm_product\Service\ProductOrderLimit
   */
  protected $productOrderLimit;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('alshaya_acm.cart_helper'),
      $container->get('event_dispatcher'),
      $container->get('alshaya_acm_product.product_order_limit')
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
   * @param \Drupal\alshaya_acm_product\Service\ProductOrderLimit $product_order_limit
   *   Product Order Limit.
   */
  public function __construct(Renderer $renderer,
                              SkuManager $sku_manager,
                              CartHelper $cart_helper,
                              EventDispatcherInterface $eventDispatcher,
                              ProductOrderLimit $product_order_limit) {
    $this->renderer = $renderer;
    $this->skuManager = $sku_manager;
    $this->cartHelper = $cart_helper;
    $this->eventDispatcher = $eventDispatcher;
    $this->productOrderLimit = $product_order_limit;
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

        $orderLimitData = [];
        $viewModeKey = isset($data['product_view_mode']) && ($data['product_view_mode'] !== 'full') ?
          $data['product_view_mode'] : 'productInfo';
        $parent_sku = isset($data['selected_parent_sku']) ? $data['selected_parent_sku'] : $variant_sku;
        // Check if max sale qty limit is set for parent.
        $max_sale_qty = $this->productOrderLimit->getParentMaxSaleQty($variant);

        if (!empty($max_sale_qty)) {
          // Get max sale qty variables.
          $max_sale_qty_variables = $this->productOrderLimit->getMaxSaleQtyVariables($variant, $max_sale_qty);
          $orderLimitData = [
            $viewModeKey => [
              $parent_sku => [
                'orderLimitMsg' => $max_sale_qty_variables['orderLimitMsg'],
                'orderLimitExceeded' => $max_sale_qty_variables['orderLimitExceeded'],
              ],
            ],
          ];
        }
        else {
          // If max sale qty for parent is not set then get for the variant.
          $plugin = $variant->getPluginInstance();
          $max_sale_qty = $plugin->getMaxSaleQty($variant_sku);
          if (!empty($max_sale_qty)) {
            // Get max sale qty variables.
            $max_sale_qty_variables = $this->productOrderLimit->getMaxSaleQtyVariables($variant, $max_sale_qty);

            $orderLimitData = [
              $viewModeKey => [
                $parent_sku => [
                  'variants' => [
                    $variant_sku => [
                      'orderLimitMsg' => $max_sale_qty_variables['orderLimitMsg'],
                      'orderLimitExceeded' => $max_sale_qty_variables['orderLimitExceeded'],
                    ],
                  ],
                ],
              ],
            ];
          }
        }
        if (!empty($orderLimitData)) {
          $return->addCommand(new SettingsCommand($orderLimitData, TRUE));
          $return->addCommand(new InvokeCommand(NULL, 'LimitExceededInCart', [$parent_sku, $variant_sku]));
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
