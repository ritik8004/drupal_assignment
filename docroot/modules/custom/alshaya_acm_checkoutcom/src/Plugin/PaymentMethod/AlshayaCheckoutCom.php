<?php

namespace Drupal\alshaya_acm_checkoutcom\Plugin\PaymentMethod;

use Drupal\acq_checkoutcom\Plugin\PaymentMethod\CheckoutCom;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AlshayaCheckoutCom.
 *
 * @package Drupal\alshaya_acm_checkout\Plugin\PaymentMethod
 */
class AlshayaCheckoutCom extends CheckoutCom {

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    // Set the default payment card to display form to enter new card.
    $payment_card = 'new';

    $pane_form['payment_card_details'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['payment_details_checkout_com'],
      ],
      '#attached' => [
        'library' => [
          $this->getCheckoutKitLibrary(),
          'alshaya_acm_checkoutcom/alshaya_checkoutcom',
          'acq_checkoutcom/checkoutcom.form',
        ],
      ],
    ];

    $cc_cvv_help = t('This code is a three or four digit number printed on the front or back of the credit card');
    $cc_prefix = '<div class="cvv-wrapper">';
    $cc_suffix = '<div class="cvv-help-text-wrapper"><div class="mobile-tooltip-icon"><span class="tooltip-icon"></span><span class="tooltip-content"><p>' . $cc_cvv_help . '</p></span></div><div class="cc_cvv_help_text">' . $cc_cvv_help . '</div></div></div>';

    $stored_cards_list = [];
    // Display tokenised cards for logged in user.
    if ($this->currentUser->isAuthenticated()) {
      $user = $this->entityTypeManager->getStorage('user')->load(
        $this->currentUser->id()
      );
      $customer_stored_cards = $this->apiHelper->getCustomerCards($user);

      if (!empty($customer_stored_cards)) {
        foreach ($customer_stored_cards as $stored_card) {
          $build = [
            '#theme' => 'payment_card_teaser',
            '#card_info' => $stored_card,
            '#user' => $user,
          ];
          $stored_cards_list[$stored_card['public_hash']] = $this->renderer->render($build);
        }

        $payment_card = empty($stored_cards_list) ? $payment_card : $this->currentRequest->query->get('payment-card');
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
          foreach ($stored_cards_list as $card_id => $card_info) {
            $pane_form['payment_card_details']['payment_card_' . $card_id] = [
              '#type' => 'container',
              '#attributes' => [
                'id' => ['payment_method_' . $card_id],
              ],
              '#weight' => $weight++,
            ];

            $title_class = ['payment-card-wrapper-div'];

            if ($card_id == $payment_card) {
              $title_class[] = 'card-selected';
            }

            $title = '<div id="payment_method_title_' . $card_id . '"';
            $title .= ' class="' . implode(' ', $title_class) . '" ';
            $title .= ' data-value="' . $card_id . '" ';
            $title .= '>';
            $title .= $card_info;
            $title .= '</div>';

            $pane_form['payment_card_details']['payment_card_' . $card_id]['title'] = [
              '#markup' => $title,
            ];

            // Ask for cvv again when using existing card.
            if ($payment_card && $payment_card != 'new') {
              $pane_form['payment_card_details']['payment_card_' . $payment_card]['card_id'] = [
                '#type' => 'hidden',
                '#value' => $customer_stored_cards[$payment_card]['gateway_token'],
              ];

              $pane_form['payment_card_details']['payment_card_' . $payment_card]['cc_cvv'] = [
                '#type' => 'password',
                '#maxlength' => 4,
                '#title' => t('Security code (CVV)'),
                '#default_value' => '',
                '#attributes' => ['placeholder' => $this->t('Enter CVV')],
                '#required' => TRUE,
                '#prefix' => $cc_prefix,
                '#suffix' => $cc_suffix,
              ];
            }
            elseif ($payment_card == 'new') {
              $pane_form['payment_card_details']['payment_card_' . $payment_card]['new'] = [
                '#type' => 'container',
                '#tree' => FALSE,
                '#attributes' => [
                  'id' => ['payment_card_' . $card_id],
                  'class' => ['payment_card_new'],
                ],
              ];
              $pane_form['payment_card_details']['payment_card_' . $payment_card]['new'] += $this->formHelper->newCardInfoForm($pane_form['payment_card_details']['payment_card_' . $payment_card]['new'], $form_state);
              $pane_form['payment_card_details']['payment_card_' . $payment_card]['new']['cc_cvv']['#prefix'] = $cc_prefix;
              $pane_form['payment_card_details']['payment_card_' . $payment_card]['new']['cc_cvv']['#suffix'] = $cc_suffix;
            }
          }
        }
      }
    }

    if ($this->currentUser->isAnonymous() || empty($stored_cards_list)) {
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
    }
    return $pane_form;
  }

  /**
   * Ajax callback method to render cvv or display form to add new card.
   */
  public function renderSelectedCardFields(&$form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();

    if (empty($element)) {
      throw new NotFoundHttpException();
    }

    return $form['acm_payment_methods']['payment_details_wrapper']['payment_method_checkout_com']['payment_card_details'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaymentForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $payment_method = !empty($form_state->getValue($pane_form['#parents'])['payment_details_wrapper'])
      ? $form_state->getValue($pane_form['#parents'])['payment_details_wrapper']['payment_method_checkout_com']
      : ['payment_card' => 'new'];

    $is_new_card = (empty($payment_method['payment_card']) || $payment_method['payment_card'] == 'new')
                   && !empty($form_state->getValue('cko_card_token'));

    $is_mada_card = FALSE;
    $card_data = [];
    if ($is_new_card) {
      if ($this->checkoutComApi->isMadaEnabled() && !empty($form_state->getValue('card_bin'))) {
        $is_mada_card = $this->checkoutComApi->isMadaBin($form_state->getValue('card_bin'));
      }

      $card_data = [
        'type' => 'new',
        'card_token' => $form_state->getValue('cko_card_token'),
        'save_card' => $form_state->getValue('save_card'),
        'mada_bin' => $is_mada_card ? 'MADA' : '',
      ];
    }

    // Process 3d payment.
    if ($is_mada_card || $this->apiHelper->getCheckoutcomConfig('verify3dsecure')) {
      if (!empty($payment_method['payment_card']) && $payment_method['payment_card'] != 'new') {
        $card_data = [
          'type' => 'existing',
          'card_id' => $payment_method['payment_card_details']['payment_card_' . $payment_method['payment_card']]['card_id'],
          'cvv' => (int) $payment_method['payment_card_details']['payment_card_' . $payment_method['payment_card']]['cc_cvv'],
        ];
      }
      // Set the card related data in session to use it to prepare request data
      // for checkout.com api.
      $session = $this->currentRequest->getSession();
      $session->set('acq_checkout_com_card', $card_data);
    }
    else {
      // For 2d process MDC will handle the part of payment with card_token_id.
      $this->initiate2dPayment(
        ($is_new_card)
          ? $form_state->getValue('cko_card_token')
          : $payment_method['payment_card_details']['payment_card_' . $payment_method['payment_card']]['card_id']
      );
    }
  }

}
