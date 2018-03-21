<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Form\AcqSkuFormBuilder;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Renderer;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('renderer'),
      $container->get('plugin.manager.sku'),
      $container->get('acq_sku.form_builder')
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
   */
  public function __construct(Request $current_request,
                              Renderer $renderer,
                              PluginManagerInterface $plugin_manager,
                              AcqSkuFormBuilder $sku_form_builder) {
    $this->currentRequest = $current_request;
    $this->renderer = $renderer;
    $this->pluginManager = $plugin_manager;
    $this->skuFormBuilder = $sku_form_builder;
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
    elseif ($max_quantity = alshaya_acm_get_product_stock($sku_entity)) {
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
   * Controller to get for cart form for product/sku on modal.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity related to SKU for which we checking the stock.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse|\Drupal\Core\Cache\CacheableJsonResponse
   *   Response object returning cart form or out of stock message.
   */
  public function getModalCartForm(EntityInterface $entity) {
    return $this->getCartForm($entity);
  }

  /**
   * Controller to get for cart form for product/sku.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity related to SKU for which we checking the stock.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse|\Drupal\Core\Cache\CacheableJsonResponse
   *   Response object returning cart form or out of stock message.
   */
  public function getCartForm(EntityInterface $entity) {
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

    if (!alshaya_acm_product_is_buyable($sku_entity)) {
      // @TODO: This is to avoid adding out of stock classes. Needs refactoring.
      $build['max_quantity'] = 100;
      $build['html'] = '';

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
    elseif ($max_quantity = alshaya_acm_get_product_stock($sku_entity)) {
      $build['max_quantity'] = $max_quantity;

      $form = $this->fetchAddCartForm($sku_entity);
      $rendered_form = $this->renderer->renderRoot($form);

      // Get the data from BubbleMetaData.
      $data = BubbleableMetadata::createFromRenderArray($form);

      // Retrieve the attachments from the $data.
      $attachments = $data->getAttachments();

      // Generate the settings to be sent back with the ajax response.
      $settings = '';
      if (!empty($attachments['drupalSettings'])) {
        $settings .= '<script type="text/javascript">jQuery.extend(drupalSettings, ';
        $settings .= Json::encode($attachments['drupalSettings']);
        $settings .= ');</script>';
      }

      $build['html'] = $rendered_form . $settings;

      return new JsonResponse($build);
    }
    else {
      // We process add to cart form if we have stock or request is POST.
      // Stock might become zero after user has loaded the page.
      // In such cases we just reload the page to show updated status.
      if ($this->currentRequest->getMethod() == 'POST') {
        $response = new AjaxResponse();
        $response->addCommand(new RedirectCommand($this->currentRequest->server->get('HTTP_REFERER')));
        return $response;
      }
      else {
        $build['max_quantity'] = 0;
        $build['html'] = '<span class="out-of-stock">' . $this->t('out of stock')->render() . '</span>';

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
    }
  }

  /**
   * Helper function to build the add cart form.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   Sku Entity for which we building the form.
   *
   * @return mixed
   *   Add to cart form for the sku.
   */
  protected function fetchAddCartForm(SKU $sku_entity) {
    $plugin_definition = $this->pluginManager->pluginFromSKU($sku_entity);

    $class = $plugin_definition['class'];
    $plugin = new $class();

    $form['add_to_cart'] = $this->skuFormBuilder->getForm($plugin, $sku_entity);
    $form['add_to_cart']['#weight'] = 50;

    return $form;
  }

}
