<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\AcqSkuLinkedSku;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductInfoHelper;
use Drupal\acq_sku\ProductOptionsManager;
use Drupal\address\Repository\CountryRepository;
use Drupal\alshaya_acm_checkout\CheckoutOptionsManager;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AlshayaSpcOrderHelper.
 */
class AlshayaSpcOrderHelper {

  use StringTranslationTrait;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Address book manager.
   *
   * @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager
   */
  protected $addressBookManager;

  /**
   * The current user making the request.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * SKU Images Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  private $skuImagesManager;

  /**
   * Product Info helper.
   *
   * @var \Drupal\acq_sku\ProductInfoHelper
   */
  private $productInfoHelper;

  /**
   * Production Options Manager service object.
   *
   * @var \Drupal\acq_sku\ProductOptionsManager
   */
  private $productOptionsManager;

  /**
   * Store cache tags and contexts to be added in response.
   *
   * @var array
   */
  private $cache;

  /**
   * Sku info helper.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuInfoHelper
   */
  private $skuInfoHelper;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * Mobile utility.
   *
   * @var \Drupal\mobile_number\MobileNumberUtilInterface
   */
  protected $mobileUtil;

  /**
   * Chekcout option manager.
   *
   * @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager
   */
  protected $checkoutOptionManager;

  /**
   * Country repository.
   *
   * @var \Drupal\address\Repository\CountryRepository
   */
  protected $countryRepository;

  /**
   * Secure Text service provider.
   *
   * @var \Drupal\alshaya_spc\Helper\SecureText
   */
  protected $secureText;

  /**
   * Request Stack.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $request;

  /**
   * Orders Manager.
   *
   * @var \Drupal\alshaya_acm_customer\OrdersManager
   */
  protected $ordersManager;

  /**
   * AlshayaSpcCustomerHelper constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager
   *   Address book manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user object.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU Images Manager.
   * @param \Drupal\acq_sku\ProductInfoHelper $product_info_helper
   *   Product Info helper.
   * @param \Drupal\acq_sku\ProductOptionsManager $product_options_manager
   *   Production Options Manager service object.
   * @param \Drupal\alshaya_acm_product\Service\SkuInfoHelper $sku_info_helper
   *   Sku info helper object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   Mobile utility.
   * @param \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager
   *   Checkout option manager.
   * @param \Drupal\address\Repository\CountryRepository $countryRepository
   *   Country Repository.
   * @param \Drupal\alshaya_spc\Helper\SecureText $secure_text
   *   Secure Text service provider.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request Stack.
   * @param \Drupal\alshaya_acm_customer\OrdersManager $orders_manager
   *   Orders Manager.
   */
  public function __construct(ModuleHandlerInterface $module_handler,
                              AlshayaAddressBookManager $address_book_manager,
                              AccountProxyInterface $current_user,
                              SkuManager $sku_manager,
                              SkuImagesManager $sku_images_manager,
                              ProductInfoHelper $product_info_helper,
                              ProductOptionsManager $product_options_manager,
                              SkuInfoHelper $sku_info_helper,
                              LanguageManagerInterface $language_manager,
                              MobileNumberUtilInterface $mobile_util,
                              CheckoutOptionsManager $checkout_options_manager,
                              CountryRepository $countryRepository,
                              SecureText $secure_text,
                              RequestStack $request_stack,
                              OrdersManager $orders_manager) {
    $this->moduleHandler = $module_handler;
    $this->addressBookManager = $address_book_manager;
    $this->currentUser = $current_user;
    $this->skuManager = $sku_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->productInfoHelper = $product_info_helper;
    $this->productOptionsManager = $product_options_manager;
    $this->skuInfoHelper = $sku_info_helper;
    $this->languageManager = $language_manager;
    $this->mobileUtil = $mobile_util;
    $this->checkoutOptionManager = $checkout_options_manager;
    $this->countryRepository = $countryRepository;
    $this->secureText = $secure_text;
    $this->request = $request_stack->getCurrentRequest();
    $this->ordersManager = $orders_manager;
  }

  /**
   * Helper function to return order from session.
   *
   * @return array
   *   Order array if found.
   */
  public function getLastOrder() {
    static $order;

    if (!empty($order)) {
      return $order;
    }

    $id = $this->request->query->get('id');
    if (empty($id)) {
      throw new NotFoundHttpException();
    }

    $data = json_decode($this->secureText->decrypt(
      $id,
      Settings::get('alshaya_api.settings')['consumer_secret']
    ), TRUE);

    if (empty($data['order_id']) || !is_numeric($data['order_id']) || empty($data['email'])) {
      throw new NotFoundHttpException();
    }

    // Security checks.
    if ($this->currentUser->isAuthenticated() && $this->currentUser->getEmail() !== $data['email']) {
      throw new AccessDeniedHttpException();
    }
    elseif ($this->currentUser->isAnonymous() && !empty(user_load_by_mail($data['email']))) {
      throw new AccessDeniedHttpException();
    }

    $order = $this->ordersManager->getOrder($data['order_id']);

    if (empty($order) || $order['email'] != $data['email']) {
      throw new AccessDeniedHttpException();
    }

    return $order;
  }

  /**
   * Responds to GET requests.
   *
   * Returns available delivery method data.
   *
   * @return array|null
   *   The response containing delivery methods data.
   */
  public function getSkuDetails(string $sku) {
    $skuEntity = SKU::loadFromSku($sku);

    if (!($skuEntity instanceof SKUInterface)) {
      return NULL;
    }

    $node = $this->skuManager->getDisplayNode($sku);
    if (!($node instanceof NodeInterface)) {
      return NULL;
    }

    $link = $node->toUrl('canonical', ['absolute' => TRUE])
      ->toString(TRUE)
      ->getGeneratedUrl();

    $data = $this->getSkuData($skuEntity, $link);

    $data['relative_link'] = str_replace('/' . $this->languageManager->getCurrentLanguage()->getId() . '/',
      '',
      $node->toUrl('canonical', ['absolute' => FALSE])->toString(TRUE)->getGeneratedUrl());

    $data['delivery_options'] = NestedArray::mergeDeepArray([$this->getDeliveryOptionsConfig($skuEntity), $data['delivery_options']], TRUE);

    return $data;
  }

  /**
   * Wrapper function to get product data.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $link
   *   Product link if main product.
   *
   * @return array
   *   Product Data.
   */
  private function getSkuData(SKUInterface $sku, string $link = ''): array {
    /** @var \Drupal\acq_sku\Entity\SKU $sku */
    $data = [];

    $data['id'] = (int) $sku->id();
    $data['sku'] = $sku->getSku();
    if ($link) {
      $data['link'] = $link;
    }
    $parent_sku = $this->skuManager->getParentSkuBySku($sku);
    $data['parent_sku'] = $parent_sku ? $parent_sku->getSku() : NULL;
    $data['title'] = (string) $this->productInfoHelper->getTitle($sku, 'pdp');

    $prices = $this->skuManager->getMinPrices($sku);
    $data['original_price'] = $this->skuInfoHelper->formatPriceDisplay((float) $prices['price']);
    $data['final_price'] = $this->skuInfoHelper->formatPriceDisplay((float) $prices['final_price']);

    $stockInfo = $this->skuInfoHelper->stockInfo($sku);
    $data['stock'] = $stockInfo['stock'];
    $data['in_stock'] = $stockInfo['in_stock'];
    $data['max_sale_qty'] = $stockInfo['max_sale_qty'];
    $data['delivery_options'] = [
      'home_delivery' => [],
      'click_and_collect' => [],
    ];
    $data['delivery_options'] = NestedArray::mergeDeepArray([$this->getDeliveryOptionsStatus($sku), $data['delivery_options']], TRUE);

    foreach (AcqSkuLinkedSku::LINKED_SKU_TYPES as $linked_type) {
      $data['linked'][] = [
        'link_type' => $linked_type,
        'skus' => $this->getLinkedSkus($sku, $linked_type),
      ];
    }

    $media_contexts = [
      'pdp' => 'detail',
      'search' => 'listing',
      'teaser' => 'teaser',
    ];
    foreach ($media_contexts as $key => $context) {
      $data['media'][] = [
        'context' => $context,
        'media' => $this->skuInfoHelper->getMedia($sku, $key),
      ];
    }

    $label_contexts = [
      'pdp' => 'detail',
      'plp' => 'listing',
    ];
    foreach ($label_contexts as $key => $context) {
      $data['labels'][] = [
        'context' => $context,
        'labels' => $this->skuManager->getSkuLabels($sku, $key),
      ];
    }

    $data['attributes'] = $this->skuInfoHelper->getAttributes($sku);

    $data['promotions'] = $this->getPromotions($sku);
    $promo_label = $this->skuManager->getDiscountedPriceMarkup($data['original_price'], $data['final_price']);
    if ($promo_label) {
      $data['promotions'][] = [
        'text' => $promo_label,
      ];
    }

    $data['configurable_values'] = $this->getConfigurableValues($sku);

    if ($sku->bundle() === 'configurable') {
      $data['swatch_data'] = $this->getSwatchData($sku);
      $data['cart_combinations'] = $this->getConfigurableCombinations($sku);

      foreach ($data['cart_combinations']['by_sku'] ?? [] as $values) {
        $child = SKU::loadFromSku($values['sku']);
        if (!$child instanceof SKUInterface) {
          continue;
        }
        $variant = $this->getSkuData($child);
        $variant['configurable_values'] = $this->getConfigurableValues($child, $values['attributes']);
        $data['variants'][] = $variant;
      }

      $data['swatch_data'] = $data['swatch_data']?: new \stdClass();
      $data['cart_combinations'] = $data['cart_combinations']?: new \stdClass();
    }

    // Adding extra data to the product resource.
    $this->moduleHandler->loadInclude('alshaya_acm_product.utility', 'inc');
    $data['extra_data'] = [];
    $image = alshaya_acm_get_product_display_image($sku, 'cart_thumbnail', 'cart');
    if (!empty($image)) {
      if ($image['#theme'] == 'image_style') {
        $data['extra_data']['cart_image'] = [
          'url' => ImageStyle::load($image['#style_name'])->buildUrl($image['#uri']),
          'title' => $image['#title'],
          'alt' => $image['#alt'],
        ];
      }
      elseif ($image['#theme'] == 'image') {
        $data['extra_data']['cart_image'] = [
          'url' => $image['#attributes']['src'],
          'title' => $image['#attributes']['title'],
          'alt' => $image['#attributes']['alt'],
        ];
      }
    }

    // Removing media if context set as we don't require and to
    // make response light.
    unset($data['media']);

    // Allow other modules to alter light product data.
    $type = 'full';
    $this->moduleHandler->alter('alshaya_acm_product_light_product_data', $sku, $data, $type);

    return $data;
  }

  /**
   * Get delivery options for pdp.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Delivery options for pdp.
   */
  private function getDeliveryOptionsConfig(SKUInterface $sku) {
    return [
      'home_delivery' => alshaya_acm_product_get_home_delivery_config(),
      'click_and_collect' => alshaya_click_collect_get_config(),
    ];
  }

  /**
   * Wrapper function to get media items for an SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Media Items.
   */
  private function getDeliveryOptionsStatus(SKUInterface $sku) {
    $this->moduleHandler->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
    return [
      'home_delivery' => [
        'status' => alshaya_acm_product_is_buyable($sku) && alshaya_acm_product_available_home_delivery($sku),
      ],
      'click_and_collect' => [
        'status' => alshaya_acm_product_available_click_collect($sku),
      ],
    ];
  }

  /**
   * Wrapper function get swatches data.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Swatches Data.
   */
  private function getSwatchData(SKUInterface $sku): array {
    $swatches = $this->skuImagesManager->getSwatchData($sku);

    if (isset($swatches['swatches'])) {
      $swatches['swatches'] = array_values($swatches['swatches']);
    }

    return $swatches;
  }

  /**
   * Wrapper function get configurable values.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param array $attributes
   *   Array of attributes containing attribute code and value.
   *
   * @return array
   *   Configurable Values.
   */
  private function getConfigurableValues(SKUInterface $sku, array $attributes = []): array {
    if ($sku->bundle() !== 'simple') {
      return [];
    }

    $values = $this->skuManager->getConfigurableValues($sku);
    $attr_values = array_column($attributes, 'value', 'attribute_code');
    foreach ($values as $attribute_code => &$value) {
      $value['attribute_code'] = $attribute_code;
      if (isset($attr_values[str_replace('attr_', '', $attribute_code)]) && $attr_value = $attr_values[str_replace('attr_', '', $attribute_code)]) {
        $value['value'] = (string) $attr_value;
      }
    }

    return array_values($values);
  }

  /**
   * Wrapper function get configurable combinations.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Configurable combinations.
   */
  private function getConfigurableCombinations(SKUInterface $sku): array {
    $combinations = $this->skuManager->getConfigurableCombinations($sku);
    unset($combinations['by_attribute']);

    foreach ($combinations['by_sku'] ?? [] as $child_sku => $attributes) {
      $combinations['by_sku'][$child_sku] = [
        'sku' => $child_sku,
      ];

      foreach ($attributes as $attribute_code => $value) {
        $combinations['by_sku'][$child_sku]['attributes'][] = [
          'attribute_code' => $attribute_code,
          'value' => (int) $value,
        ];
      }
    }

    $size_labels = $this->skuInfoHelper->getSizeLabels($sku);
    foreach ($combinations['attribute_sku'] ?? [] as $attribute_code => $attribute_data) {
      $combinations['attribute_sku'][$attribute_code] = [
        'attribute_code' => $attribute_code,
      ];

      foreach ($attribute_data as $value => $skus) {
        $attr_value = [
          'value' => $value,
          'skus' => $skus,
        ];

        if ($attribute_code == 'size') {
          if (!empty($size_labels[$value])) {
            $attr_value['label'] = $size_labels[$value];
          }
          elseif (
            ($term = $this->productOptionsManager->loadProductOptionByOptionId(
              $attribute_code,
              $value,
              $this->languageManager->getCurrentLanguage()->getId())
            )
            && $term instanceof TermInterface
          ) {
            $attr_value['label'] = $term->label();
          }
        }

        $combinations['attribute_sku'][$attribute_code]['values'][] = $attr_value;
      }
    }

    foreach ($combinations as $key => $value) {
      $combinations[$key] = array_values($value);
    }

    return $combinations;
  }

  /**
   * Wrapper function get promotions.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Promotions.
   */
  private function getPromotions(SKUInterface $sku): array {
    $promotions = [];
    $promotions_data = $this->skuManager->getPromotionsFromSkuId($sku, '', ['cart'], 'full');
    foreach ($promotions_data as $nid => $promotion) {
      $promotions[] = [
        'text' => $promotion['text'],
        'promo_web_url' => str_replace('/' . $this->languageManager->getCurrentLanguage()->getId() . '/',
          '',
          Url::fromRoute('entity.node.canonical', ['node' => $nid])->toString(TRUE)->getGeneratedUrl()),
        'promo_node' => $nid,
      ];
    }
    return $promotions;
  }

  /**
   * Get fully loaded linked skus.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $linked_type
   *   Linked type.
   *
   * @return array
   *   Linked SKUs.
   */
  private function getLinkedSkus(SKUInterface $sku, string $linked_type) {
    $return = [];
    $linkedSkus = $this->skuInfoHelper->getLinkedSkus($sku, $linked_type);
    foreach (array_keys($linkedSkus) as $linkedSku) {
      $linkedSkuEntity = SKU::loadFromSku($linkedSku);
      if ($lightProduct = $this->skuInfoHelper->getLightProduct($linkedSkuEntity)) {
        $return[] = $lightProduct;
      }
    }
    return $return;
  }

  /**
   * Gets HD / cnc order details.
   *
   * @param array $order
   *   Order array.
   *
   * @return array
   *   Order details array.
   */
  public function getOrderTypeDetails(array $order) {
    $orderDetails = [];

    $orderDetails['contact_no'] = $this->getFormattedMobileNumber($order['shipping']['address']['telephone']);
    $shipping_method_code = $this->checkoutOptionManager->getCleanShippingMethodCode($order['shipping']['method']);
    $shippingTerm = $this->checkoutOptionManager->loadShippingMethod($shipping_method_code);
    $orderDetails['delivery_charge'] = $order['totals']['shipping'];

    $orderDetails['delivery_method'] = $shippingTerm->getName();
    $orderDetails['delivery_method_description'] = $shippingTerm->get('field_shipping_method_desc')->getString();

    if ($shipping_method_code == $this->checkoutOptionManager->getClickandColectShippingMethod()) {
      // @todo Get clickncollect store details
    }
    else {
      $orderDetails['type'] = $this->t('Home delivery');

      // Check if we have cart description available, display in parenthesis.
      if ($cart_desc = $shippingTerm->get('field_shipping_method_cart_desc')->getString()) {
        $orderDetails['delivery_method'] = $this->t('@shipping_method_name (@shipping_method_description)', [
          '@shipping_method_name' => $orderDetails['delivery_method'],
          '@shipping_method_description' => $cart_desc,
        ]);
      }

      $shipping_address = $order['shipping']['address'];

      $shipping_address_array = $this->addressBookManager->getAddressArrayFromMagentoAddress($shipping_address);
      $country_list = $this->countryRepository->getList();
      $shipping_address_array['country'] = $country_list[$shipping_address_array['country_code']];
      $shipping_address_array['telephone'] = $this->getFormattedMobileNumber($shipping_address_array['mobile_number']['value']);

      $orderDetails['delivery_address'] = $shipping_address_array;
    }

    // Don't show Billing Address for CoD payment method.
    if ($order['payment']['method'] !== 'cashondelivery') {
      $billing_address_array = $this->addressBookManager->getAddressArrayFromMagentoAddress($order['billing']);
      $billing_address_array['telephone'] = $this->getFormattedMobileNumber($billing_address_array['mobile_number']['value']);
      $orderDetails['billing_address'] = $billing_address_array;
    }

    return $orderDetails;

  }

  /**
   * Wrapper function to get formatted mobile number.
   *
   * @param string $number
   *   Number.
   *
   * @return string
   *   Formatted number if possible, as is otherwise.
   */
  public function getFormattedMobileNumber(string $number) {
    try {
      return $this->mobileUtil->getFormattedMobileNumber($number);
    }
    catch (\Throwable $e) {
      return $number;
    }
  }

}
