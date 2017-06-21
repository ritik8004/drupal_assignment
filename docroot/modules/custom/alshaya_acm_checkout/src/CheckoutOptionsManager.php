<?php

namespace Drupal\alshaya_acm_checkout;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class CheckoutOptionsManager.
 *
 * @package Drupal\alshaya_acm_checkout
 */
class CheckoutOptionsManager {

  /**
   * Term storage object.
   *
   * @var \Drupal\taxonomy\TermStorage
   */
  protected $termStorage;

  /**
   * CheckoutOptionsManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger_factory) {
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->logger = $logger_factory->get('alshaya_acm_checkout');
  }

  /**
   * Function to load or create shipping method term from code.
   *
   * @param string $code
   *   Shipping method code.
   * @param string $name
   *   Name of shipping method, available during checkout.
   * @param string $carrier_code
   *   Carrier code.
   * @param string $method_code
   *   Method code.
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   *   Term object.
   */
  public function loadShippingMethod($code, $name = '', $carrier_code = '', $method_code = '') {
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

      $term->get('description')->setValue($name);
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

      $term->get('description')->setValue($name);

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

}
