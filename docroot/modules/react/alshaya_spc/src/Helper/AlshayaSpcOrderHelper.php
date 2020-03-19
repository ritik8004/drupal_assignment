<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductInfoHelper;
use Drupal\acq_sku\ProductOptionsManager;
use Drupal\address\Repository\CountryRepository;
use Drupal\alshaya_acm_checkout\CheckoutOptionsManager;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_stores_finder_transac\StoresFinderUtility;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\image\Entity\ImageStyle;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\node\NodeInterface;
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
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Store Finder utility.
   *
   * @var \Drupal\alshaya_stores_finder_transac\StoresFinderUtility
   */
  protected $storeFinder;

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
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Config factory manager.
   * @param \Drupal\alshaya_stores_finder_transac\StoresFinderUtility $store_finder
   *   Store finder utility.
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
                              OrdersManager $orders_manager,
                              ConfigFactory $configFactory,
                              StoresFinderUtility $store_finder) {
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
    $this->configFactory = $configFactory;
    $this->storeFinder = $store_finder;
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
  public function getSkuDetails(array $item) {
    $data['title'] = $item['name'];
    $data['final_price'] = $this->skuInfoHelper->formatPriceDisplay((float) $item['price']);
    $data['extra_data']['cart_image'] = NULL;
    $data['relative_link'] = '';
    $data['configurable_values'] = ($item['product_type'] === 'configurable')
      ? $item['extension_attributes']['product_options'][0]['attributes_info']
      : [];

    $node = $this->skuManager->getDisplayNode($item['sku']);
    $skuEntity = SKU::loadFromSku($item['sku']);
    if (($skuEntity instanceof SKUInterface) && ($node instanceof NodeInterface)) {
      $data['title'] = $this->productInfoHelper->getTitle($skuEntity, 'basket');
      $data['relative_link'] = $node->toUrl('canonical', ['absolute' => FALSE])->toString();
      $data['extra_data']['cart_image'] = $this->getProductDisplayImage($skuEntity, 'cart_thumbnail', 'cart');
    }

    return $data;
  }

  /**
   * Helper function to get the product display image.
   *
   * @param mixed $sku
   *   SKU text or full entity object.
   * @param string $image_style
   *   Image style to apply to the image.
   * @param string $context
   *   (optional) Context for image.
   *
   * @return array
   *   Return string of uri or Null if not found.
   */
  public function getProductDisplayImage($sku, $image_style = '', $context = '') {
    // Load the first image.
    $media_image = $this->skuImagesManager->getFirstImage($sku, $context);

    // If we have image for the product.
    if (!empty($media_image)) {
      $image = [
        'url' => ImageStyle::load($image_style)->buildUrl($media_image['drupal_uri']),
        'title' => $sku->label(),
        'alt' => $sku->label(),
      ];
    }
    else {
      // If still image is not available, set default one.
      $default_image_url = $this->skuImagesManager->getProductDefaultImageUrl();

      if ($default_image_url) {
        $image = [
          'url' => $default_image_url,
          'title' => $sku->label(),
          'alt' => $sku->label(),
        ];
      }
    }

    return $image ?? [];
  }

  /**
   * Get order details processed.
   *
   * @param array $order
   *   Order array.
   *
   * @return array
   *   Order details array.
   */
  public function getOrderDetails(array $order) {
    $orderDetails = [];

    $orderDetails['contact_no'] = $this->getFormattedMobileNumber($order['shipping']['address']['telephone']);
    $orderDetails['delivery_charge'] = $order['totals']['shipping'];

    $shipping_info = explode(' - ', $order['shipping_description']);
    $orderDetails['delivery_method'] = $shipping_info[0];
    $orderDetails['delivery_method_description'] = $shipping_info[1] ?? $shipping_info[0];

    $shipping_method_code = $this->checkoutOptionManager->getCleanShippingMethodCode($order['shipping']['method']);
    if ($shipping_method_code == $this->checkoutOptionManager->getClickandColectShippingMethod()) {
      $orderDetails['type'] = 'cc';

      $store_code = $order['shipping']['extension_attributes']['store_code'];
      $cc_type = $order['shipping']['extension_attributes']['click_and_collect_type'];
      $orderDetails['view_on_map_link'] = '';

      // Getting store node object from store code.
      if ($store_node = $this->storeFinder->getTranslatedStoreFromCode($store_code)) {
        $orderDetails['store_name'] = $store_node->label();
        $orderDetails['store_address'] = $this->storeFinder->getStoreAddress($store_node);
        $orderDetails['store_phone'] = $store_node->get('field_store_phone')->getString();
        $orderDetails['store_open_hours'] = $store_node->get('field_store_open_hours')->getValue();

        if ($lat_lng = $store_node->get('field_latitude_longitude')->getValue()) {
          $lat = $lat_lng[0]['lat'];
          $lng = $lat_lng[0]['lng'];
          $orderDetails['view_on_map_link'] = 'https://maps.google.com/?q=' . $lat . ',' . $lng;
        }

        $cc_text = ($cc_type == 'reserve_and_collect')
          ? $this->configFactory->get('alshaya_click_collect.settings')->get('click_collect_rnc')
          : $store_node->get('field_store_sts_label')->getString();

        if (!empty($cc_text)) {
          $orderDetails['delivery_method'] = $this->t('@shipping_method_name (@shipping_method_description)', [
            '@shipping_method_name' => $orderDetails['delivery_method'],
            '@shipping_method_description' => $cc_text,
          ]);
        }
      }
    }
    else {
      $orderDetails['type'] = $this->t('Home delivery');
      $orderDetails['delivery_type'] = 'HD';
      $orderDetails['delivery_address'] = $this->getProcessedAddress($order['shipping']['address']);
    }

    $payment = $this->checkoutOptionManager->loadPaymentMethod($order['payment']['method']);
    $orderDetails['payment']['method'] = $payment->label();
    $orderDetails['payment']['methodCode'] = $order['payment']['method'];

    $orderDetails['billing'] = ($order['payment']['method'] === 'cashondelivery')
      ? NULL
      : $this->getProcessedAddress($order['billing']);

    if ($order['payment']['method'] === 'knet') {
      // @TODO: Get this information from Magento in a better way.
      $orderDetails['payment']['transactionId'] = $order['payment']['additional_information'][0];
      $orderDetails['payment']['paymentId'] = $order['payment']['additional_information'][2];

      // @TODO: Get this information from Magento.
      $orderDetails['payment']['resultCode'] = 'CAPTURED';
    }
    elseif ($order['payment']['method'] == 'banktransfer' && !empty(array_filter($order['extension']['bank_transfer_instructions']))) {
      $instructions = $order['extension']['bank_transfer_instructions'];
      $bank_transfer = [
        '#theme' => 'banktransfer_payment_details',
        '#account_number' => $instructions['account_number'],
        '#address' => $instructions['address'],
        '#bank_name' => $instructions['bank_name'],
        '#beneficiary_name' => $instructions['beneficiary_name'],
        '#branch' => $instructions['branch'],
        '#iban' => $instructions['iban'],
        '#swift_code' => $instructions['swift_code'],
        '#purpose' => $this->t('Purchase of Goods - @order_id', [
          '@order_id' => $order['increment_id'],
        ]),
      ];

      $orderDetails['payment']['bankDetails'] = $this->renderer->renderPlain($bank_transfer);
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

  /**
   * Get processed address.
   *
   * @param array $address
   *   Address details.
   *
   * @return array
   *   Processed address array.
   */
  protected function getProcessedAddress(array $address) {
    $processed = $this->addressBookManager->getAddressArrayFromMagentoAddress($address);

    $country_list = $this->countryRepository->getList();
    $processed['country'] = $country_list[$processed['country_code']];

    $processed['telephone'] = $this->getFormattedMobileNumber($processed['mobile_number']['value']);

    return $processed;
  }

}
