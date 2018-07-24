<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Form\AcqSkuFormBuilder;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\Renderer;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
   * Current request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * SKU Plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * ACQ SKU Form builder.
   *
   * @var \Drupal\acq_sku\Form\AcqSkuFormBuilder
   */
  protected $skuFormBuilder;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('renderer'),
      $container->get('plugin.manager.sku'),
      $container->get('acq_sku.form_builder'),
      $container->get('module_handler')
    );
  }

  /**
   * CustomerController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   Current request object.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   Renderer service object.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   SKU Plugin manager.
   * @param \Drupal\acq_sku\Form\AcqSkuFormBuilder $sku_form_builder
   *   ACQ SKU Form builder.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(Request $current_request,
                              Renderer $renderer,
                              PluginManagerInterface $plugin_manager,
                              AcqSkuFormBuilder $sku_form_builder,
                              ModuleHandlerInterface $module_handler) {
    $this->currentRequest = $current_request;
    $this->renderer = $renderer;
    $this->pluginManager = $plugin_manager;
    $this->skuFormBuilder = $sku_form_builder;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Controller to check for Product's availability.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity related to SKU for which we checking the stock.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   Response object returning availability.
   */
  public function checkStock(EntityInterface $entity) {
    if ($entity instanceof Node) {
      $sku = $entity->get('field_skus')->first()->getString();
      $sku_entity = SKU::loadFromSku($sku);
    }
    elseif ($entity instanceof SKU) {
      $sku_entity = $entity;
    }
    // For someone trying to trick the system with different entity type.
    // This will be good as we will be using this with GET now.
    else {
      throw new NotFoundHttpException();
    }

    $build = [];

    // We wont have AJAX call if product not buyable but kept to be double sure.
    if (!alshaya_acm_product_is_buyable($sku_entity)) {
      $build['max_quantity'] = 100;
      $build['html'] = '';
    }
    elseif ($max_quantity = alshaya_acm_get_stock_from_sku($sku_entity)) {
      $build['max_quantity'] = $max_quantity;
      $build['html'] = '';
    }
    else {
      $build['html'] = '<span class="out-of-stock">' . $this->t('out of stock')->render() . '</span>';
      $build['max_quantity'] = 0;
    }

    $response = new CacheableJsonResponse($build, 200);
    $response->addCacheableDependency($sku_entity);
    $response->addcacheabledependency(['url.path']);

    // Adding cacheability metadata, so whenever, cache invalidates, this
    // url's cached response also gets invalidate.
    $cacheMeta = new CacheableMetadata();

    // Adding cache tags.
    $cacheMeta->addCacheTags(['acq_sku:' . $sku_entity->id()]);
    $response->addCacheableDependency($cacheMeta);

    return $response;
  }

  /**
   * Controller to get for cart form for product/sku.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   * @param string $view_mode
   *   View mode of the SKU entity for which the form is being pulled up.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity related to SKU for which we checking the stock.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Response object returning cart form or out of stock message.
   */
  public function getCartForm(Request $request, $view_mode, EntityInterface $entity) {
    $referer = $request->headers->get('referer');

    // Set params from referer to current request.
    // For swatches we set color param on PDP url.
    // This is not forwarded to get cart form request. If we add
    // it in hook_node_view it is cached and then first value is
    // passed to all the requests.
    // We use referer which always have proper value to solve.
    if ($referer) {
      $parsed = parse_url($referer);
      $referrer_query = $parsed['query'] ?? '';

      if ($referrer_query) {
        $referrer_query_array = [];
        parse_str($referrer_query, $referrer_query_array);

        foreach ($referrer_query_array as $key => $value) {
          $request->query->set($key, $value);
        }
      }
    }

    $response = new AjaxResponse();

    $wrapper = 'article[data-skuid="' . $entity->id() . '"]:visible';

    $buyable = alshaya_acm_product_is_buyable($entity);

    if ($buyable) {
      $stock = alshaya_acm_get_stock_from_sku($entity);
      if ($stock) {
        $form = $this->fetchAddCartForm($entity, $view_mode);
        $response->addCommand(new HtmlCommand($wrapper, $form));
      }
      else {
        $html = [
          '#markup' => '<span class="out-of-stock">' . $this->t('out of stock')->render() . '</span>',
        ];
        $response->addCommand(new HtmlCommand($wrapper, $html));
      }
      $this->moduleHandler->alter('alshaya_acm_product_ajax_cart_form', $response, $entity, $stock);
    }
    else {
      $response->addCommand(new HtmlCommand($wrapper, ''));
    }

    return $response;
  }

  /**
   * Helper function to build the add cart form.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   Sku Entity for which we building the form.
   * @param string $view_mode
   *   View mode of the SKU entity for which the form is being pulled up.
   *
   * @return mixed
   *   Add to cart form for the sku.
   */
  protected function fetchAddCartForm(SKU $sku_entity, $view_mode) {
    $plugin = $sku_entity->getPluginInstance();

    $form['add_to_cart'] = $this->skuFormBuilder->getForm($plugin, $sku_entity);
    $form['add_to_cart']['#weight'] = 50;
    $form['add_to_cart']['sku_view_mode'] = [
      '#type' => 'hidden',
      '#value' => $view_mode,
      '#attributes' => [
        'name' => 'sku_view_mode',
      ],
    ];

    return $form;
  }

}
