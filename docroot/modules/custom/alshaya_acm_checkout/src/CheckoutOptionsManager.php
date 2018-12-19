<?php

namespace Drupal\alshaya_acm_checkout;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\alshaya_acm\ApiHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class CheckoutOptionsManager.
 *
 * @package Drupal\alshaya_acm_checkout
 */
class CheckoutOptionsManager {

  use StringTranslationTrait;

  /**
   * API Helper object.
   *
   * @var \Drupal\alshaya_acm\ApiHelper
   */
  protected $apiHelper;

  /**
   * The cart storage service.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Term storage object.
   *
   * @var \Drupal\taxonomy\TermStorage
   */
  protected $termStorage;

  /**
   * The factory for configuration objects.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * THe language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * CheckoutOptionsManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\alshaya_acm\ApiHelper $api_helper
   *   API Helper object.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   Cart Storage service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language Manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory,
                              ApiHelper $api_helper,
                              CartStorageInterface $cart_storage,
                              LoggerChannelFactoryInterface $logger_factory,
                              LanguageManagerInterface $languageManager) {
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->configFactory = $config_factory;
    $this->apiHelper = $api_helper;
    $this->cartStorage = $cart_storage;
    $this->logger = $logger_factory->get('alshaya_acm_checkout');
    $this->languageManager = $languageManager;
  }

  /**
   * Function to load or create shipping method term from code.
   *
   * @param string $code
   *   Shipping method code.
   * @param string $name
   *   Name of shipping method, available during checkout.
   * @param string $description
   *   Description of shipping method, used in cart and order pages.
   * @param string $carrier_code
   *   Carrier code.
   * @param string $method_code
   *   Method code.
   * @param string $order_description
   *   Description of shipping method, used on order page.
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   *   Term object.
   */
  public function loadShippingMethod($code, $name = '', $description = '', $carrier_code = '', $method_code = '', $order_description = '') {
    // Simple check to avoid 500 errors. Might not come in production but
    // issue might come during development.
    if (empty($code)) {
      return;
    }

    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    // Clean the code every-time.
    $code = $this->getCleanShippingMethodCode($code);

    $query = $this->termStorage->getQuery();
    $query->condition('vid', 'shipping_method');
    $query->condition('field_shipping_code', $code);

    $result = $query->execute();

    if (empty($result)) {
      if (empty($name)) {
        $name = $code;
      }

      $term = $this->termStorage->create([
        'vid' => 'shipping_method',
        'name' => $name,
        'langcode' => $langcode,
      ]);

      // Following will be used as default, it will be available for
      // configuration in term edit page.
      if (empty($order_description)) {
        $order_description = $this->t('Your order will be delivered at the following address');
      }

      $term->get('field_shipping_method_cart_desc')->setValue($description);
      $term->get('field_shipping_method_desc')->setValue($order_description);
      $term->get('field_shipping_code')->setValue($code);
      $term->get('field_shipping_carrier_code')->setValue($carrier_code);
      $term->get('field_shipping_method_code')->setValue($method_code);

      $term->save();

      $this->logger->critical('New shipping method created for code @code. Please confirm the values asap.', ['@code' => $code]);
    }
    else {
      if (count($result) > 1) {
        $this->logger->error('Duplicate shipping method terms found for code @code.', ['@code' => $code]);
      }

      $tid = array_shift($result);

      /** @var \Drupal\taxonomy\Entity\Term $term */
      $term = $this->termStorage->load($tid);

      if ($term->hasTranslation($langcode)) {
        $term = $term->getTranslation($langcode);

        // Temp fix, check CORE-781 and related tickets.
        // We don't get all the values all the time, we mainly get it while
        // we try to get shipping estimates during checkout.
        // We add conditions for each field here to ensure we don't delete
        // existing data when we try to invoke on other places, for instance
        // Checkout Summary block, Order Detail page.
        $save_term = FALSE;

        if ($name && $name != $term->getName()) {
          $term->setName($name);
          $save_term = TRUE;
        }

        if ($description && $description != $term->get('field_shipping_method_cart_desc')->getString()) {
          $term->get('field_shipping_method_cart_desc')->setValue($description);
          $save_term = TRUE;
        }

        if ($order_description && $order_description != $term->get('field_shipping_method_desc')->getString()) {
          $term->get('field_shipping_method_desc')->setValue($order_description);
          $save_term = TRUE;
        }

        if ($carrier_code && $carrier_code != $term->get('field_shipping_carrier_code')->getString()) {
          $term->get('field_shipping_carrier_code')->setValue($carrier_code);
          $save_term = TRUE;
        }

        if ($method_code && $method_code != $term->get('field_shipping_method_code')->getString()) {
          $term->get('field_shipping_method_code')->setValue($method_code);
          $save_term = TRUE;
        }

        // Save the term only if there is some change done.
        if ($save_term) {
          $term->save();
        }
      }
      // If we don't have translation and values available, we create it.
      elseif (!empty($name)) {
        $term = $term->addTranslation($langcode, []);
        $term->setName($name);
        $term->get('field_shipping_method_cart_desc')->setValue($description);
        $term->get('field_shipping_method_desc')->setValue($order_description);
        $term->get('field_shipping_code')->setValue($code);
        $term->get('field_shipping_carrier_code')->setValue($carrier_code);
        $term->get('field_shipping_method_code')->setValue($method_code);
        $term->save();

        $this->logger->critical('Translation added for shipping method with code @code. Please confirm the values asap.', ['@code' => $code]);
      }
    }

    return $term;
  }

  /**
   * Returns all shipping terms.
   *
   * @param bool $loaded_terms
   *   Flag to mention if fully loaded terms are required.
   *
   * @return array|\Drupal\taxonomy\Entity\Term[]
   *   Array of terms.
   */
  public function getAllShippingTerms($loaded_terms = TRUE) {
    $query = $this->termStorage->getQuery();
    $query->condition('vid', 'shipping_method');
    $result = $query->execute();

    if (empty($result)) {
      return [];
    }

    if ($loaded_terms) {
      return $this->termStorage->loadMultiple($result);
    }

    return $result;
  }

  /**
   * Function to get default payment method term.
   *
   * @return \Drupal\taxonomy\Entity\Term
   *   Full loaded term object.
   */
  public function getDefaultPayment() {
    $query = $this->termStorage->getQuery();
    $query->condition('vid', 'payment_method');
    $query->condition('field_payment_default', 1);
    $result = $query->execute();

    if (empty($result)) {
      return '';
    }

    $tid = array_shift($result);
    return $this->termStorage->load($tid);
  }

  /**
   * Function to get default payment method code.
   *
   * @return string
   *   Default payment method code.
   */
  public function getDefaultPaymentCode() {
    if ($term = $this->getDefaultPayment()) {
      return $term->get('field_payment_code')->getString();
    }

    return '';
  }

  /**
   * Function to load or create payment method term from code.
   *
   * @param string $code
   *   Payment method code.
   * @param string $name
   *   Default available name.
   * @param bool $current_language
   *   Return the term in current language or default.
   *
   * @return \Drupal\taxonomy\Entity\Term
   *   Full loaded term object.
   */
  public function loadPaymentMethod($code, $name = '', $current_language = TRUE) {
    $query = $this->termStorage->getQuery();
    $query->condition('vid', 'payment_method');
    $query->condition('field_payment_code', $code);

    $result = $query->execute();

    if (empty($result)) {
      if (empty($name)) {
        $name = $code;
      }

      $term = $this->termStorage->create([
        'vid' => 'payment_method',
        'name' => $name,
      ]);

      if (!$this->getDefaultPaymentCode()) {
        $term->get('field_payment_default')->setValue(1);
      }

      $term->get('field_payment_code')->setValue($code);

      $term->save();

      $this->logger->critical('New payment method created for code @code. Please save the description asap.', ['@code' => $code]);
    }
    else {
      if (count($result) > 1) {
        $this->logger->error('Duplicate payment method terms found for code @code.', ['@code' => $code]);
      }

      $tid = array_shift($result);

      /** @var \Drupal\taxonomy\Entity\Term $term */
      $term = $this->termStorage->load($tid);

      if ($current_language) {
        $langcode = $this->languageManager->getCurrentLanguage()->getId();
        if ($term->hasTranslation($langcode)) {
          $term = $term->getTranslation($langcode);
        }
      }
    }

    return $term;
  }

  /**
   * Function to get the click and collect method code.
   *
   * @return string|null
   *   Click and collect method code.
   */
  public function getClickandColectShippingMethod() {
    $settings = $this->configFactory->get('alshaya_acm_checkout.settings');

    $carrier = $settings->get('click_collect_method_carrier_code');
    $method = $settings->get('click_collect_method_method_code');

    // Code hold both carrier and method.
    $code = $carrier . '_' . $method;

    return $this->getCleanShippingMethodCode($code);
  }

  /**
   * Function to get the fully loaded term object for click and collect method.
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   *   Term object for the click and collect method.
   */
  public function getClickandColectShippingMethodTerm() {
    return $this->loadShippingMethod($this->getClickandColectShippingMethod());
  }

  /**
   * Helper function to get shipping estimates.
   *
   * @param array|object $address
   *   Array of object of address.
   *
   * @return array
   *   Available shipping methods.
   */
  public function loadShippingEstimates($address) {
    $cart = $this->cartStorage->getCart(FALSE);

    if (empty($cart) || empty($cart->customerId())) {
      return [];
    }

    // Below code is to ensure we call the API only once.
    static $options;
    $static_key = base64_encode(serialize($address));
    if (isset($options[$static_key]) && !empty($options[$static_key])) {
      return $options[$static_key];
    }

    $address = (array) $address;

    $address = _alshaya_acm_checkout_clean_address($address);

    $shipping_methods = [];
    $shipping_method_options = [];

    if (!empty($address) && !empty($address['country_id'])) {
      $shipping_methods = $this->apiHelper->getShippingEstimates($address);
    }

    if (!empty($shipping_methods)) {
      foreach ($shipping_methods as $method) {
        // Key needs to hold both carrier and method.
        $key = $method['carrier_code'] . '_' . $method['method_code'];

        $code = $this->getCleanShippingMethodCode($key);
        $price = !empty($method['amount']) ? alshaya_acm_price_format($method['amount']) : $this->t('FREE');

        $term = $this->loadShippingMethod($code, $method['carrier_title'], $method['method_title'], $method['carrier_code'], $method['method_code']);

        $shipping_method_options[$code] = [
          'term' => $term,
          'price' => $price,
        ];
      }
    }

    $options[$static_key] = $shipping_method_options;

    return $shipping_method_options;
  }

  /**
   * Helper function to get shipping estimates for home delivery.
   *
   * @param array|object $address
   *   Array of object of address.
   *
   * @return array
   *   Available shipping method options.
   */
  public function getHomeDeliveryShippingEstimates($address) {
    $shipping_method_options = [];

    try {
      if ($shipping_methods = $this->loadShippingEstimates($address)) {
        foreach ($shipping_methods as $code => $data) {
          // We don't display click and collect method for home delivery.
          if ($code == $this->getClickandColectShippingMethod()) {
            continue;
          }

          $method_name = '
            <div class="shipping-method-name">
              <div class="shipping-method-title">' . $data['term']->getName() . '</div>
              <div class="shipping-method-price">' . $data['price'] . '</div>
              <div class="shipping-method-description">' . $data['term']->get('field_shipping_method_cart_desc')->getString() . '</div>
            </div>
          ';

          $shipping_method_options[$code] = $method_name;
        }
      }
    }
    catch (\Exception $e) {
      if (acq_commerce_is_exception_api_down_exception($e)) {
        drupal_set_message($e->getMessage(), 'error');
      }
    }

    return $shipping_method_options;
  }

  /**
   * Helper function to get clean code.
   *
   * @param string $code
   *   Code from API.
   *
   * @return string
   *   Cleaned code.
   */
  public function getCleanShippingMethodCode($code) {
    // @TODO: Currently what we get back in orders is first 120 characters
    // and concatenated by underscore.
    // Check MMCPA-2197 for more details.
    $code = str_replace(',', '_', $code);
    $code = substr($code, 0, 120);
    return $code;
  }

  /**
   * Helper function to fetch shipping method translations.
   *
   * @return array
   *   List of shipping methods keyed by shipping method in current language.
   */
  public function getShippingMethodTranslations() {
    $shipping_method_translations = [];

    $site_default_langcode = $this->languageManager->getDefaultLanguage()->getId();
    $site_current_langcode = $this->languageManager->getCurrentLanguage()->getId();
    if ($site_current_langcode !== $site_default_langcode) {
      $shipping_options = $this->getAllShippingTerms();
      foreach ($shipping_options as $shipping_option) {
        // Prepare translation for shipping term only if translation exists,
        // else, keep the key & value as the original term itself.
        if ($shipping_option->hasTranslation($site_current_langcode)) {
          $shipping_translation = $shipping_option->getTranslation($site_current_langcode)->getName();
          $shipping_method_translations[$shipping_translation] = $shipping_translation;
        }
        else {
          $shipping_original = $shipping_option->getName();
          $shipping_method_translations[$shipping_original] = $shipping_original;
        }
      }
    }

    return $shipping_method_translations;
  }

}
