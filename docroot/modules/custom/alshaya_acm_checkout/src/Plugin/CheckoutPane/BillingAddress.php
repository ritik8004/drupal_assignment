<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the billing address form.
 *
 * @ACQCheckoutPane(
 *   id = "billing_address",
 *   label = @Translation("Billing address"),
 *   defaultStep = "payment",
 *   wrapperElement = "fieldset",
 * )
 */
class BillingAddress extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * Billing address case - same as shipping.
   */
  public const BILLING_ADDR_CASE_SAME_AS_SHIPPING = 1;

  /**
   * Billing address case - not same as shipping.
   */
  public const BILLING_ADDR_CASE_NOT_SAME_AS_SHIPPING = 2;

  /**
   * Billing address case - click and collect.
   */
  public const BILLING_ADDR_CASE_CLICK_COLLECT = 3;

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'weight' => 10,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $complete_form['messages'] = [
      '#type' => 'status_messages',
      '#weight' => -49,
    ];

    // We don't show this form completely when we use CoD.
    $selected_payment_method = $this->getCheckoutHelper()->getSelectedPayment();
    if ($selected_payment_method === 'cashondelivery') {
      $pane_form['#access'] = FALSE;

      // We still need to copy from Shipping for Magento.
      $pane_form['same_as_shipping'] = [
        '#type' => 'value',
        '#value' => self::BILLING_ADDR_CASE_SAME_AS_SHIPPING,
      ];

      $this->getCheckoutHelper()->setBillingFromShipping();

      return $pane_form;
    }

    $billing_info = $this->getCheckoutHelper()->getBillingInfoFromHistory();
    $is_same = $billing_info['is_same'] ?? self::BILLING_ADDR_CASE_SAME_AS_SHIPPING;

    /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
    $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');

    $cart = $this->getCart();
    $shipping_method = $cart->getShippingMethodAsString();
    $shipping_method = $checkout_options_manager->getCleanShippingMethodCode($shipping_method);

    if ($shipping_method == $checkout_options_manager->getClickandColectShippingMethod()) {
      // For click and collect we always want the billing address.
      $same_as_shipping = self::BILLING_ADDR_CASE_CLICK_COLLECT;

      $pane_form['same_as_shipping'] = [
        '#type' => 'value',
        '#value' => $same_as_shipping,
      ];

      // We don't want to show shipping in billing for CC.
      // @todo Re-visit when working on CORE-4483.
      if ($is_same) {
        $billing_info['address'] = [];
      }
    }
    else {
      // By default we want to use same address as shipping.
      $same_as_shipping = $is_same
        ? self::BILLING_ADDR_CASE_SAME_AS_SHIPPING
        : self::BILLING_ADDR_CASE_NOT_SAME_AS_SHIPPING;

      $pane_form['summary'] = [
        '#markup' => $this->t('Is the delivery address the same as your billing address?'),
      ];

      $pane_form['same_as_shipping'] = [
        '#type' => 'radios',
        '#options' => [
          self::BILLING_ADDR_CASE_SAME_AS_SHIPPING => $this->t('Yes'),
          self::BILLING_ADDR_CASE_NOT_SAME_AS_SHIPPING => $this->t('No'),
        ],
        '#attributes' => ['class' => ['same-as-shipping']],
        '#ajax' => [
          'callback' => $this->updateAddressAjaxCallback(...),
        ],
        '#default_value' => $same_as_shipping,
      ];
    }

    if ($form_state->getValues()) {
      $values = $form_state->getValue($pane_form['#parents']);
      $same_as_shipping = (int) $values['same_as_shipping'];
    }

    $pane_form['address'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['address_wrapper'],
      ],
      '#attached' => [
        'library' => [
          'core/drupal.form',
          'alshaya_white_label/convert_to_select2',
          'clientside_validation_jquery/cv.jquery.validate',
        ],
      ],
    ];

    if ($same_as_shipping !== self::BILLING_ADDR_CASE_SAME_AS_SHIPPING) {
      $billing_address = $billing_info['address'] ?? [];

      if (!empty($billing_address['country_id'])) {
        /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
        $address_book_manager = \Drupal::service('alshaya_addressbook.manager');
        $address_default_value = $address_book_manager->getAddressArrayFromMagentoAddress($billing_address);
        $form_state->setTemporaryValue('default_value_mobile', $address_default_value['mobile_number']);
      }
      elseif ($same_as_shipping == self::BILLING_ADDR_CASE_CLICK_COLLECT) {
        $shipping = $cart->getShipping();
        $address_default_value = [
          'given_name' => $shipping['firstname'],
          'family_name' => $shipping['lastname'],
          'country_code' => _alshaya_custom_get_site_level_country_code(),
        ];
      }
      else {
        $address_default_value = [
          'country_code' => _alshaya_custom_get_site_level_country_code(),
        ];
      }

      $pane_form['address']['billing_title'] = [
        '#markup' => '<div class="title billing-address-title">' . $this->t('Billing address') . '</div>',
      ];

      $pane_form['address']['billing'] = [
        '#type' => 'address',
        '#title' => '',
        '#default_value' => $address_default_value,
        '#required' => TRUE,
      ];
    }

    return $pane_form;
  }

  /**
   * Ajax handler for reusing address.
   */
  public static function updateAddressAjaxCallback($form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('.address_wrapper', $form['billing_address']['address']));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    if ($form_state->getErrors()) {
      return;
    }

    /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
    $address_book_manager = \Drupal::service('alshaya_addressbook.manager');

    $values = $form_state->getValue($pane_form['#parents']);

    if ($values['same_as_shipping'] != self::BILLING_ADDR_CASE_SAME_AS_SHIPPING) {
      $address_values = $values['address']['billing'] ?: [];

      if ($violations = $address_book_manager->validateAddress($address_values)) {
        foreach ($violations as $field => $message) {
          $form_state->setErrorByName('billing_address][address][billing][' . $field, $message);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);

    /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
    $address_book_manager = \Drupal::service('alshaya_addressbook.manager');

    if ($values['same_as_shipping'] == self::BILLING_ADDR_CASE_SAME_AS_SHIPPING) {
      $this->getCheckoutHelper()->setBillingFromShipping(TRUE);
    }
    else {
      $address_values = $values['address']['billing'];
      $address = $address_book_manager->getMagentoAddressFromAddressArray($address_values);
      $this->getCheckoutHelper()->setBillingInfo(FALSE, $address);
    }
  }

  /**
   * Get checkout helper service object.
   *
   * @return \Drupal\alshaya_acm_checkout\CheckoutHelper
   *   Checkout Helper service object.
   */
  protected function getCheckoutHelper() {
    static $helper;

    if (empty($helper)) {
      /** @var \Drupal\alshaya_acm_checkout\CheckoutHelper $helper */
      $helper = \Drupal::service('alshaya_acm_checkout.checkout_helper');
    }

    return $helper;
  }

}
