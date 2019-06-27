<?php

namespace Drupal\acq_checkoutcom;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides required elements for checkout card from.
 *
 * @package Drupal\acq_checkoutcom
 */
trait CheckoutComCardInfoFormTrait {

  /**
   * Returns the card related necessary elements.
   *
   * @param array $form
   *   The form elements array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   Returns form elements.
   */
  public static function newCardInfoForm(array $form, FormStateInterface $form_state) {
    $states = [
      '#states' => [
        'required' => [
          ':input[name="acm_payment_methods[payment_details_wrapper][payment_method_checkout_com][payment_details][card_token]"]' => ['value' => ''],
        ],
      ],
    ];

    $form['cc_name'] = [
      '#type' => 'textfield',
      '#title' => t('Name on card'),
      '#attributes' => [
        'class' => ['checkoutcom-credit-card-name', 'checkoutcom-input'],
        'data-checkout' => 'card-name',
        'id' => 'cardName',
      ],
    ] + $states;

    $form['cc_number'] = [
      '#type' => 'tel',
      '#title' => t('Credit Card Number'),
      '#default_value' => '',
      '#attributes' => [
        'class' => ['checkoutcom-credit-card-input', 'checkoutcom-input'],
        'autocomplete' => 'cc-number',
        'data-checkout' => 'card-number',
        'id' => 'cardNumber',
      ],
    ] + $states;

    $form['cc_exp_month'] = [
      '#type' => 'textfield',
      '#title' => t('Expiration Month'),
      '#attributes' => [
        'class' => [
          'checkoutcom-credit-card-exp-month-select',
          'checkoutcom-input',
        ],
        'id' => 'expMonth',
        'data-checkout' => 'expiry-month',
      ],
    ] + $states;

    $form['cc_exp_year'] = [
      '#type' => 'textfield',
      '#title' => t('Expiration Year'),
      '#attributes' => [
        'class' => [
          'checkoutcom-credit-card-exp-year-select',
          'checkoutcom-input',
        ],
        'id' => 'expYear',
        'data-checkout' => 'expiry-year',
      ],
    ] + $states;

    $form['cc_cvv'] = [
      '#type' => 'password',
      '#maxlength' => 4,
      '#title' => t('Security code (CVV)'),
      '#default_value' => '',
      '#attributes' => [
        'class' => [
          'checkoutcom-credit-card-cvv-input',
          'checkoutcom-input',
        ],
        'id' => 'cardCvv',
        'autocomplete' => 'cc-csc',
        'data-checkout' => 'cvv',
      ],
    ] + $states;

    $form['card_token'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'cardToken',
      ],
    ];

    // Card can be saved in account for authenticated users only.
    if (\Drupal::service('current_user')->isAuthenticated()) {
      $form['save_card'] = [
        '#type'  => 'checkbox',
        '#title' => t('Save card for future use'),
      ];

      $form['cc_save_help_text'] = [
        '#type'  => 'markup',
        '#markup' => '<div class = "cc-save-help-text">' . t('This card will be securely saved for a faster payment experience. CVV number will not be saved. More Info') . '</div>',
      ];
    }

    $debug = \Drupal::service('config.factory')->get('acq_checkoutcom.settings')->get('debug') ? 'true' : 'false';
    $public_key = \Drupal::service('acq_checkoutcom.agent_api')->getSubscriptionInfo('public_key');
    $string = "window.CKOConfig = {
        debugMode: {$debug},
        publicKey: '{$public_key}',
        ready: function (event) {
          CheckoutKit.monitorForm('.multistep-checkout', CheckoutKit.CardFormModes.CARD_TOKENISATION);
        },
        cardTokenised: function(event) {
          cardToken.value = event.data.cardToken
          cardName.value = ''
          cardNumber.value = ''
          cardCvv.value = ''
          expMonth.value = ''
          expYear.value = ''
          document.getElementById('multistep-checkout').submit();
        },
      };";

    $form['checkout_kit'] = [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#value' => $string,
      '#attached' => [
        'library' => [
          'acq_checkoutcom/checkoutcom.kit',
        ],
      ],
    ];

    return $form;
  }

}
