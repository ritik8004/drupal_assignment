<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ProductStockController.
 *
 * @package Drupal\alshaya_acm_product\Controller
 */
class ProductStockController extends ControllerBase {

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

    // @TODO: We should avoid this AJAX call as well.
    if (!alshaya_acm_product_is_buyable($sku_entity)) {
      // @TODO: This is to avoid adding out of stock classes. Needs refactoring.
      $build['max_quantity'] = 100;
      $build['html'] = '';
    }
    elseif ($max_quantity = alshaya_acm_is_product_in_stock($sku_entity)) {
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
    // Adding max-age.
    $expiration_time = _alshaya_acm_get_stock_expiration_time($sku_entity);
    $max_age = $expiration_time - \Drupal::time()->getRequestTime();
    $cacheMeta->setCacheMaxAge($max_age);
    $response->addCacheableDependency($cacheMeta);

    return $response;
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
    elseif ($max_quantity = alshaya_acm_is_product_in_stock($sku_entity)) {
      $build['max_quantity'] = $max_quantity;

      $form = $this->fetchAddCartForm($sku_entity);
      $rendered_form = \Drupal::service('renderer')->renderRoot($form);

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
      // Adding max-age.
      $expiration_time = _alshaya_acm_get_stock_expiration_time($sku_entity);
      $max_age = $expiration_time - \Drupal::time()->getRequestTime();
      $cacheMeta->setCacheMaxAge($max_age);
      $response->addCacheableDependency($cacheMeta);

      return $response;
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
    $plugin_manager = \Drupal::service('plugin.manager.sku');
    $plugin_definition = $plugin_manager->pluginFromSKU($sku_entity);

    $class = $plugin_definition['class'];
    $plugin = new $class();

    $form['add_to_cart'] = \Drupal::service('acq_sku.form_builder')->getForm($plugin, $sku_entity);
    $form['add_to_cart']['#weight'] = 50;

    return $form;
  }

}
