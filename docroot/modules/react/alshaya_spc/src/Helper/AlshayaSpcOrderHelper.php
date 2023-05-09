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
use Drupal\alshaya_acm_product\SkuImagesHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_stores_finder_transac\StoresFinderUtility;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\alshaya_acm_product\DeliveryOptionsHelper;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\locale\StringStorageInterface;

/**
 * Class Alshaya Spc Order Helper.
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
  protected $skuInfoHelper;

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
   * Sku images helper.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesHelper
   */
  protected $skuImagesHelper;

  /**
   * Delivery Options helper.
   *
   * @var \Drupal\alshaya_acm_product\DeliveryOptionsHelper
   */
  protected $deliveryOptionsHelper;

  /**
   * Date time formatter interface.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The locale string storage handler.
   *
   * @var Drupal\locale\StringStorageInterface
   */
  protected $stringStorageHandler;

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
   * @param \Drupal\alshaya_acm_product\SkuImagesHelper $images_helper
   *   Sku images helper.
   * @param \Drupal\alshaya_acm_product\DeliveryOptionsHelper $delivery_options_helper
   *   Delivery Options Helper.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   * @param \Drupal\locale\StringStorageInterface $string_storage_handler
   *   The string storage handler.
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
                              RendererInterface $renderer,
                              SkuImagesHelper $images_helper,
                              DeliveryOptionsHelper $delivery_options_helper,
                              DateFormatterInterface $date_formatter,
                              StringStorageInterface $string_storage_handler) {
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
    $this->skuImagesHelper = $images_helper;
    $this->deliveryOptionsHelper = $delivery_options_helper;
    $this->dateFormatter = $date_formatter;
    $this->stringStorageHandler = $string_storage_handler;
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
    if (!empty($id)) {
      $data = json_decode(SecureText::decrypt(
        $id,
        $this->configFactory->get('alshaya_api.settings')->get('consumer_secret')
      ), TRUE);
    }

    // Parameter used for V2 from browser.
    $oid = $this->request->query->get('oid');
    if (!empty($oid)) {
      $data = (array) json_decode(base64_decode($oid), NULL);
    }

    if (empty($data)) {
      throw new NotFoundHttpException();
    }

    $data_email = strtolower(trim($data['email'] ?? ''));
    if (empty($data['order_id']) || !is_numeric($data['order_id']) || empty($data_email)) {
      throw new NotFoundHttpException();
    }

    // Security checks.
    if ($this->currentUser->isAuthenticated()) {
      $current_user_email = strtolower(trim($this->currentUser->getEmail()));
      if ($current_user_email !== $data_email) {
        throw new AccessDeniedHttpException();
      }
    }
    elseif ($this->currentUser->isAnonymous() && !empty(user_load_by_mail($data_email))) {
      throw new AccessDeniedHttpException();
    }

    $order = $this->ordersManager->getOrder($data['order_id']);
    $order_email = strtolower(trim($order['email'] ?? ''));
    if (empty($order) || $order_email !== $data_email) {
      throw new AccessDeniedHttpException();
    }

    return $order;
  }

  /**
   * Sets E-gift details to order item.
   *
   * @param array $item
   *   Order item.
   * @param array $data
   *   Array in which to set the E-gift details.
   */
  protected function setEgiftDetailsToOrderItem(array $item, array &$data) {
    // Prepare Egift Card Product, if item is virtual means its an egift card.
    if ($item['is_virtual']) {
      $data['isEgiftCard'] = $this->configFactory->get('alshaya_egift_card.settings')->get('egift_card_enabled');
      $data['media'] = $item['extension_attributes']['product_media'][0]['file'];
      $data['egiftOptions'] = json_decode($item['extension_attributes']['product_options'][0], TRUE);
      $data['price'] = $this->skuInfoHelper->formatPriceDisplay((float) $item['price']);
    }
  }

  /**
   * Responds to GET requests.
   *
   * Returns available delivery method data.
   *
   * @param array $item
   *   Order item.
   *
   * @return array|null
   *   The response containing delivery methods data.
   */
  public function getSkuDetails(array $item) {
    $data = [];
    // We will use this as flag in React to avoid reading from local storage
    // and also avoid doing API call.
    $data['prepared'] = TRUE;
    $data['freeItem'] = ($item['price_incl_tax'] == 0) || ($item['price_incl_tax'] == SkuManager::FREE_GIFT_PRICE);
    $data['title'] = $item['name'];
    $data['finalPrice'] = $this->skuInfoHelper->formatPriceDisplay((float) $item['price']);
    $data['sku'] = $item['sku'];
    $data['id'] = $item['item_id'];
    $data['image'] = NULL;
    $data['url'] = '';
    $data['isNonRefundable'] = NULL;
    // Added quantity of product for checkout olapic pixel.
    $data['qtyOrdered'] = $item['qty_ordered'];
    $this->setEgiftDetailsToOrderItem($item, $data);

    $data['options'] = [];
    $node = $this->skuManager->getDisplayNode($item['sku']);
    $skuEntity = SKU::loadFromSku($item['sku']);
    if ($skuEntity instanceof SKUInterface) {
      $data['title'] = $this->productInfoHelper->getTitle($skuEntity, 'basket');

      if ($node instanceof NodeInterface) {
        $data['url'] = $node->toUrl('canonical', ['absolute' => FALSE])->toString();
      }

      $data['image'] = $this->getProductDisplayImage($skuEntity, SkuImagesHelper::STYLE_CART_THUMBNAIL, 'cart');
      // Check if we can find a parent SKU for this to get proper name.
      if ($this->skuManager->getParentSkuBySku($skuEntity)) {
        // Get configurable values.
        $data['options'] = array_values($this->skuManager->getConfigurableValues($skuEntity));
      }
      $data['isNonRefundable'] = $skuEntity->get('attr_non_refundable_products')->getString();
      // Allow other modules to alter response data.
      $this->moduleHandler->alter('alshaya_spc_order_sku_details', $data, $skuEntity);
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
      $image = $this->skuImagesHelper->getImageStyleUrl($media_image, $image_style);
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
    $payment_info = [];
    $orderDetails = [];
    if (in_array('address', $order['shipping'])) {
      $orderDetails['contact_no'] = $this->getFormattedMobileNumber($order['shipping']['address']['telephone']);
    }
    $orderDetails['delivery_charge'] = $order['totals']['shipping'];
    // Skip Shipping information if only virtual product in order,
    // i.e. e-gift card product or e-gift top-up.
    if (!$order['is_virtual']) {
      $shipping_info = $this->deliveryOptionsHelper->ifSddEdFeatureEnabled() ? explode(' - ', $order['shipping_description'], 2) : explode(' - ', $order['shipping_description']);
      $orderDetails['delivery_method'] = $shipping_info[0];
      $orderDetails['delivery_method_description'] = ($order['shipping']['extension_attributes']['click_and_collect_type'] === 'pudo_pickup')
        ? $shipping_info[0]
        : ($shipping_info[1] ?? $shipping_info[0]);
      $shipping_address = $order['shipping']['address'];
      $orderDetails['customerNameShipping'] = $shipping_address['firstname'] . ' ' . $shipping_address['lastname'];

      $shipping_method_code = $this->checkoutOptionManager->getCleanShippingMethodCode($order['shipping']['method']);
      $orderDetails['shipping_method_code'] = $shipping_method_code;
      if ($shipping_method_code == $this->checkoutOptionManager->getClickandColectShippingMethod()) {
        $orderDetails['delivery_type'] = 'cc';
        $orderDetails['type'] = $orderDetails['delivery_method_description'];

        $store_code = $order['shipping']['extension_attributes']['store_code'];
        $cc_type = $order['shipping']['extension_attributes']['click_and_collect_type'];
        $orderDetails['view_on_map_link'] = '';
        $orderDetails['collection_charge'] = $order['shipping']['extension_attributes']['price_amount'] ?? '';
        $orderDetails['collection_date'] = $order['shipping']['extension_attributes']['pickup_date'] ?? '';

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
          $orderDetails['store']['collection_point'] = $order['shipping']['extension_attributes']['collection_point'] ?? '';
          $orderDetails['store']['pudo_available'] = $order['shipping']['extension_attributes']['click_and_collect_type'] === 'pudo_pickup';

          $cc_text = ($cc_type == 'reserve_and_collect')
            ? $this->configFactory->get('alshaya_click_collect.settings')->get('click_collect_rnc')
            : $store_node['delivery_time'];

          if (!empty($cc_text)) {
            $orderDetails['delivery_method_description'] = ($orderDetails['store']['pudo_available'] === TRUE)
              ? $this->t('@shipping_method_description', [
                '@shipping_method_description' => $cc_text,
              ])
              : $this->t('@shipping_method_name (@shipping_method_description)', [
                '@shipping_method_name' => $orderDetails['delivery_method'],
                '@shipping_method_description' => $cc_text,
              ]);

            $orderDetails['shipping_method_code'] = $shipping_method_code;
          }
        }
      }
      else {
        $orderDetails['type'] = $this->t('Home delivery');
        $orderDetails['delivery_type'] = 'HD';
        $orderDetails['delivery_address'] = $this->getProcessedAddress($order['shipping']['address']);
      }
    }

    $payment = $this->checkoutOptionManager->loadPaymentMethod($order['payment']['method']);
    $orderDetails['payment']['method'] = $payment->label();
    $orderDetails['payment']['methodCode'] = $order['payment']['method'];

    $orderDetails['billing'] = ($order['payment']['method'] === 'cashondelivery')
      ? NULL
      : $this->getProcessedAddress($order['billing']);

    foreach ($order['extension']['payment_additional_info'] ?? [] as $payment_additiona_info) {
      $payment_info[$payment_additiona_info['key']] = $payment_additiona_info['value'];
    }

    switch ($order['payment']['method']) {
      case 'knet':
        $orderDetails['payment']['transactionId'] = $payment_info['transaction_id'];
        $orderDetails['payment']['paymentId'] = $payment_info['payment_id'];
        $orderDetails['payment']['resultCode'] = 'CAPTURED';
        break;

      case 'checkout_com_upapi_knet':
        $orderDetails['payment']['transactionId'] = $payment_info['knet_transaction_id'];
        $orderDetails['payment']['paymentId'] = $payment_info['knet_payment_id'];
        $orderDetails['payment']['resultCode'] = $payment_info['knet_result'];
        break;

      case 'checkout_com_upapi_qpay':
        $orderDetails['payment']['transactionId'] = $payment_info['confirmation_id'];
        $orderDetails['payment']['paymentId'] = $payment_info['pun'] ?? '';
        $orderDetails['payment']['resultCode'] = $payment_info['status_message'] ?? $payment_info['status'];
        $orderDetails['payment']['orderDate'] = alshaya_master_get_site_date_from_api_date(
          $order['created_at'],
          'order_date'
        );

        break;

      case 'banktransfer':
        if (!empty(array_filter($order['extension']['bank_transfer_instructions']))) {
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
        break;

      case 'checkout_com_upapi_fawry':
        $orderDetails['payment']['referenceNumber'] = $payment_info['reference_number'];
        $orderDetails['payment']['paymentExpiryTime'] = $payment_info['payment_expiry_time'];

        break;

      case 'checkout_com_upapi_benefitpay':
        $orderDetails['payment']['methodTitle'] = $payment_info['method_title'] ?? '';
        $orderDetails['payment']['qrData'] = $payment_info['qr_data'] ?? '';
        $orderDetails['payment']['referenceNumber'] = $payment_info['reference_number'] ?? '';
        $orderDetails['payment']['paymentId'] = $payment_info['payment_id'] ?? '';
        $orderDetails['payment']['paymentExpiryTime'] = $payment_info['payment_expiry_time'] ?? '';

        break;
    }

    // Process HFD online booking information if available and if the order is
    // eligible for the HFD booking.
    if (isset($order['online_booking_information'])
      && isset($order['online_booking_information']['is_hfd_booking_order'])
      && $order['online_booking_information']['is_hfd_booking_order']) {
      // Check if the appointment is booked with order or failed. To confirm
      // this we will check if the 'appointment_date' and 'start_time' are
      // available of not. If 'appointment_date' and 'start_time' values are
      // available, we will process to display booking details on FE.
      if (!empty($order['online_booking_information']['appointment_date'])
        && !empty($order['online_booking_information']['start_time'])) {
        // For the translations of AM and PM in start and end time of booking.
        // We assume time will always be in format of '10:00 AM' or '11:00 PM',
        // having a space as delimitor. We will translate the AM/PM from the
        // exploded array and implode it again with translated string.
        $start_time = $order['online_booking_information']['start_time'];
        $end_time = $order['online_booking_information']['end_time'];

        // If the current language is not the english, we will fetch and update
        // translations.
        $current_language = $this->languageManager->getCurrentLanguage()->getId();
        if ($current_language !== 'en') {
          // We can't use the variables in t function so need to fetch the AM/PM
          // translations from the storage directly.
          // Translation for AM/PM in start time.
          $start_time_array = explode(' ', $start_time);
          if (count($start_time_array) > 1) {
            $string = $this->stringStorageHandler->getTranslations([
              'source' => $start_time_array[1],
              'language' => $current_language,
              'context' => 'online_booking',
            ]);
            $start_time_array[1] = (!empty($string)) ? $string[0]->getString() : $start_time_array[1];
            $start_time = implode(' ', $start_time_array);
          }
          // Translation for AM/PM in end time.
          $end_time_array = explode(' ', $end_time);
          if (count($end_time_array) > 1) {
            $string = $this->stringStorageHandler->getTranslations([
              'source' => $end_time_array[1],
              'language' => $current_language,
              'context' => 'online_booking',
            ]);
            $end_time_array[1] = (!empty($string)) ? $string[0]->getString() : $end_time_array[1];
            $end_time = implode(' ', $end_time_array);
          }
        }

        $orderDetails['online_booking_information'] = $this->t('<b>@appointment_date</b> between <b>@start_time</b> - <b>@end_time</b>', [
          '@appointment_date' => $this->dateFormatter->format(
            strtotime($order['online_booking_information']['appointment_date']),
            'online_booking',
            'd-M-Y',
          ),
          '@start_time' => $start_time,
          '@end_time' => $end_time,
        ],
        [
          'context' => 'online_booking',
        ]
        );
      }
      else {
        // If the order is HFD order, i.e. 'is_hfd_booking_order' is set to
        // true and 'appointment_date' isn't available, it means there were
        // some errors in appointment booking during the order placement. We
        // need to show an error message to customer in this case.
        $orderDetails['online_booking_information'] = $this->t('There was some error while booking. Please contact to customer care.', [], ['context' => 'online_booking']);
      }
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
    // Return if number is empty or null.
    if (empty($number)) {
      return '';
    }

    try {
      return $this->mobileUtil->getFormattedMobileNumber($number);
    }
    catch (\Throwable) {
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

    if (in_array('mobile_number', $processed)) {
      $processed['telephone'] = $this->getFormattedMobileNumber($processed['mobile_number']['value']);
    }

    // Remove empty items.
    return array_filter($processed);
  }

}
