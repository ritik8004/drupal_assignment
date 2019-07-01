<?php

namespace Drupal\alshaya_acm_checkout\Plugin\PaymentMethod;

use Drupal\acq_checkoutcom\Plugin\PaymentMethod\CheckoutCom;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
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
          'alshaya_acm_checkout/checkout_com',
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
          $stored_cards_list[$stored_card['id']] = $this->renderer->render($build);
        }

        $payment_card = empty($stored_cards_list) ? $payment_card : $this->currentRequest->query->get('payment-card');
        $values = $form_state->getValue('acm_payment_methods');
        if (!empty($values) && !empty($values['payment_details_wrapper']['payment_method_checkout_com']['payment_card'])) {
          $payment_card = $values['payment_details_wrapper']['payment_method_checkout_com']['payment_card'];
        }

        if (!empty($stored_cards_list)) {
          $stored_cards_list += ['new' => $this->t('New Card')];
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
              $pane_form['payment_card_details']['payment_card_' . $payment_card]['cc_cvv'] = [
                '#type' => 'password',
                '#maxlength' => 4,
                '#title' => t('Security code (CVV)'),
                '#default_value' => '',
                '#required' => TRUE,
                '#prefix' => $cc_prefix,
                '#suffix' => $cc_suffix,
              ];
            }
            elseif ($payment_card == 'new') {
              $pane_form['payment_card_details']['payment_card_' . $payment_card]['new'] = [
                '#type' => 'container',
                '#attributes' => [
                  'id' => ['payment_card_' . $card_id],
                ],
              ];
              $pane_form['payment_card_details']['payment_card_' . $payment_card] += $this->formHelper->newCardInfoForm($pane_form['payment_card_details']['payment_card_' . $payment_card], $form_state);
            }
          }
        }
      }
    }

    if ($this->currentUser->isAnonymous() || empty($stored_cards_list)) {
      $pane_form['payment_card_details']['payment_card_new'] = [];
      $pane_form['payment_card_details']['payment_card_new'] += $this->formHelper->newCardInfoForm($pane_form['payment_card_details']['payment_card_new'], $form_state);
      $pane_form['payment_card_details']['cc_cvv']['#prefix'] = $cc_prefix;
      $pane_form['payment_card_details']['cc_cvv']['#suffix'] = $cc_suffix;
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

    $payment_card = $form_state->getValue($element['#parents']);
    $response = new AjaxResponse();
    $url = Url::fromRoute(
      'acq_checkout.form',
      ['step' => 'payment'],
      [
        'query' => [
          'payment-card' => $payment_card,
        ],
      ]
    );
    $response->addCommand(new InvokeCommand(NULL, 'showCheckoutLoader', []));
    $response->addCommand(new RedirectCommand($url->toString()));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaymentForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    // cko-card-token is not available in form state values.
    $inputs = $form_state->getUserInput();

    if ($this->apiHelper->getSubscriptionInfo('verify_3dsecure')) {
      $payment_method = $form_state->getValue($pane_form['#parents'])['payment_details_wrapper']['payment_method_checkout_com'];

      if ((empty($payment_method['payment_card']) || $payment_method['payment_card'] == 'new')
          && !empty($inputs['cko-card-token'])
      ) {
        $this->initiate3dSecurePayment(
          $inputs,
          $this->checkoutComApi->isMadaEnabled()
          ? $payment_method['payment_card_details']['payment_card_new']['card_bin']
          : NULL);
      }
      elseif (!empty($payment_method['payment_card']) && $payment_method['payment_card'] != 'new') {
        $this->initiateStoredCardPayment(
          $payment_method['payment_card'],
          (int) $payment_method['payment_card_details']['payment_card_' . $payment_method['payment_card']]['cc_cvv']
        );
      }
    }
    else {
      // For 2d process MDC will handle the part of payment with card_token_id.
      $this->getCart()->setPaymentMethod(
        $this->getId(),
        ['card_token_id' => $inputs['cko-card-token']]
      );
    }
  }

}
