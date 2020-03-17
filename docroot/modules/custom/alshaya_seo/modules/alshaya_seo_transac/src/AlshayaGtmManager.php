<?php

namespace Drupal\alshaya_seo_transac;

use Drupal\alshaya_acm\CartHelper;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\alshaya_acm_product\ProductCategoryHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\node\NodeInterface;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_checkout\CheckoutOptionsManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\alshaya_stores_finder_transac\StoresFinderUtility;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityRepositoryInterface;

/**
 * Class AlshayaGtmManager.
 *
 * @package Drupal\alshaya_seo
 */
class AlshayaGtmManager {

  /**
   * Store GTM Container in static to avoid re-calculating.
   *
   * @var null
   */
  public static $gtmContainer = NULL;

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
    'entity.taxonomy_term.canonical' => 'taxonomy term',
    'entity.taxonomy_term.canonical:acq_product_category' => 'product listing page',
    'entity.node.canonical:acq_product' => 'product detail page',
    'entity.node.canonical:advanced_page' => 'advanced page',
    'entity.node.canonical:department_page' => 'department page',
    'entity.node.canonical:acq_promotion' => 'promotion page',
    'entity.node.canonical:static_html' => 'static page',
    'entity.user.canonical' => 'my account page',
    'system.404' => 'page not found',
    'alshaya_user.user_register_complete' => 'register complete page',
    'acq_cart.cart' => 'cart page',
    'acq_checkout.form:login' => 'checkout login page',
    'alshaya_spc.checkout' => 'checkout click and collect page',
    'alshaya_spc.checkout' => 'checkout delivery page',
    'acq_checkout.form:payment' => 'checkout payment page',
    'alshaya_spc.checkout.confirmation' => 'purchase confirmation page',
    'view.stores_finder.page_2' => 'store finder',
    'view.stores_finder.page_1' => 'store finder',
    'entity.webform.canonical:alshaya_contact' => 'contact us',
    'user.pass' => 'user password page',
    'change_pwd_page.change_password_form' => 'user change password page',
    'user.page' => 'user page',
    'user.reset' => 'user reset page',
    'user.reset.form' => 'user reset page',
  ];

  /**
   * Mapping between Drupal routes & GTM list types.
   */
  const LIST_GTM_MAPPING = [
    'view.search.page' => 'Search Results Page',
    'entity.taxonomy_term.canonical:acq_product_category' => 'PLP',
    'entity.node.canonical:acq_product' => 'PDP',
    'entity.node.canonical:acq_promotion' => 'Promotion',
    'acq_cart.cart' => 'CartPage',
    'alshaya_master.home' => 'HP-ProductCarrousel',
    'entity.node.canonical:department_page' => 'DPT-ProductCarrousel',
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
    'variant' => 'gtm-product-sku',
    'dimension6' => 'gtm-dimension6',
    'dimension2' => 'gtm-sku-type',
    'dimension1' => 'gtm-dimension1',
    'dimension4' => 'gtm-dimension4',
    'dimension5' => 'gtm-dimension5',
    'dimension7' => 'gtm-dimension7',
    'dimension8' => 'gtm-dimension8',
    'dimension3' => 'gtm-dimension3',
    'metric1' => 'gtm-metric1',
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
   * Cart Helper service object.
   *
   * @var \Drupal\alshaya_acm\CartHelper
   */
  protected $cartHelper;

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The Entity Type Manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The checkout options Manager service.
   *
   * @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager
   */
  protected $checkoutOptionsManager;

  /**
   * Store Finder service.
   *
   * @var \Drupal\alshaya_stores_finder_transac\StoresFinderUtility
   */
  protected $storeFinder;

  /**
   * Langauge Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Cache data service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Address Book Manager service.
   *
   * @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager
   */
  private $addressBookManager;

  /**
   * Sku Manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * SKU Images Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImagesManager;

  /**
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Orders Manager.
   *
   * @var \Drupal\alshaya_acm_customer\OrdersManager
   */
  protected $ordersManager;

  /**
   * Entity Repository service object.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Product Category Helper.
   *
   * @var \Drupal\alshaya_acm_product\ProductCategoryHelper
   */
  protected $productCategoryHelper;

  /**
   * AlshayaGtmManager constructor.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   Current route matcher service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config Factory service.
   * @param \Drupal\acq_cart\CartStorageInterface $cartStorage
   *   Private temp store service.
   * @param \Drupal\alshaya_acm\CartHelper $cartHelper
   *   Cart Helper service object.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current User service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request stack service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Manager service.
   * @param \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkoutOptionsManager
   *   Checkout Options Manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language Manager service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache data service.
   * @param \Drupal\Core\Database\Connection $database
   *   Database connection service.
   * @param \Drupal\alshaya_addressbook\AlshayaAddressBookManager $addressBookManager
   *   Address Book Manager service.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku Manager service.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU Images Manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Drupal\alshaya_acm_customer\OrdersManager $orders_manager
   *   Orders Manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   Entity Repository object.
   * @param \Drupal\alshaya_acm_product\ProductCategoryHelper $productCategoryHelper
   *   Product Category Helper.
   */
  public function __construct(CurrentRouteMatch $currentRouteMatch,
                              ConfigFactoryInterface $configFactory,
                              CartStorageInterface $cartStorage,
                              CartHelper $cartHelper,
                              AccountProxyInterface $currentUser,
                              RequestStack $requestStack,
                              EntityTypeManagerInterface $entityTypeManager,
                              CheckoutOptionsManager $checkoutOptionsManager,
                              LanguageManagerInterface $languageManager,
                              CacheBackendInterface $cache,
                              Connection $database,
                              AlshayaAddressBookManager $addressBookManager,
                              SkuManager $skuManager,
                              SkuImagesManager $sku_images_manager,
                              ModuleHandlerInterface $module_handler,
                              OrdersManager $orders_manager,
                              EntityRepositoryInterface $entityRepository,
                              ProductCategoryHelper $productCategoryHelper) {
    $this->currentRouteMatch = $currentRouteMatch;
    $this->configFactory = $configFactory;
    $this->cartStorage = $cartStorage;
    $this->cartHelper = $cartHelper;
    $this->currentUser = $currentUser;
    $this->requestStack = $requestStack;
    $this->entityTypeManager = $entityTypeManager;
    $this->checkoutOptionsManager = $checkoutOptionsManager;
    $this->languageManager = $languageManager;
    $this->cache = $cache;
    $this->database = $database;
    $this->addressBookManager = $addressBookManager;
    $this->skuManager = $skuManager;
    $this->skuImagesManager = $sku_images_manager;
    $this->moduleHandler = $module_handler;
    $this->ordersManager = $orders_manager;
    $this->entityRepository = $entityRepository;
    $this->productCategoryHelper = $productCategoryHelper;
  }

  /**
   * Setter function for Stores Finder Utility service.
   *
   * @param \Drupal\alshaya_stores_finder_transac\StoresFinderUtility $storesFinderUtility
   *   Store Finder service.
   */
  public function setStoreFinderUtility(StoresFinderUtility $storesFinderUtility) {
    // @TODO: Move this back to normal/constructor once module enabled on prod.
    $this->storeFinder = $storesFinderUtility;
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
    static $gtm_container = NULL;

    if (!isset($gtm_container)) {
      $gtm_container = $this->convertCurrentRouteToGtmPageName($this->getGtmContainer());
    }

    if ($product->hasTranslation('en')) {
      $product = $product->getTranslation('en');
    }

    $skuId = $this->skuManager->getSkuForNode($product);
    $skuAttributes = $this->fetchSkuAtttributes($skuId);

    $attributes['gtm-type'] = 'gtm-product-link';
    $attributes['gtm-category'] = implode('/', $this->fetchProductCategories($product));
    $attributes['gtm-container'] = $gtm_container;
    $attributes['gtm-view-mode'] = $view_mode;
    $attributes['gtm-cart-value'] = '';

    $attributes['gtm-main-sku'] = $this->skuManager->getSkuForNode($product);
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
    $this->moduleHandler->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
    $sku = SKU::loadFromSku($skuId);

    if (empty($sku)) {
      return [];
    }

    // We always use en for tracking.
    if ($sku->hasTranslation('en')) {
      $sku = $sku->getTranslation('en');
    }

    $attributes = [];
    $product_node = $this->skuManager->getDisplayNode($sku);
    $prices = $this->skuManager->getMinPrices($sku);
    $original_price = $prices['price'];
    $final_price = $prices['final_price'];
    $gtm_disabled_vars = $this->configFactory->get('alshaya_seo.disabled_gtm_vars')->get('disabled_vars');

    if ($sku->bundle() == 'configurable') {
      $prices = $this->skuManager->getMinPrices($sku);
      $original_price = $prices['price'];
      $final_price = $prices['final_price'];
    }

    $product_type = 'Regular Product';

    $attributes['gtm-name'] = trim($sku->label());
    $attributes['gtm-product-sku'] = $sku->getSku();
    $attributes['gtm-product-sku-class-identifier'] = strtolower(Html::cleanCssIdentifier($sku->getSku()));
    $attributes['gtm-sku-type'] = $sku->bundle();

    // Dimension1 & 2 correspond to size & color.
    // Should stay blank unless added to cart.
    if (!in_array('dimension1', $gtm_disabled_vars)) {
      $attributes['gtm-dimension1'] = $sku->get('attribute_set')->getString();
    }

    if (!in_array('dimension5', $gtm_disabled_vars)) {
      $attributes['gtm-dimension5'] = $sku->get('attr_product_collection')->getString();
    }

    if (!in_array('dimension6', $gtm_disabled_vars)) {
      $attributes['gtm-dimension6'] = $sku->get('attr_size')->getString();
    }

    if (!in_array('brand', $gtm_disabled_vars)) {
      // Site name.
      $gtm_brand = $this->configFactory->get('system.site')->get('name');
      $attributes['gtm-brand'] = $sku->get('attr_product_brand')->getString() ?: $gtm_brand;
    }

    $media = $this->skuImagesManager->getProductMedia($sku, 'pdp', TRUE);
    $attributes['gtm-dimension4'] = isset($media['media_items'], $media['media_items']['images'])
      ? count($media['media_items']['images'])
      : 'image not available';

    $attributes['gtm-price'] = (float) _alshaya_acm_format_price_with_decimal((float) $final_price, '.', '');

    if ($final_price
      && ($original_price !== $final_price)
      && ($final_price < $original_price)) {

      $product_type = 'Discounted Product';
    }

    $attributes['gtm-dimension3'] = $product_type;

    // @TODO: This is supposed to stay blank here?
    $attributes['gtm-stock'] = '';

    // Override values from parent if parent sku available.
    if ($parent_sku = alshaya_acm_product_get_parent_sku_by_sku($skuId)) {
      $attributes['gtm-sku-type'] = $parent_sku->bundle();
      if (!in_array('brand', $gtm_disabled_vars)) {
        $attributes['gtm-brand'] = $parent_sku->get('attr_product_brand')->getString() ?: $attributes['gtm-brand'];
      }

      if (!in_array('dimension5', $gtm_disabled_vars)) {
        $attributes['gtm-dimension5'] = $parent_sku->get('attr_product_collection')->getString();
      }
    }
    $this->moduleHandler->invokeAll('gtm_product_attributes_alter',
      [
        &$product_node,
        &$attributes,
      ]
    );
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
    if (empty(self::$gtmContainer)) {
      self::$gtmContainer['route_name'] = $this->currentRouteMatch->getRouteName();
      self::$gtmContainer['route_params'] = $this->currentRouteMatch->getParameters()->all();
      self::$gtmContainer['pathinfo'] = $this->currentRouteMatch->getRouteObject();
      self::$gtmContainer['query'] = $this->requestStack->getCurrentRequest()->query->all();
    }

    return self::$gtmContainer;
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
            if ($node->bundle() == 'advanced_page' && $node->get('field_use_as_department_page')->value == 1) {
              $routeIdentifier .= ':department_page';
            }
            else {
              $routeIdentifier .= ':' . $node->bundle();
            }
          }
          break;

        case 'entity.taxonomy_term.canonical':
          if (isset($currentRoute['route_params']['taxonomy_term'])) {
            /** @var \Drupal\taxonomy\Entity\Term $term */
            $term = $currentRoute['route_params']['taxonomy_term'];
            $routeIdentifier .= ':' . $term->getVocabularyId();
          }
          break;

        case 'acq_checkout.form':
          if (isset($currentRoute['route_params']['step'])) {
            if (($currentRoute['route_params']['step'] === 'delivery') &&
              isset($currentRoute['query']['method']) &&
              ($currentRoute['query']['method'] === 'cc')) {
              $routeIdentifier .= ':click_collect';
            }
            else {
              $routeIdentifier .= ':' . $currentRoute['route_params']['step'];
            }
          }
          break;

        case 'entity.webform.canonical':
          if (isset($currentRoute['route_params']['webform'])) {
            $routeIdentifier .= ':' . $currentRoute['route_params']['webform']->id();
          }
      }
      $gtmRoutes = self::ROUTE_GTM_MAPPING;

      if (array_key_exists($routeIdentifier, $gtmRoutes)) {
        $gtmPageType = self::ROUTE_GTM_MAPPING[$routeIdentifier];
      }
      else {
        $gtmPageType = 'not defined';
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
            if ($node->bundle() == 'advanced_page' && $node->get('field_use_as_department_page')->value == 1) {
              $routeIdentifier .= ':department_page';
            }
            else {
              $routeIdentifier .= ':' . $node->bundle();
            }
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
    $processed_attributes['ecommerce']['currencyCode'] = $this->getGtmCurrency();
    $gtm_disabled_vars = $this->configFactory->get('alshaya_seo.disabled_gtm_vars')->get('disabled_vars');

    // Set dimension1 & 2 to empty until product added to cart.
    if (!in_array('dimension6', $gtm_disabled_vars)) {
      $attributes['gtm-dimension6'] = '';
    }

    $attributes['gtm-product-sku'] = '';
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

      if (isset($attributes[$attribute_key])) {
        $product_details[$datalayer_key] = $attributes[$attribute_key];
      }
    }

    // If list cookie is set, set the list variable.
    if (isset($_COOKIE['product-list'])) {
      $listValues = Json::decode($_COOKIE['product-list']);
      $product_details['list'] = $listValues[$product_details['id']] ?? '';
    }
    return $product_details;
  }

  /**
   * Helper function to fetch current cart & its items.
   *
   * @throws \InvalidArgumentException
   */
  public function fetchCartItemAttributes() {
    $attributes = [];

    // Include product utility file to use helper functions.
    $this->moduleHandler->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
    $gtm_disabled_vars = $this->configFactory->get('alshaya_seo.disabled_gtm_vars')->get('disabled_vars');

    if ($cart = $this->cartStorage->getCart(FALSE)) {
      $dimension7 = '';
      $dimension8 = '';

      $cartItems = $cart->items();

      if ($this->convertCurrentRouteToGtmPageName($this->getGtmContainer()) == 'checkout click and collect page' || $this->convertCurrentRouteToGtmPageName($this->getGtmContainer()) == 'checkout delivery page') {
        // For delivery we always use step 2.
        $attributes['step'] = 2;
      }

      if ($cart_delivery_method = $cart->getShippingMethodAsString()) {
        if ($cart_delivery_method === $this->checkoutOptionsManager->getClickandColectShippingMethod()) {
          // Get store code from cart extension.
          $store_code = $cart->getExtension('store_code');

          // We should always have store but a sanity check. Additional check to
          // ensure the variable is not in list of disabled vars.
          if ((!in_array('dimension7', $gtm_disabled_vars)) &&
            ($store = $this->storeFinder->getStoreFromCode($store_code))) {
            $dimension7 = $store->label();
          }

          // We should always have store but a sanity check. Additional check to
          // ensure the variable is not in list of disabled vars.
          if ((!in_array('dimension8', $gtm_disabled_vars)) &&
            ($store = $this->storeFinder->getStoreFromCode($store_code))) {
            $storeAddress = $this->storeFinder->getStoreAddress($store, TRUE, TRUE);
            $dimension8 = $storeAddress['address_line1'] . ' ' . $storeAddress['administrative_area_display'];
          }
        }
      }

      foreach ($cartItems as $cartItem) {
        $skuId = $cartItem['sku'];
        $attributes[$skuId] = $this->fetchSkuAtttributes($skuId);

        // Fetch product for this sku to get the category.
        $productNode = $this->skuManager->getDisplayNode($skuId);

        if ($productNode instanceof NodeInterface) {
          // Get product media.
          $attributes[$skuId]['gtm-dimension4'] = count(alshaya_acm_product_get_product_media($productNode->id())) ?: 'image not available';
          $attributes[$skuId]['gtm-category'] = implode('/', $this->fetchProductCategories($productNode));
          $attributes[$skuId]['gtm-main-sku'] = $this->skuManager->getSkuForNode($productNode);
        }
        $attributes[$skuId]['quantity'] = $cartItem['qty'];

        $attributes[$skuId]['gtm-product-sku'] = $cartItem['sku'];
        $delivery_page = ($this->convertCurrentRouteToGtmPageName($this->getGtmContainer()) === 'checkout payment page');

        if (($dimension7) && ($delivery_page)) {
          $attributes[$skuId]['gtm-dimension7'] = trim($dimension7);
        }

        if (($dimension8) && ($delivery_page)) {
          $attributes[$skuId]['gtm-dimension8'] = trim($dimension8);
        }

        $this->moduleHandler->invokeAll('gtm_product_attributes_alter',
          [
            &$productNode,
            &$attributes[$skuId],
          ]
        );
      }

      $attributes['privilegeCustomer'] = !empty($cart->getExtension('loyalty_card')) ? 'Privilege Customer' : 'Regular Customer';
      $attributes['privilegesCardNumber'] = $cart->getExtension('loyalty_card');
    }

    return $attributes;
  }

  /**
   * Helper function to fetch & concatenate product categories.
   *
   * @param \Drupal\node\Entity\Node $product_node
   *   Product node.
   *
   * @return false|array
   *   Array of Product categories.
   *
   * @throws \UnexpectedValueException
   * @throws \InvalidArgumentException
   */
  public function fetchProductCategories(Node $product_node) {
    $terms = [];

    // For GTM we always want English data.
    $product_node = $this->entityRepository->getTranslationFromContext($product_node, 'en');
    $langcode = $product_node->language()->getId();

    $cid = implode(':', [
      'alshaya_product_breadcrumb_terms',
      $langcode,
      $product_node->id(),
    ]);

    if ($this->cache->get($cid)) {
      $terms = $this->cache->get($cid);
      return $terms->data;
    }

    $cache_tags = ['node:' . $product_node->id()];

    $term_list = $product_node->get('field_category')->getValue();
    foreach ($term_list as $term) {
      $cache_tags[] = 'taxonomy_term:' . $term['target_id'];
    }

    $parents = $this->productCategoryHelper->getBreadcrumbTermList($term_list);

    /** @var \Drupal\taxonomy\Entity\Term $parent */
    foreach ($parents ?? [] as $parent) {
      // For GTM we always want English data.
      $parent = $this->entityRepository->getTranslationFromContext($parent, 'en');
      $terms[$parent->id()] = trim($parent->getName());
    }

    $terms = array_reverse($terms, TRUE);
    $this->cache->set($cid, $terms, Cache::PERMANENT, $cache_tags);

    return $terms;
  }

  /**
   * Helper function to fetch order attributes.
   *
   * @params array $order
   *   Order array.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   * @throws \InvalidArgumentException
   */
  public function fetchCompletedOrderAttributes(array $order) {
    $orderItems = $order['items'];

    $dimension7 = '';
    $dimension8 = '';

    $shipping_method = $this->checkoutOptionsManager->loadShippingMethod($order['shipping']['method']);
    $gtm_disabled_vars = $this->configFactory->get('alshaya_seo.disabled_gtm_vars')->get('disabled_vars');

    $deliveryOption = 'Home Delivery';
    $deliveryType = $shipping_method->getName();

    $shipping_method_name = $shipping_method->get('field_shipping_code')->getString();
    if ($shipping_method_name === $this->checkoutOptionsManager->getClickandColectShippingMethod()) {
      $shipping_assignment = reset($order['extension']['shipping_assignments']);

      $deliveryOption = 'Click and Collect';
      $deliveryType = $shipping_assignment['shipping']['extension_attributes']['click_and_collect_type'];

      $store_code = $shipping_assignment['shipping']['extension_attributes']['store_code'];

      // We should always have store but a sanity check. Additional check to
      // ensure the variable is not in list of disabled vars.
      if ((!in_array('dimension7', $gtm_disabled_vars)) &&
        ($store = $this->storeFinder->getStoreFromCode($store_code))) {
        $dimension7 = $store->label();
      }

      // We should always have store but a sanity check. Additional check to
      // ensure the variable is not in list of disabled vars.
      if ((!in_array('dimension8', $gtm_disabled_vars)) &&
        ($store = $this->storeFinder->getStoreFromCode($store_code))) {
        $storeAddress = $this->storeFinder->getStoreAddress($store, TRUE, TRUE);
        $dimension8 = $storeAddress['address_line1'] . ' ' . $storeAddress['administrative_area_display'];
      }
    }

    $privilege_order = isset($order['extension']['loyalty_card']) ? 'Privilege Customer' : 'Regular Customer';

    foreach ($orderItems as $item) {
      $product = $this->fetchSkuAtttributes($item['sku']);
      if (isset($product['gtm-metric1']) && (!empty($product['gtm-metric1']))) {
        $product['gtm-metric1'] *= $item['ordered'];
      }
      $productNode = $this->skuManager->getDisplayNode($item['sku']);
      if ($productNode instanceof NodeInterface) {
        $product['gtm-category'] = implode('/', $this->fetchProductCategories($productNode));
        $product['gtm-main-sku'] = $this->skuManager->getSkuForNode($productNode);
      }
      $productExtras = [
        'quantity' => $item['ordered'],
      ];

      // Avoid empty valued in GTM.
      if ($dimension7) {
        $productExtras['dimension7'] = $dimension7;
      }

      // Avoid empty valued in GTM.
      if ($dimension8) {
        $productExtras['dimension8'] = $dimension8;
      }

      $products[] = array_merge($this->convertHtmlAttributesToDatalayer($product), $productExtras);
    }

    $actionData = [
      'id' => $order['increment_id'],
      'affiliation' => 'Online Store',
      'revenue' => alshaya_master_convert_amount_to_float($order['totals']['grand']),
      'tax' => alshaya_master_convert_amount_to_float($order['totals']['tax']) ?: 0.00,
      'shipping' => alshaya_master_convert_amount_to_float($order['totals']['shipping']) ?: 0.00,
      'coupon' => $order['coupon'],
    ];

    $loyalty_card = '';

    if (isset($order['extension'], $order['extension']['loyalty_card'])) {
      $loyalty_card = $order['extension']['loyalty_card'];
    }

    /** @var \Drupal\alshaya_acm_customer\OrdersManager $manager */
    $orders_count = $this->ordersManager->getOrdersCount($order['email']);

    $generalInfo = [
      'deliveryOption' => $deliveryOption,
      'deliveryType' => $deliveryType,
      'paymentOption' => $this->checkoutOptionsManager->loadPaymentMethod($order['payment']['method'], '', FALSE)->getName(),
      'discountAmount' => alshaya_master_convert_amount_to_float($order['totals']['discount']),
      'transactionID' => $order['increment_id'],
      'firstTimeTransaction' => $orders_count > 1 ? 'False' : 'True',
      'privilegesCardNumber' => $loyalty_card,
      'userEmailID' => $order['email'],
      'userName' => $order['firstname'] . ' ' . $order['lastname'],
    ];

    return [
      'general' => $generalInfo,
      'products' => $products,
      'actionField' => $actionData,
      'privilegeCustomer' => $privilege_order,
    ];
  }

  /**
   * Helper function to fetch general datalayer attributes for a page.
   */
  public function fetchGeneralPageAttributes($data_layer) {
    $data_layer_attributes = [
      'language' => $this->languageManager->getCurrentLanguage()->getId(),
      'country' => function_exists('_alshaya_country_get_site_level_country_name') ? _alshaya_country_get_site_level_country_name() : '',
      'currency' => $this->getGtmCurrency(),
    ];

    return $data_layer_attributes;
  }

  /**
   * Helper function to fetch page-specific datalayer attributes.
   */
  public function fetchPageSpecificAttributes($page_type, $current_route) {
    $page_dl_attributes = [];
    switch ($page_type) {
      case 'product detail page':
        $node = $current_route['route_params']['node'];
        if ($node->hasTranslation('en')) {
          $node = $node->getTranslation('en');
        }
        $product_sku = $this->skuManager->getSkuForNode($node);

        $sku_entity = SKU::loadFromSku($product_sku);

        if (empty($sku_entity)) {
          return [];
        }

        if ($sku_entity->hasTranslation('en')) {
          $sku_entity = $sku_entity->getTranslation('en');
        }

        $sku_attributes = $this->fetchSkuAtttributes($product_sku);

        // Check if this product is in stock.
        $stock_status = $this->skuManager->isProductInStock($sku_entity)
          ? 'in stock'
          : 'out of stock';
        $product_terms = $this->fetchProductCategories($node);

        $product_media = alshaya_acm_product_get_product_media($node->id(), TRUE);
        $product_media_url = !empty($product_media)
          ? file_create_url($product_media['drupal_uri'])
          : '';

        $oldprice = '';
        if ((float) $sku_entity->get('price')->getString() != (float) $sku_attributes['gtm-price']) {
          $oldprice = (float) $sku_entity->get('price')->getString();
        }
        $page_dl_attributes = [
          'productSKU' => $sku_attributes['gtm-sku-type'] === 'configurable' ? '' : $product_sku,
          'productStyleCode' => $product_sku,
          'stockStatus' => $stock_status,
          'productName' => $node->getTitle(),
          'productBrand' => !empty($sku_attributes['gtm-brand']) ? $sku_attributes['gtm-brand'] : $sku_entity->get('attr_product_brand')->getString(),
          'productColor' => '',
          'productPrice' => (float) $sku_attributes['gtm-price'],
          'productOldPrice' => $oldprice,
          'productPictureUrl' => $product_media_url,
          'productRating' => '',
          'productReviews' => '',
          'magentoProductID' => $sku_entity->get('product_id')->getString(),
        ];

        if ($sku_entity->bundle() == 'configurable') {
          $prices = $this->skuManager->getMinPrices($sku_entity);
          $page_dl_attributes['productOldPrice'] = $prices['price'];
          if ($prices['price'] == $page_dl_attributes['productPrice']) {
            $page_dl_attributes['productOldPrice'] = '';
          }
        }

        $page_dl_attributes = array_merge($page_dl_attributes, $this->fetchDepartmentAttributes($product_terms));
        break;

      case 'product listing page':
        $taxonomy_term = $current_route['route_params']['taxonomy_term'];
        $taxonomy_parents = array_reverse($this->entityTypeManager->getStorage('taxonomy_term')->loadAllParents($taxonomy_term->id()));
        foreach ($taxonomy_parents as $taxonomy_parent) {
          $taxonomy_parent = $this->entityRepository->getTranslationFromContext($taxonomy_parent, $this->languageManager->getCurrentLanguage()->getId());
          $terms[$taxonomy_parent->id()] = $taxonomy_parent->getName();
        }

        $page_dl_attributes = $this->fetchDepartmentAttributes($terms);
        break;

      case 'advanced page':
      case 'department page':
        $department_node = $current_route['route_params']['node'];
        if ($department_node->get('field_use_as_department_page')->value == 1) {
          $taxonomy_term = $this->entityTypeManager->getStorage('taxonomy_term')
            ->load($department_node->get('field_product_category')->target_id);
          if (!empty($taxonomy_term)) {
            $taxonomy_parents = array_reverse($this->entityTypeManager->getStorage('taxonomy_term')
              ->loadAllParents($taxonomy_term->id()));
            foreach ($taxonomy_parents as $taxonomy_parent) {
              $terms[$taxonomy_parent->id()] = $taxonomy_parent->getName();
            }

            $page_dl_attributes = $this->fetchDepartmentAttributes($terms);
          }
        }
        break;

      case 'cart page':
      case 'checkout login page':
      case 'checkout delivery page':
      case 'checkout payment page':
        $cart = $this->cartStorage->getCart(FALSE);
        $gtm_disabled_vars = $this->configFactory->get('alshaya_seo.disabled_gtm_vars')->get('disabled_vars');

        if ($cart) {
          $cart_totals = $cart->totals();
          $cart_items = $cart->items() ?? [];
          $productStyleCode = [];
          $productSKU = [];

          foreach ($cart_items as $item) {
            $productSKU[] = $item['sku'];
            $product_node = $this->skuManager->getDisplayNode($item['sku']);
            if ($product_node instanceof NodeInterface) {
              $productStyleCode[] = $this->skuManager->getSkuForNode($product_node);
            }
          }

          $page_dl_attributes = [
            'productSKU' => $productSKU,
            'productStyleCode' => $productStyleCode,
            'cartTotalValue' => (float) $cart_totals['grand'],
            'cartItemsCount' => count($cart_items),
          ];

          // Add cartItemsRR variable only when its not in the list of disabled
          // vars.
          if (!in_array('cartItemsRR', $gtm_disabled_vars)) {
            $page_dl_attributes['cartItemsRR'] = $this->formatCartRr($cart_items);
          }

          // Add cartItemsFlocktory only for cart page.
          if ($page_type == 'cart page') {
            $page_dl_attributes['cartItemsFlocktory'] = $this->formatCartFlocktory($cart_items);
          }

          if (($page_type === 'checkout delivery page') || ($page_type === 'checkout payment page') || ($page_type === 'checkout click and collect page')) {
            if ($shipping = $cart->getShippingMethodAsString()) {
              $shipping_method = $this->checkoutOptionsManager->loadShippingMethod($shipping);

              $page_dl_attributes['deliveryOption'] = 'Home Delivery';
              $page_dl_attributes['deliveryType'] = $shipping_method->getName();

              $shipping_method_name = $shipping_method->get('field_shipping_code')->getString();

              // Check if selected shipping method is click and collect.
              if ($shipping_method_name === $this->checkoutOptionsManager->getClickandColectShippingMethod()) {
                $page_dl_attributes['deliveryOption'] = 'Click and Collect';
                $page_dl_attributes['deliveryType'] = $cart->getExtension('click_and_collect_type');

                if ($store = $this->storeFinder->getStoreFromCode($cart->getExtension('store_code'))) {
                  $page_dl_attributes['storeLocation'] = $store->label();
                  $storeAddress = $this->storeFinder->getStoreAddress($store, TRUE, TRUE);
                  $page_dl_attributes['storeAddress'] = $storeAddress['address_line1'] . ' ' . $storeAddress['administrative_area_display'];
                }
              }
              else {
                $delivery_area = $this->addressBookManager->getCartShippingAreaValue($cart);
                $delivery_city = $this->addressBookManager->getCartShippingAreaParentValue($cart);

                if ($delivery_area) {
                  $page_dl_attributes['deliveryArea'] = $delivery_area;
                }
                if ($delivery_city) {
                  $page_dl_attributes['deliveryCity'] = $delivery_city;
                }
              }

              if (isset($page_dl_attributes['deliveryType']) && !empty($shipping_method)) {
                $site_default_langcode = $this->languageManager->getDefaultLanguage()->getId();
                $site_current_langcode = $this->languageManager->getCurrentLanguage()->getId();

                if ($site_current_langcode !== $site_default_langcode) {
                  if ($shipping_method->hasTranslation($site_default_langcode)) {
                    $delivery_type_term = $shipping_method->getTranslation($site_default_langcode);
                    $page_dl_attributes['deliveryType'] = $delivery_type_term->getName();
                  }
                }
              }
            }
          }
        }
        break;

      case 'purchase confirmation page':
        $order = _alshaya_acm_checkout_get_last_order_from_session(TRUE);

        // Validations will be handled in other code.
        if (empty($order)) {
          return $page_dl_attributes;
        }

        $orderItems = $order['items'];
        $productSKU = [];
        $productStyleCode = [];
        $store_code = '';
        $gtm_disabled_vars = $this->configFactory->get('alshaya_seo.disabled_gtm_vars')->get('disabled_vars');

        $shipping_method = $this->checkoutOptionsManager->loadShippingMethod($order['shipping']['method']);
        $shipping_method_name = $shipping_method->get('field_shipping_code')->getString();
        if ($shipping_method_name === $this->checkoutOptionsManager->getClickandColectShippingMethod()) {
          $shipping_assignment = reset($order['extension']['shipping_assignments']);
          $store_code = $shipping_assignment['shipping']['extension_attributes']['store_code'];
        }

        $deliveryArea = $this->addressBookManager->getAddressShippingAreaValue($order['shipping']['address']);
        $address = $this->addressBookManager->getAddressArrayFromMagentoAddress($order['shipping']['address']);
        $deliveryCity = $this->addressBookManager->getAddressShippingAreaParentValue($address, $order['shipping']['address']);

        foreach ($orderItems as $orderItem) {
          $productSKU[] = $orderItem['sku'];
          $product_node = $this->skuManager->getDisplayNode($orderItem['sku']);

          if ($product_node instanceof NodeInterface) {
            $productStyleCode[] = $this->skuManager->getSkuForNode($product_node);
          }
        }

        $page_dl_attributes = [
          'productSKU' => $productSKU,
          'productStyleCode' => $productStyleCode,
          'cartTotalValue' => (float) $order['totals']['grand'],
          'cartItemsCount' => count($orderItems),
        ];

        // We should always have store but a sanity check. Additional check to
        // ensure the variable is not in list of disabled vars.
        if ($store_code && ($store = $this->storeFinder->getStoreFromCode($store_code))) {
          $page_dl_attributes['storeLocation'] = $store->label();
          $storeAddress = $this->storeFinder->getStoreAddress($store, TRUE, TRUE);
          $page_dl_attributes['storeAddress'] = $storeAddress['address_line1'] . ' ' . $storeAddress['administrative_area_display'];
        }

        // Add cartItemsRR variable only when its not in the list of disabled
        // vars.
        if (!in_array('cartItemsRR', $gtm_disabled_vars)) {
          $page_dl_attributes['cartItemsRR'] = $this->formatCartRr($orderItems);
        }

        // Add cartItemsFlocktory variable only when its not in the list of
        // disabled vars.
        if (!in_array('cartItemsFlocktory', $gtm_disabled_vars)) {
          $page_dl_attributes['cartItemsFlocktory'] = $this->formatCartFlocktory($orderItems);
        }

        if ($deliveryArea) {
          $page_dl_attributes['deliveryArea'] = $deliveryArea;
        }
        if ($deliveryCity) {
          $page_dl_attributes['deliveryCity'] = $deliveryCity;
        }

        break;
    }

    return $page_dl_attributes;
  }

  /**
   * Helper function to get department specific attributes from terms.
   */
  public function fetchDepartmentAttributes($terms) {
    $term_ids = array_keys($terms);

    return array_filter([
      'departmentName' => implode('|', $terms),
      'departmentId' => current($term_ids),
      'listingName' => end($terms),
      'listingId' => end($term_ids),
      'majorCategory' => array_shift($terms) ?: '',
      'minorCategory' => array_shift($terms) ?: '',
      'subCategory' => array_shift($terms) ?: '',
    ]);
  }

  /**
   * Helper function to fetch cart Items in RR format.
   *
   * @param array $items
   *   Cart items array.
   *
   * @return array
   *   Cart items in RR format.
   */
  public function formatCartRr(array $items) {
    $cart_items_rr = [];

    foreach ($items as $item) {
      $cart_items_rr[] = [
        'id' => $item['sku'],
        'price' => (float) $item['price'],
        'qnt' => isset($item['qty']) ? $item['qty'] : $item['ordered'],
      ];
    }

    return $cart_items_rr;
  }

  /**
   * Helper function to fetch cart Items in flocktory format.
   *
   * @param array $items
   *   Cart items array.
   *
   * @return array
   *   Cart items in flocktory format.
   */
  public function formatCartFlocktory(array $items) {
    $cart_items_flock = [];

    foreach ($items as $item) {
      $product_node = $this->skuManager->getDisplayNode($item['sku']);
      // Get product media.
      if ($product_node instanceof NodeInterface) {
        $sku_media = alshaya_acm_product_get_product_media($product_node->id(), TRUE) ?: '';
      }

      $sku_media_url = empty($sku_media)
        ? 'image not available'
        : file_create_url($sku_media['drupal_uri']);

      $sku_entity = SKU::loadFromSku($item['sku']);
      if ($sku_entity instanceof SKU && $sku_entity->hasTranslation('en')) {
        $sku_entity = $sku_entity->getTranslation('en');
        $item['name'] = $sku_entity->label();
      }

      $cart_items_flock[] = [
        'id' => $item['sku'],
        'price' => (float) $item['price'],
        'count' => isset($item['qty']) ? $item['qty'] : $item['ordered'],
        'title' => $item['name'],
        'image' => $sku_media_url,
      ];
    }

    return $cart_items_flock;
  }

  /**
   * Helper function to get gtm currency code.
   *
   * @return string
   *   GTM currency code.
   */
  public function getGtmCurrency() {
    $currency_code = $this->configFactory->get('acq_commerce.currency');
    return !empty($currency_code->get('iso_currency_code'))
      ? $currency_code->get('iso_currency_code')
      : $currency_code->get('currency_code');
  }

}
