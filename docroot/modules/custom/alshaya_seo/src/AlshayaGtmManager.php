<?php

namespace Drupal\alshaya_seo;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

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
  const ROUTE_GTM_MAPPING = [
    'view.search.page' => 'search result page',
    'alshaya_master.home' => 'home page',
    'entity.taxonomy_term.canonical:acq_product_category' => 'product listing page',
    'entity.node.canonical:acq_product' => 'product detail page',
    'entity.node.canonical:department_page' => 'department page',
    'system.404' => 'page not found',
    '' => 'contact us page',
    '' => 'about us page',
    '' => 'store finder',
    '' => 'click and collect page',
    '' => 'static page',
    '' => 'confirmation page',
    '' => 'payment page',
    '' => 'summary page',
    '' => 'delivery page',
    '' => 'about you page',
    'user.login' => 'login page',
    'acq_cart.cart' => 'cart page',
  ];

  /**
   * Mapping between Drupal routes & GTM list types.
   */
  const LIST_GTM_MAPPING = [
    'view.search.page' => 'Search Results Page',
    'entity.taxonomy_term.canonical:acq_product_category' => 'PLP',
    'entity.node.canonical:acq_product' => 'PDP',
    'acq_cart.cart' => 'CartPage',
    'alshaya_master.home' => 'home page',
  ];

  /**
   * GTM gobal variables that need to be available on all pages.
   */
  const GTM_GLOBALS = [
    'language',
    'pageType',
    'country',
    'currency',
  ];

  /**
   * Html attributes mapped with GTM tags.
   */
  const GTM_KEYS = [
    'name' => 'gtm-name',
    'id' => 'gtm-main-sku',
    'price' => 'gtm-price',
    'brand' => 'gtm-brand',
    'category' => 'gtm-category',
    'variant' => '',
    'dimension1' => 'gtm-dimension1',
    'dimension2' => 'gtm-dimension2',
    'dimension3' => 'gtm-dimension3',
    'dimension4' => 'gtm-stock',
    'dimension5' => 'gtm-sku-type',
    'metric1' => '',
  ];

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The cart storage service.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * AlshayaGtmManager constructor.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   Current route matcher service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config Factory service.
   * @param \Drupal\acq_cart\CartStorageInterface $cartStorage
   *   Cart Storage service.
   */
  public function __construct(CurrentRouteMatch $currentRouteMatch,
                              ConfigFactoryInterface $configFactory,
                              CartStorageInterface $cartStorage) {
    $this->currentRouteMatch = $currentRouteMatch;
    $this->configFactory = $configFactory;
    $this->cartStorage = $cartStorage;
  }

  /**
   * Helper function to prepare attributes for a product.
   *
   * @param \Drupal\node\Entity\Node $product
   *   Node object for which we want to get the attributes prepared.
   * @param string $view_mode
   *   View mode in which we trying to render the product.
   *
   * @return array
   *   Array of attributes to be exposed to GTM.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \InvalidArgumentException
   */
  public function fetchProductGtmAttributes(Node $product, $view_mode) {
    $skuId = $product->get('field_skus')->first()->getString();
    $skuAttributes = $this->fetchSkuAtttributes($skuId);

    $attributes['gtm-type'] = 'gtm-product-link';
    $attributes['gtm-category'] = $this->fetchProductCategories($product);
    $attributes['gtm-container'] = $this->convertCurrentRouteToGtmPageName($this->getGtmContainer());
    $attributes['gtm-view-mode'] = $view_mode;
    $attributes['gtm-cart-value'] = '';

    $attributes = array_merge($attributes, $skuAttributes);

    return $attributes;
  }

  /**
   * Helper function to fetch attributes on SKU.
   *
   * @param string $skuId
   *   Identifier of the product variant on SKU entity.
   *
   * @return array
   *   Attributes on sku to be exposed to GTM.
   *
   * @throws \InvalidArgumentException
   */
  public function fetchSkuAtttributes($skuId) {
    $sku = SKU::loadFromSku($skuId);

    $attributes = [];

    $attributes['gtm-name'] = $sku->label();
    $attributes['gtm-main-sku'] = $sku->getSku();

    $price = $sku->get('price')->getString() ?: $sku->get('final_price')->getString();
    $attributes['gtm-price'] = number_format($price, 3);

    // @TODO: Is this site name?
    $attributes['gtm-brand'] = $sku->get('attr_brand')->getString();

    // @TODO: We should find a way to get this function work for other places.
    $attributes['gtm-product-sku'] = '';

    // @TODO: This is getting static, need to find a way or discuss.
    // Dimension1 & 2 corrrespond to size & color.
    // Should stay blank unless added to cart.
    $attributes['gtm-dimension1'] = $sku->get('attr_size')->getString();
    $attributes['gtm-dimension2'] = '';

    $attributes['gtm-dimension3'] = 'Baby Clothing';
    $attributes['gtm-stock'] = alshaya_acm_is_product_in_stock($sku->getSku()) ? 'in stock' : 'out of stock';
    $attributes['gtm-sku-type'] = $sku->bundle();

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
  public function getGtmContainer() {
    $currentRoute = &drupal_static(__FUNCTION__);

    if (!isset($currentRoute)) {
      $currentRoute['route_name'] = $this->currentRouteMatch->getRouteName();
      $currentRoute['route_params'] = $this->currentRouteMatch->getParameters()->all();
      $currentRoute['pathinfo'] = $this->currentRouteMatch->getRouteObject();
    }

    return $currentRoute;
  }

  /**
   * Helper function to convert route name into actual GTM page name.
   *
   * @param array $currentRoute
   *   Current route details.
   *
   * @return string
   *   GTM page name of the current route.
   */
  public function convertCurrentRouteToGtmPageName(array $currentRoute) {
    $gtmPageType = &drupal_static(__FUNCTION__);

    if (!isset($gtmPageType)) {
      $routeIdentifier = $currentRoute['route_name'];
      // Return GTM page-type based on our current route.
      switch ($currentRoute['route_name']) {
        case 'entity.node.canonical':
          if (isset($currentRoute['route_params']['node'])) {
            /** @var \Drupal\node\Entity\Node $node */
            $node = $currentRoute['route_params']['node'];
            $routeIdentifier .= ':' . $node->bundle();
          }
          break;

        case 'entity.taxonomy_term.canonical':
          if (isset($currentRoute['route_params']['taxonomy_term'])) {
            /** @var \Drupal\taxonomy\Entity\Term $term */
            $term = $currentRoute['route_params']['taxonomy_term'];
            $routeIdentifier .= ':' . $term->getVocabularyId();
          }
          break;
      }
      $gtmRoutes = self::ROUTE_GTM_MAPPING;

      if (array_key_exists($routeIdentifier, $gtmRoutes)) {
        $gtmPageType = self::ROUTE_GTM_MAPPING[$routeIdentifier];
      }

      if (($currentRoute['route_name'] === 'acq_checkout.form') &&
        ($currentRoute['route_params']['step'] === 'login')) {
        $gtmPageType = 'cart-checkout-login';
      }
    }

    return $gtmPageType;
  }

  /**
   * Helper function to convert route name into actual GTM list name.
   *
   * @param array $currentRoute
   *   Current route details.
   *
   * @return string
   *   GTM page name of the current route.
   */
  public function convertCurrentRouteToGtmListName(array $currentRoute) {
    $gtmListName = &drupal_static(__FUNCTION__);

    if (!isset($gtmListName)) {
      $routeIdentifier = $currentRoute['route_name'];
      // Return GTM page-type based on our current route.
      switch ($currentRoute['route_name']) {
        case 'entity.node.canonical':
          if (isset($currentRoute['route_params']['node'])) {
            /** @var \Drupal\node\Entity\Node $node */
            $node = $currentRoute['route_params']['node'];
            $routeIdentifier .= ':' . $node->bundle();
          }
          break;

        case 'entity.taxonomy_term.canonical':
          if (isset($currentRoute['route_params']['taxonomy_term'])) {
            /** @var \Drupal\taxonomy\Entity\Term $term */
            $term = $currentRoute['route_params']['taxonomy_term'];
            $routeIdentifier .= ':' . $term->getVocabularyId();
          }
          break;
      }

      $gtmRoutes = self::LIST_GTM_MAPPING;

      if (array_key_exists($routeIdentifier, $gtmRoutes)) {
        $gtmListName = self::LIST_GTM_MAPPING[$routeIdentifier];
      }
    }

    return $gtmListName;
  }

  /**
   * Helper function to process Product attributes for PDP page.
   *
   * @param array $attributes
   *   Product Attributes.
   *
   * @return array
   *   Array of processed attributes.
   */
  public function processAttributesForPdp(array $attributes) {
    $processed_attributes['ecommerce'] = [];
    $processed_attributes['ecommerce']['currencyCode'] = $this->configFactory->get('acq_commerce.currency')->get('currency_code');
    $processed_attributes['ecommerce']['detail']['actionField'] = [
      'list' => $this->convertCurrentRouteToGtmPageName($this->getGtmContainer()),
    ];

    $processed_attributes['ecommerce']['detail']['products'][] = $this->convertHtmlAttributesToDatalayer($attributes);
    return $processed_attributes;
  }

  /**
   * Converts attributes calculated for HTML to Datalayer attributes.
   */
  public function convertHtmlAttributesToDatalayer($attributes) {
    $product_details = [];

    foreach (self::GTM_KEYS as $datalayer_key => $attribute_key) {
      // Keep attribute values as empty till we have info about them.
      if ($attribute_key === '') {
        $product_details[$datalayer_key] = '';
        continue;
      }

      $product_details[$datalayer_key] = $attributes[$attribute_key];
    }

    return $product_details;
  }

  /**
   * Helper function to fetch current cart & its items.
   *
   * @throws \InvalidArgumentException
   */
  public function fetchCartItemAttributes() {
    // Include product utility file to use helper functions.
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
    $cart = $this->cartStorage->getCart();
    $cartItems = $cart->get('items');
    $skuIds = array_keys($cartItems);
    $attributes = [];

    foreach ($cartItems as $cartItem) {
      $skuId = $cartItem['sku'];
      $attributes[$skuId] = $this->fetchSkuAtttributes($skuId);
      // Fetch product for this sku to get the category.
      $productNode = alshaya_acm_product_get_display_node($skuId);
      $attributes[$skuId]['gtm-category'] = $this->fetchProductCategories($productNode);
    }

    return $attributes;
  }

  /**
   * Helper function to fetch & concatenate product categories.
   *
   * @param \Drupal\node\Entity\Node $productNode
   *   Product node.
   *
   * @return string
   *   Concatenated product categories.
   *
   * @throws \InvalidArgumentException
   */
  public function fetchProductCategories(Node $productNode) {
    $categories = $productNode->get('field_category')->getValue();
    $terms = [];

    if (count($categories)) {
      foreach ($categories as $category) {
        $term = Term::load($category['target_id']);
        $terms[] = $term->getName();
      }
    }

    return implode('/', $terms);
  }

}
