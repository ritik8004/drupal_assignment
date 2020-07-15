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
use Drupal\Core\Render\RendererInterface;
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
   * Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

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
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request Stack.
   * @param \Drupal\alshaya_acm_customer\OrdersManager $orders_manager
   *   Orders Manager.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Config factory manager.
   * @param \Drupal\alshaya_stores_finder_transac\StoresFinderUtility $store_finder
   *   Store finder utility.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer.
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
                              RequestStack $request_stack,
                              OrdersManager $orders_manager,
                              ConfigFactory $configFactory,
                              StoresFinderUtility $store_finder,
                              RendererInterface $renderer) {
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
    $this->request = $request_stack->getCurrentRequest();
    $this->ordersManager = $orders_manager;
    $this->configFactory = $configFactory;
    $this->storeFinder = $store_finder;
    $this->renderer = $renderer;
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

    $data = json_decode(SecureText::decrypt(
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
    // We will use this as flag in React to avoid reading from local storage
    // and also avoid doing API call.
    $data['prepared'] = TRUE;

    $data['title'] = $item['name'];
    $data['finalPrice'] = $this->skuInfoHelper->formatPriceDisplay((float) $item['price']);
    $data['sku'] = $item['sku'];
    $data['id'] = $item['item_id'];
    $data['image'] = NULL;
    $data['url'] = '';
    $data['isNonRefundable'] = NULL;

    $data['options'] = [];
    $node = $this->skuManager->getDisplayNode($item['sku']);
    $skuEntity = SKU::loadFromSku($item['sku']);
    if (($skuEntity instanceof SKUInterface) && ($node instanceof NodeInterface)) {
      $data['title'] = $this->productInfoHelper->getTitle($skuEntity, 'basket');
      $data['url'] = $node->toUrl('canonical', ['absolute' => FALSE])->toString();
      $data['image'] = $this->getProductDisplayImage($skuEntity, 'cart_thumbnail', 'cart');
      // Check if we can find a parent SKU for this to get proper name.
      if ($this->skuManager->getParentSkuBySku($skuEntity)) {
        // Get configurable values.
        $data['options'] = array_values($this->skuManager->getConfigurableValues($skuEntity));
      }
      $data['isNonRefundable'] = $skuEntity->get('attr_non_refundable_products')->getString();
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
   * @return string
   *   Return url of image or null if not found.
   */
  protected function getProductDisplayImage($sku, $image_style = '', $context = '') {
    // Load the first image.
    $media_image = $this->skuImagesManager->getFirstImage($sku, $context);

    // If we have image for the product.
    if (!empty($media_image)) {
      $image = ImageStyle::load($image_style)->buildUrl($media_image['drupal_uri']);
    }
    else {
      // If still image is not available, set default one.
      $default_image_url = $this->skuImagesManager->getProductDefaultImageUrl();

      if ($default_image_url) {
        $image = $default_image_url;
      }
    }

    return $image ?? '';
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

    $shipping_address = $order['shipping']['address'];
    $orderDetails['customerNameShipping'] = $shipping_address['firstname'] . ' ' . $shipping_address['lastname'];

    $shipping_method_code = $this->checkoutOptionManager->getCleanShippingMethodCode($order['shipping']['method']);
    if ($shipping_method_code == $this->checkoutOptionManager->getClickandColectShippingMethod()) {
      $orderDetails['delivery_type'] = 'cc';
      $orderDetails['type'] = $orderDetails['delivery_method_description'];

      $store_code = $order['shipping']['extension_attributes']['store_code'];
      $cc_type = $order['shipping']['extension_attributes']['click_and_collect_type'];
      $orderDetails['view_on_map_link'] = '';

      // Getting store node object from store code.
      if ($store_data = $this->storeFinder->getMultipleStoresExtraData([$store_code => []])) {
        $store_node = current($store_data);
        $orderDetails['store']['store_name'] = $store_node['name'];
        $country_list = $this->countryRepository->getList();
        $orderDetails['store']['store_address'] = $store_node['cart_address_raw'];
        $orderDetails['store']['store_address']['country'] = $country_list[$store_node['cart_address_raw']['country_code']];
        $orderDetails['store']['store_phone'] = $store_node['phone_number'];
        $orderDetails['store']['map_link'] = $store_node['view_on_map_link'];
        $orderDetails['store']['store_open_hours'] = $store_node['open_hours_group'];
        $lat = $store_node['lat'];
        $lng = $store_node['lng'];
        $orderDetails['store']['view_on_map_link'] = 'https://maps.google.com/?q=' . $lat . ',' . $lng;

        $cc_text = ($cc_type == 'reserve_and_collect')
          ? $this->configFactory->get('alshaya_click_collect.settings')->get('click_collect_rnc')
          : $store_node['delivery_time'];

        if (!empty($cc_text)) {
          $orderDetails['delivery_method_description'] = $this->t('@shipping_method_name (@shipping_method_description)', [
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

    // Remove empty items.
    return array_filter($processed);
  }

}
