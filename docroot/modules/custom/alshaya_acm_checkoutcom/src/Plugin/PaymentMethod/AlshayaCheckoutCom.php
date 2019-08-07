<?php

namespace Drupal\alshaya_acm_checkoutcom\Plugin\PaymentMethod;

use Drupal\acq_checkoutcom\Plugin\PaymentMethod\CheckoutCom;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AlshayaCheckoutCom.
 *
 * @package Drupal\alshaya_acm_checkoutcom\Plugin\PaymentMethod
 */
class AlshayaCheckoutCom extends CheckoutCom {

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    // Set the default payment card to display form, to enter new card.
    $session = $this->currentRequest->getSession();
    $payment_card = $session->get('checkout_com_payment_card');

    $pane_form['payment_card_details'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['payment_details_checkout_com'],
      ],
      '#attached' => [
        'library' => [
          $this->getCheckoutKitLibrary(),
          'alshaya_acm_checkoutcom/checkoutcom',
          'acq_checkoutcom/checkoutcom.form',
        ],
      ],
    ];

    $cc_cvv_help = $this->t('This code is a three or four digit number printed on the front or back of the credit card');
    $cc_prefix = '<div class="cvv-wrapper">';
    $cc_suffix = '<div class="cvv-help-text-wrapper"><div class="mobile-tooltip-icon"><span class="tooltip-icon"></span><span class="tooltip-content"><p>' . $cc_cvv_help . '</p></span></div><div class="cc_cvv_help_text">' . $cc_cvv_help . '</div></div></div>';

    $customer_stored_cards = [];
    // Display tokenised cards for logged in user.
    if ($this->currentUser->isAuthenticated() && $this->apiHelper->getCheckoutcomConfig('vault_enabled')) {
      $user = $this->entityTypeManager->getStorage('user')->load(
        $this->currentUser->id()
      );
      $form_state->addBuildInfo('user', $user);
      $customer_stored_cards = $this->apiHelper->getCustomerCards($user);
      $stored_cards_list = $this->prepareRadioOptionsMarkup($customer_stored_cards);

      $payment_card = empty($payment_card) && !empty($customer_stored_cards) ? current(array_keys($customer_stored_cards)) : $payment_card;
      $payment_card = empty($customer_stored_cards) ? 'new' : $payment_card;
      $values = $form_state->getValue('acm_payment_methods');
      if (!empty($values) && !empty($values['payment_details_wrapper']['payment_method_checkout_com']['payment_card'])) {
        $payment_card = $values['payment_details_wrapper']['payment_method_checkout_com']['payment_card'];
      }

      if (!empty($stored_cards_list)) {
        $stored_cards_list += ['new' => '<span class="new">' . $this->t('New Card') . '</span>'];
        $pane_form['payment_card'] = [
          '#type' => 'radios',
          '#options' => $stored_cards_list,
          '#default_value' => $payment_card,
          '#required' => TRUE,
          '#ajax' => [
            'callback' => [$this, 'renderSelectedCardFields'],
            'wrapper' => 'payment_details_checkout_com',
            'method' => 'replace',
            'effect' => 'fade',
          ],
        ];

        $weight = 0;
        foreach ($stored_cards_list as $card_hash => $card_info) {
          $pane_form['payment_card_details']['payment_card_' . $card_hash] = [
            '#type' => 'container',
            '#attributes' => [
              'id' => ['payment_method_' . $card_hash],
            ],
            '#weight' => $weight++,
          ];

          $title_class = ['payment-card-wrapper-div'];

          if ($card_hash == $payment_card) {
            $title_class[] = 'card-selected';
          }

          $title = '<div id="payment_method_title_' . $card_hash . '"';
          $title .= ' class="' . implode(' ', $title_class) . '" ';
          $title .= ' data-value="' . $card_hash . '" ';
          $title .= '>';
          $title .= $card_info;
          $title .= '</div>';

          $pane_form['payment_card_details']['payment_card_' . $card_hash]['title'] = [
            '#markup' => $title,
          ];

          if ($payment_card && $payment_card != 'new') {
            // Set mada value for tokenised card.
            $pane_form['payment_card_details']['payment_card_' . $payment_card]['mada'] = [
              '#type' => 'hidden',
              '#value' => $customer_stored_cards[$payment_card]['mada'] ?? FALSE,
            ];
          }
          else {
            $pane_form['payment_card_details']['payment_card_' . $payment_card]['new'] = [
              '#type' => 'container',
              '#tree' => FALSE,
              '#attributes' => [
                'id' => ['payment_card_' . $card_hash],
                'class' => ['payment_card_new'],
              ],
            ];
            $pane_form['payment_card_details']['payment_card_' . $payment_card]['new'] += $this->formHelper->newCardInfoForm($pane_form['payment_card_details']['payment_card_' . $payment_card]['new'], $form_state);
            $pane_form['payment_card_details']['payment_card_' . $payment_card]['new']['cc_cvv']['#prefix'] = $cc_prefix;
            $pane_form['payment_card_details']['payment_card_' . $payment_card]['new']['cc_cvv']['#suffix'] = $cc_suffix;
            $pane_form['payment_card_details']['payment_card_' . $payment_card]['new']['cc_exp_month']['#attributes']['class'][] = 'convert-to-select2';
            $pane_form['payment_card_details']['payment_card_' . $payment_card]['new']['cc_exp_year']['#attributes']['class'][] = 'convert-to-select2';
          }
        }
      }
    }

    if ($this->currentUser->isAnonymous() || empty($customer_stored_cards)) {
      $pane_form['payment_card_details']['payment_card_new'] = [
        '#type' => 'container',
        '#tree' => FALSE,
        '#attributes' => [
          'id' => ['payment_method_new'],
          'class' => ['payment_card_new'],
        ],
      ];

      $pane_form['payment_card_details']['payment_card_new'] += $this->formHelper->newCardInfoForm($pane_form['payment_card_details']['payment_card_new'], $form_state);
      $pane_form['payment_card_details']['payment_card_new']['cc_cvv']['#prefix'] = $cc_prefix;
      $pane_form['payment_card_details']['payment_card_new']['cc_cvv']['#suffix'] = $cc_suffix;
      $pane_form['payment_card_details']['payment_card_new']['cc_exp_month']['#attributes']['class'][] = 'convert-to-select2';
      $pane_form['payment_card_details']['payment_card_new']['cc_exp_year']['#attributes']['class'][] = 'convert-to-select2';
    }
    return $pane_form;
  }

  /**
   * Process with correct payment type for given card info.
   *
   * @param array $card
   *   Card info.
   */
  protected function selectCheckoutComPayment(array $card) {
    // Set the card related data in session to use it to prepare request data
    // for checkout.com api.
    if ($card['mada']) {
      $session = $this->currentRequest->getSession();
      $session->set('acq_checkout_com_card', $card);
    }
    else {
      $this->initiate2dPayment($card);
    }
  }

}
