<?php

namespace Drupal\alshaya_seo;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\node\Entity\Node;

/**
 * Class AlshayaGtmManager.
 *
 * @package Drupal\alshaya_seo
 */
class AlshayaGtmManager {

  /**
   * The current route matcher service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Mapping between Drupal routes & GTM Page names.
   *
   * @var array
   */
  public static $routeGTMMapping = [
    'view.search.page' => 'Search Results Page',
  ];

  /**
   * AlshayaGtmManager constructor.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   Current route matcher service.
   */
  public function __construct(CurrentRouteMatch $currentRouteMatch) {
    $this->currentRouteMatch = $currentRouteMatch;
  }

  /**
   * Helper function to prepare attributes for a product.
   *
   * @param \Drupal\node\Entity\Node $product
   *   Node object for which we want to get the attributes prepared.
   *
   * @return array
   *   Array of attributes to be exposed to GTM.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \InvalidArgumentException
   *   Attributes array.
   */
  public function fetchProductGtmAttributes(Node $product) {
    $sku = SKU::loadFromSku($product->get('field_skus')->first()->getString());

    $attributes = [];

    $attributes['gtm-type'] = 'gtm-product-link';
    $attributes['gtm-name'] = $sku->label();
    $attributes['gtm-main-sku'] = $sku->getSku();

    $price = $sku->get('price')->getString() ?: $sku->get('final_price')->getString();
    $attributes['gtm-price'] = number_format($price, 3);

    // @TODO: Is this site name?
    $attributes['gtm-brand'] = $sku->get('attributes')->getString();

    $attributes['gtm-category'] = $product->get('field_category')->getString();

    // @TODO: We should find a way to get this function work for other places.
    $attributes['gtm-product-sku'] = '';

    // @TODO: This is getting static, need to find a way or discuss.
    $attributes['gtm-dimension1'] = $sku->get('attr_size')->getString();
    $attributes['gtm-dimension2'] = $sku->get('attr_color')->getString();
    $attributes['gtm-dimension3'] = 'Baby Clothing';
    $attributes['gtm-stock'] = alshaya_acm_is_product_in_stock($sku->getSku()) ? 'in stock' : 'out of stock';
    $attributes['gtm-sku-type'] = $sku->bundle();
    $attributes['gtm-container'] = $this->convertCurrentRouteToGtmPageName($this->getGtmContainer());
    $attributes['gtm-product-type'] = 'simple/configurable';

    // @TODO: We should find a way to get this function work for other places.
    $attributes['gtm-cart-value'] = '';

    return $attributes;
  }

  /**
   * Helper function to convert attributes array to string.
   *
   * @param array $attributes
   *   Attributes array.
   *
   * @return string
   *   Attributes string to be displayed directly in twig.
   */
  public function convertAttrsToString(array $attributes) {
    $attributes_string = ' ';

    foreach ($attributes as $key => $value) {
      $attributes_string .= $key;
      $attributes_string .= '=';
      $attributes_string .= '"' . $value . '"';
      $attributes_string .= ' ';
    }

    return $attributes_string;
  }

  /**
   * Helper function to fetch the current url & return corresponding page-type.
   */
  protected function getGtmContainer() {
    $currentRoute = &drupal_static(__FUNCTION__);

    if (!isset($currentRoute)) {
      $currentRoute = $this->currentRouteMatch->getRouteName();
    }

    return $currentRoute;
  }

  /**
   * Helper function to convert route name into actual GTM page name.
   *
   * @param string $currentRouteName
   *   Route name for the current page.
   *
   * @return string
   *   GTM page name of the current route.
   */
  public function convertCurrentRouteToGtmPageName($currentRouteName) {
    return self::$routeGTMMapping[$currentRouteName];
  }

}
