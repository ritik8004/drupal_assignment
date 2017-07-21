<?php

namespace Drupal\alshaya_acm_checkout;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class CheckoutOptionsManager.
 *
 * @package Drupal\alshaya_acm_checkout
 */
class CheckoutOptionsManager {

  /**
   * API Wrapper object.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

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
   * CheckoutOptionsManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   ApiWrapper object.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   Cart Storage service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory, APIWrapper $api_wrapper, CartStorageInterface $cart_storage, LoggerChannelFactoryInterface $logger_factory) {
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->configFactory = $config_factory;
    $this->apiWrapper = $api_wrapper;
    $this->cartStorage = $cart_storage;
    $this->logger = $logger_factory->get('alshaya_acm_checkout');
  }

  /**
   * Function to load or create shipping method term from code.
   *
   * @param string $code
   *   Shipping method code.
   * @param string $name
   *   Name of shipping method, available during checkout.
   * @param string $description
   *   Description of shipping method, available during checkout.
   * @param string $carrier_code
   *   Carrier code.
   * @param string $method_code
   *   Method code.
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   *   Term object.
   */
  public function loadShippingMethod($code, $name = '', $description = '', $carrier_code = '', $method_code = '') {
    // Simple check to avoid 500 errors. Might not come in production but
    // issue might come during development.
    if (empty($code)) {
      return;
    }

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
      ]);

      $term->get('description')->setValue($description);
      $term->get('field_shipping_method_desc')->setValue($name);
      $term->get('field_shipping_code')->setValue($code);
      $term->get('field_shipping_carrier_code')->setValue($carrier_code);
      $term->get('field_shipping_method_code')->setValue($method_code);

      $term->save();

      $this->logger->critical('New shipping method created for code @code. Please save the description asap.', ['@code' => $code]);
    }
    else {
      if (count($result) > 1) {
        $this->logger->error('Duplicate shipping method terms found for code @code.', ['@code' => $code]);
      }

      $tid = array_shift($result);

      /** @var \Drupal\taxonomy\Entity\Term $term */
      $term = $this->termStorage->load($tid);

      $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
      if ($term->hasTranslation($langcode)) {
        $term = $term->getTranslation($langcode);
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
   * Get all allowed payment method codes for particular shipping methods.
   *
   * @param string $shipping_method
   *   Shipping method code.
   *
   * @return array
   *   Array of payment method codes allowed for the shipping method.
   */
  public function getAllowedPaymentMethodCodes($shipping_method) {
    $shipping_method_term = $this->loadShippingMethod($shipping_method);

    $query = $this->termStorage->getQuery();
    $query->condition('vid', 'payment_method');
    $query->condition('field_payment_shipping_methods', $shipping_method_term->id());

    $result = $query->execute();

    if (empty($result)) {
      $this->logger->warning('No payment methods found for shipping method @method', ['@method' => $shipping_method]);
      return [];
    }

    $terms = $this->termStorage->loadMultiple($result);

    $methods = [];
    foreach ($terms as $term) {
      $methods[] = $term->get('field_payment_code')->getString();
    }

    return $methods;
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
   *
   * @return \Drupal\taxonomy\Entity\Term
   *   Full loaded term object.
   */
  public function loadPaymentMethod($code, $name = '') {
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

      if ($shipping_methods = $this->getAllShippingTerms(FALSE)) {
        $term->get('field_payment_shipping_methods')->setValue($shipping_methods);
      }

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

      $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
      if ($term->hasTranslation($langcode)) {
        $term = $term->getTranslation($langcode);
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
    // Below code is to ensure we call the API only once.
    static $options;
    $static_key = base64_encode(serialize($address));
    if (isset($options[$static_key]) && !empty($options[$static_key])) {
      return $options[$static_key];
    }

    $address = (array) $address;

    $address = _alshaya_acm_checkout_clean_address($address);

    $cart = $this->cartStorage->getCart();

    $shipping_methods = [];
    $shipping_method_options = [];

    if (!empty($address) && !empty($address['country_id'])) {
      $shipping_methods = $this->apiWrapper->getShippingEstimates($cart->id(), $address);
    }

    if (!empty($shipping_methods)) {
      foreach ($shipping_methods as $method) {
        // Key needs to hold both carrier and method.
        $key = $method['carrier_code'] . '_' . $method['method_code'];

        $code = $this->getCleanShippingMethodCode($key);
        $price = !empty($method['amount']) ? alshaya_acm_price_format($method['amount']) : t('FREE');

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

    if ($shipping_methods = $this->loadShippingEstimates($address)) {
      foreach ($shipping_methods as $code => $data) {
        // We don't display click and collect delivery method for home delivery.
        if ($code == $this->getClickandColectShippingMethod()) {
          continue;
        }

        $method_name = '
          <div class="shipping-method-name">
            <div class="shipping-method-title">' . $data['term']->getName() . '</div>
            <div class="shipping-method-price">' . $data['price'] . '</div>
            <div class="shipping-method-description">' . $data['term']->get('description')->getValue()[0]['value'] . '</div>
          </div>
        ';

        $shipping_method_options[$code] = $method_name;
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
    // @TODO: Currently what we get back in orders is first 32 characters
    // and concatenated by underscore.
    $code = str_replace(',', '_', $code);
    $code = substr($code, 0, 32);
    return $code;
  }

}
