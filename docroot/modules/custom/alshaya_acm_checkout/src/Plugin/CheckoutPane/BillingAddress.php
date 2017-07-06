<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
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

    $cart = $this->getCart();

    $pane_form['summary'] = [
      '#markup' => $this->t('Is the delivery address the same as your billing address?'),
    ];

    $pane_form['same_as_shipping'] = [
      '#type' => 'radios',
      '#options' => [
        1 => $this->t('Yes'),
        2 => $this->t('No'),
      ],
      '#attributes' => ['class' => ['same-as-shipping']],
      '#ajax' => [
        'callback' => [$this, 'updateAddressAjaxCallback'],
      ],
      '#default_value' => 1,
    ];

    // By default we want to use same address as shipping.
    $same_as_shipping = 1;

    if ($form_state->getValues()) {
      $values = $form_state->getValue($pane_form['#parents']);
      $same_as_shipping = (int) $values['same_as_shipping'];
    }

    if ($same_as_shipping === 1) {
      // Add empty wrapper to use when we click on No.
      $pane_form['address'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['address_wrapper'],
        ],
      ];
    }
    else {
      $billing_address = (array) $cart->getBilling();
      if (!empty($billing_address['country_id'])) {
        /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
        $address_book_manager = \Drupal::service('alshaya_addressbook.manager');
        $address_default_value = $address_book_manager->getAddressArrayFromMagentoAddress($billing_address);
        $form_state->setTemporaryValue('default_value_mobile', $address_default_value['mobile_number']);
      }
      else {
        $address_default_value = [
          'country_code' => _alshaya_custom_get_site_level_country_code(),
        ];
      }

      $pane_form['address']['billing'] = [
        '#type' => 'address',
        '#title' => '',
        '#default_value' => $address_default_value,
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
    $response->addCommand(new InvokeCommand(NULL, 'rebindCheckoutClientSideValidations'));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    if ($form_state->getErrors()) {
      return;
    }

    $values = $form_state->getValue($pane_form['#parents']);

    if ($values['same_as_shipping'] != 1) {
      $address_values = $values['address']['billing'];

      /** @var \Drupal\profile\Entity\Profile $profile */
      $profile = \Drupal::entityTypeManager()->getStorage('profile')->create([
        'type' => 'address_book',
        'uid' => 0,
        'field_address' => $address_values,
      ]);

      /* @var \Drupal\Core\Entity\EntityConstraintViolationListInterface $violations */
      if ($violations = $profile->validate()) {
        foreach ($violations->getByFields(['field_address']) as $violation) {
          $error_field = explode('.', $violation->getPropertyPath());
          $form_state->setErrorByName('billing_address][address][shipping][' . $error_field[2], $violation->getMessage());
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);
    $cart = $this->getCart();

    /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
    $address_book_manager = \Drupal::service('alshaya_addressbook.manager');

    if ($values['same_as_shipping'] == 1) {
      $shipping_address = $cart->getShipping();

      // Loading address from address book if customer_address_id is available.
      if (isset($shipping_address['customer_address_id'])) {
        if ($entity = $address_book_manager->getUserAddressByCommerceId($shipping_address['customer_address_id'])) {
          $shipping_address = $address_book_manager->getAddressFromEntity($entity, FALSE);
        }
      }

      $cart->setBilling(_alshaya_acm_checkout_clean_address($shipping_address));
    }
    else {
      $address_values = $values['address']['billing'];

      $address = $address_book_manager->getMagentoAddressFromAddressArray($address_values);

      $cart->setBilling(_alshaya_acm_checkout_clean_address($address));
    }
  }

}
