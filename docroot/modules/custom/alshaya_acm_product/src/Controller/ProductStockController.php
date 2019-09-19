<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\Event\AddToCartFormSubmitEvent;
use Drupal\alshaya_acm\CartHelper;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\Renderer;
use http\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

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
    $return = new AjaxResponse();
    $response = $this->cartHelper->addItemToCart(
      $entity,
      $data
    );

    if ($response === TRUE) {
      $this->moduleHandler()->alter('alshaya_acm_product_add_to_cart_submit_ajax_response', $return, $entity, $data);
    }
    else {
      $class = '.error-container-' . strtolower(Html::cleanCssIdentifier($entity->getSku()));
      $return->addCommand(new HtmlCommand($class, $response));
    }

    // Instantiate and Dispatch add_to_cart_submit event.
    $this->eventDispatcher->dispatch(
      AddToCartFormSubmitEvent::EVENT_NAME,
      new AddToCartFormSubmitEvent($entity, $return)
    );

    return $return;
  }

}
