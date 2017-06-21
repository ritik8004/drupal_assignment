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
    $save_term = FALSE;

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

      $save_term = TRUE;

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

    // Save the term only if required.
    if ($save_term) {
      $term->save();
    }

    return $term;
  }

}
