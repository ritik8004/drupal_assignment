<?php

namespace Drupal\alshaya_seo;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Mobile_Detect;

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
    'user.login' => 'login page',
    'acq_cart.cart' => 'cart page',
    'acq_checkout.form:login' => 'summary page',
    'acq_checkout.form:click_collect' => 'click and collect page',
    'acq_checkout.form:delivery' => 'delivery page',
    'acq_checkout.form:payment' => 'payment page',
    'acq_checkout.form:confirmation' => 'confirmation page',
    'view.stores_finder.page_2' => 'store finder',
    'entity.webform.canonical:alshaya_contact' => 'contact us',
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
    'variant' => 'gtm-product-sku',
    'dimension1' => 'gtm-dimension1',
    'dimension2' => 'gtm-dimension2',
    'dimension3' => 'gtm-dimension3',
    'dimension4' => 'gtm-dimension4',
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
   * The private temp store service.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $privateTempStore;

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
   * The Entity Manager service.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * AlshayaGtmManager constructor.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   Current route matcher service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config Factory service.
   * @param \Drupal\acq_cart\CartStorageInterface $cartStorage
   *   Cart Storage service.
   * @param \Drupal\user\PrivateTempStoreFactory $privateTempStore
   *   Private temp store service.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current User service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request stack service.
   * @param \Drupal\Core\Entity\EntityManager $entityManager
   *   Entity Manager service.
   */
  public function __construct(CurrentRouteMatch $currentRouteMatch,
                              ConfigFactoryInterface $configFactory,
                              CartStorageInterface $cartStorage,
                              PrivateTempStoreFactory $privateTempStore,
                              AccountProxyInterface $currentUser,
                              RequestStack $requestStack,
                              EntityManager $entityManager) {
    $this->currentRouteMatch = $currentRouteMatch;
    $this->configFactory = $configFactory;
    $this->cartStorage = $cartStorage;
    $this->privateTempStore = $privateTempStore;
    $this->currentUser = $currentUser;
    $this->requestStack = $requestStack;
    $this->entityManager = $entityManager;
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
    $attributes['gtm-category'] = implode('/', $this->fetchProductCategories($product));
    $attributes['gtm-container'] = $this->convertCurrentRouteToGtmPageName($this->getGtmContainer());
    $attributes['gtm-view-mode'] = $view_mode;
    $attributes['gtm-cart-value'] = '';
    $attributes['gtm-main-sku'] = $product->get('field_skus')->first()->getString();

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
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
    $sku = SKU::loadFromSku($skuId);

    $attributes = [];

    $attributes['gtm-name'] = $sku->label();
    $price = $sku->get('final_price')->getString() ? $sku->get('final_price')->getString() : 0.000;
    $attributes['gtm-price'] = (float) number_format((float) $price, 3, '.', '');
    $attributes['gtm-brand'] = $sku->get('attr_product_brand')->getString() ?: 'Mothercare Kuwait';
    $attributes['gtm-product-sku'] = $sku->getSku();

    // Dimension1 & 2 correspond to size & color.
    // Should stay blank unless added to cart.
    $attributes['gtm-dimension1'] = $sku->get('attr_size')->getString();
    $attributes['gtm-dimension2'] = $sku->get('attr_product_collection')->getString();
    $attributes['gtm-dimension3'] = $sku->get('attribute_set')->getString();
    $attributes['gtm-dimension4'] = count($sku->getMedia()) ?: '';
    $attributes['gtm-stock'] = '';
    $attributes['gtm-sku-type'] = $sku->bundle();

    if ($parent_sku = alshaya_acm_product_get_parent_sku_by_sku($skuId)) {
      $attributes['gtm-sku-type'] = $parent_sku->bundle();
    }

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
      $currentRoute['query'] = $this->requestStack->getCurrentRequest()->query->all();
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

        case 'acq_cart.form':
          if (isset($currentRoute['route_params']['step'])) {
            if (($currentRoute['route_params']['step'] === 'delivery') &&
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

    // Set dimension1 & 2 to empty until product added to cart.
    $attributes['gtm-dimension1'] = '';
    $attributes['gtm-dimension2'] = '';
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
    $attributes = [];

    foreach ($cartItems as $cartItem) {
      $skuId = $cartItem['sku'];
      $attributes[$skuId] = $this->fetchSkuAtttributes($skuId);
      // Fetch product for this sku to get the category.
      $productNode = alshaya_acm_product_get_display_node($skuId);
      // Get product media.
      $first_sku = $productNode->get('field_skus')->first()->get('entity')->getValue();
      $attributes[$skuId]['gtm-dimension4'] = count($first_sku->getMedia()) ?: '';
      $attributes[$skuId]['gtm-category'] = implode('/', $this->fetchProductCategories($productNode));
      $attributes[$skuId]['gtm-main-sku'] = $productNode->get('field_skus')->first()->getString();
      $attributes[$skuId]['quantity'] = $cartItem['qty'];
      $attributes[$skuId]['gtm-product-sku'] = $cartItem['sku'];
    }

    return $attributes;
  }

  /**
   * Helper function to fetch & concatenate product categories.
   *
   * @param \Drupal\node\Entity\Node $productNode
   *   Product node.
   *
   * @return array
   *   Concatenated product categories.
   *
   * @throws \InvalidArgumentException
   */
  public function fetchProductCategories(Node $productNode) {
    $categories = $productNode->get('field_category')->getValue();
    $terms = [];

    if (count($categories) > 1) {
      foreach ($categories as $category) {
        $term = Term::load($category['target_id']);
        $terms[] = $term->getName();
      }
    }
    elseif (count($categories) === 1) {
      // Load parent terms of the category & send them across to GTM too.
      $category = array_shift($categories);
      $category_parents = $this->entityManager->getStorage('taxonomy_term')->loadAllParents($category['target_id']);
      $category_parents = array_reverse($category_parents);

      foreach ($category_parents as $category_parent) {
        $terms[$category_parent->id()] = $category_parent->getName();
      }
    }

    return $terms;
  }

  /**
   * Helper function to fetch order attributes.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   * @throws \InvalidArgumentException
   */
  public function fetchCompletedOrderAttributes() {

    $temp_store = $this->privateTempStore->get('alshaya_acm_checkout');
    $order_data = $temp_store->get('order');

    // Throw access denied if nothing in session.
    if (empty($order_data) || empty($order_data['id'])) {
      throw new AccessDeniedHttpException();
    }

    $order_id = (int) str_replace('"', '', $order_data['id']);

    if ($this->currentUser->isAnonymous()) {
      $email = $temp_store->get('email');
    }
    else {
      $email = $this->currentUser->getEmail();
    }

    $orders = alshaya_acm_customer_get_user_orders($email);

    $order_index = array_search($order_id, array_column($orders, 'order_id'), TRUE);

    if ($order_index === FALSE) {
      throw new NotFoundHttpException();
    }
    $order = $orders[$order_index];
    $orderItems = $order['items'];

    foreach ($orderItems as $key => $item) {
      $product = $this->fetchSkuAtttributes($item['sku']);
      $productNode = alshaya_acm_product_get_display_node($item['sku']);
      $product['gtm-category'] = implode('/', $this->fetchProductCategories($productNode));
      $product['gtm-main-sku'] = $productNode->get('field_skus')->first()->getString();
      $productExtras = [
        'quantity' => $item['ordered'],
        'dimension6' => '',
        'dimension7' => '',
      ];

      $products[] = array_merge($this->convertHtmlAttributesToDatalayer($product), $productExtras);
    }

    $actionData = [
      'id' => $order['increment_id'],
      'affiliation' => 'Online Store',
      'revenue' => (float) $order['totals']['grand'],
      'tax' => (float) $order['totals']['tax'] ?: 0.00,
      'shippping' => (float) $order['shipping']['method']['amount'] ?: 0.00,
      'coupon' => $order['coupon'],
    ];

    $generalInfo = [
      'deliveryOption' => $order['shipping']['method']['carrier_code'],
      'paymentOption' => $order['payment']['method_title'],
      'discountAmount' => (float) $order['totals']['discount'],
      'transactionID' => $order['increment_id'],
      'firstTimeTransaction' => count($orders) > 1 ? 'False' : 'True',
    ];

    return [
      'general' => $generalInfo,
      'products' => $products,
      'actionField' => $actionData,
    ];
  }

  /**
   * Helper function to fetch general datalayer attributes for a page.
   */
  public function fetchGeneralPageAttributes($data_layer) {
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');
    // Detect platform from headers.
    $request_headers = $this->requestStack->getCurrentRequest()->headers->all();
    $request_ua = $this->requestStack->getCurrentRequest()->headers->get('HTTP_USER_AGENT');
    $mobile_detect = new Mobile_Detect($request_headers, $request_ua);
    $platform = 'Desktop';
    if ($mobile_detect->isMobile()) {
      $platform = 'Mobile';
    }
    elseif ($mobile_detect->isTablet()) {
      $platform = 'Tablet';
    }

    if ($data_layer['userUid'] !== 0) {
      $customer_type = count(alshaya_acm_customer_get_user_orders($data_layer['userMail'])) ? 'repeat buyer' : 'first time buyer';
    }
    else {
      $customer_type = 'first time buyer';
    }

    $data_layer_attributes = [
      'language' => $data_layer['drupalLanguage'],
      'platformType' => $platform,
      'country' => 'Kuwait',
      'currency' => 'KWD',
      'userID' => $data_layer['userUid'],
      'userEmailID' => ($data_layer['userUid'] !== 0) ? $data_layer['userMail'] : '',
      'customerType' => $customer_type,
      'userName' => ($data_layer['userUid'] !== 0) ? $data_layer['userName'] : '',
      'userType' => $data_layer['userUid'] ? 'Logged in User' : 'Guest User',
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
        $product_sku = $node->get('field_skus')->getValue();
        $sku_entity = SKU::loadFromSku($product_sku);
        $sku_attributes = $this->fetchSkuAtttributes($product_sku);

        // Check if this product is in stock.
        $stock_response = alshaya_acm_is_product_in_stock($sku_entity);
        $stock_status = $stock_response ? 'in stock' : 'out of stock';
        $product_terms = $this->fetchProductCategories($node);

        $page_dl_attributes = [
          'product_style_code' => $product_sku,
          'product_sku' => $sku_attributes['gtm-sku-type'] === 'configurable' ? '' : $product_sku,
          'stock_status' => $stock_status,
          'product_name' => $node->getTitle(),
          'product_brand' => $sku_attributes['gtm-brand'],
          'product_color' => '',
          'product_size' => $sku_attributes['gtm-dimension1'],
          'product_price' => $sku_attributes['gtm-price'],
          'product_old_price' => $sku_entity->get('price')->getString(),
          'product_picture_url' => alshaya_acm_product_get_product_media($node->id(), TRUE),
          'product_rating' => '',
          'product_reviews' => '',
          'product_magento_id' => $product_sku,
        ];

        $page_dl_attributes = array_merge($page_dl_attributes, $this->fetchDepartmentAttributes($product_terms));
        break;

      case 'product listing page':
        $taxonomy_term = $current_route['route_params']['taxonomy_term'];
        $taxonomy_parents = array_reverse($this->entityManager->getStorage('taxonomy_term')->loadAllParents($taxonomy_term->id()));
        foreach ($taxonomy_parents as $taxonomy_parent) {
          $terms[$taxonomy_parent->id()] = $taxonomy_parent->getName();
        }

        $page_dl_attributes = $this->fetchDepartmentAttributes($terms);
        break;
    }

    return $page_dl_attributes;
  }

  /**
   * Helper function to get department specific attributes from terms.
   */
  public function fetchDepartmentAttributes($terms) {
    return [
      'department_name' => implode('|', $terms),
      'department_id' => implode('|', array_keys($terms)),
      'listing_name' => implode(',', $terms),
      'listing_id' => implode(',', array_keys($terms)),
      'major_category' => array_shift($terms),
      'minor_category' => array_shift($terms),
      'sub_category' => array_shift($terms) ?: '',
    ];
  }

}
