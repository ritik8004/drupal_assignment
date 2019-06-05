<?php

namespace Drupal\acq_checkoutcom\Plugin\PaymentMethod;

use Drupal\acq_cart\CartInterface;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodBase;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides the CheckoutCom payment method.
 *
 * @ACQPaymentMethod(
 *   id = "checkout_com",
 *   label = @Translation("Checkout.com"),
 * )
 */
class CheckoutCom extends PaymentMethodBase implements PaymentMethodInterface {

  /**
   * Checkout.com api wrapper object.
   *
   * @var \Drupal\acq_checkoutcom\CheckoutComAPIWrapper
   */
  protected $checkoutComApi;

  /**
   * The api helper object.
   *
   * @var \Drupal\acq_checkoutcom\ApiHelper
   */
  protected $apiHelper;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * CheckoutCom constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\acq_cart\CartInterface $cart
   *   The shopping cart.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CartInterface $cart) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $cart);
    $this->checkoutComApi = \Drupal::service('acq_checkoutcom.checkout_api');
    $this->apiHelper = \Drupal::service('acq_checkoutcom.agent_api');
    $this->configFactory = \Drupal::service('config.factory');
    $this->currentUser = \Drupal::service('current_user');
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaymentSummary() {
    return $this->t('checkout.com details here.');
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $payment_card = 'new';
    // @todo: Replace this code with API call.
    if ($this->currentUser->isAuthenticated()) {
      $file = drupal_get_path('module', 'acq_checkoutcom') . '/saved_card_new.json';
      $data = file_get_contents($file);
      $existing_cards = !empty($data) ? json_decode($data) : [];

      $options = [];
      foreach ($existing_cards as $card) {
        $options[$card->id] = '
        <div class="saved-card">
          <div class="card-number">**** **** **** ' . $card->last4 . '</div>
          <div class="card-name">' . $card->name . '</div>
          <div class="card-expiry-date">' . "{$card->expiryMonth}/{$card->expiryYear}" . '</div>
        </div>
      ';
      }

      $payment_card = empty($options) ? $payment_card : \Drupal::requestStack()->getCurrentRequest()->query->get('payment-card');
      if (!empty($form_state->getValue('acm_payment_methods')['payment_details_wrapper']['payment_method_checkout_com']['payment_card'])) {
        $payment_card = $form_state->getValue('acm_payment_methods')['payment_details_wrapper']['payment_method_checkout_com']['payment_card'];
      }

      if (!empty($options)) {
        $pane_form['payment_card'] = [
          '#type' => 'radios',
          '#options' => $options + ['new' => $this->t('New Card')],
          '#default_value' => $payment_card,
          '#required' => TRUE,
          '#ajax' => [
            'callback' => [$this, 'renderSelectedCardFields'],
            'wrapper' => 'payment_details_checkout_com',
            'method' => 'replace',
            'effect' => 'fade',
          ],
          '#attached' => [
            'library' => ['acq_checkoutcom/checkoutcom.kit'],
          ],
        ];
      }
    }

    $pane_form['payment_details'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['payment_details_checkout_com'],
      ],
    ];

    if (!empty($payment_card) && $payment_card != 'new') {
      $pane_form['payment_details'][$payment_card]['cc_cvv'] = [
        '#type' => 'password',
        '#maxlength' => 4,
        '#title' => $this->t('Security code (CVV)'),
        '#default_value' => '',
        '#required' => TRUE,
        '#attributes' => [
          'class' => [
            'checkoutcom-credit-card-cvv-input',
            'checkoutcom-input',
          ],
          'autocomplete' => 'cc-csc',
          'data-checkout' => 'cvv',
        ],
      ];
    }
    elseif ($payment_card == 'new') {
      $pane_form['payment_details']['cc_number'] = [
        '#type' => 'tel',
        '#title' => $this->t('Credit Card Number'),
        '#default_value' => '',
        '#required' => TRUE,
        '#attributes' => [
          'autocomplete' => 'cc-number',
        ],
      ];

      $pane_form['payment_details']['cc_cvv'] = [
        '#type' => 'password',
        '#maxlength' => 4,
        '#title' => $this->t('Security code (CVV)'),
        '#default_value' => '',
        '#required' => TRUE,
        '#attributes' => [
          'class' => [
            'checkoutcom-credit-card-cvv-input',
            'checkoutcom-input',
          ],
          'autocomplete' => 'cc-csc',
          'data-checkout' => 'cvv',
        ],
      ];

      $pane_form['payment_details']['cc_exp_month'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Expiration Month'),
        '#required' => TRUE,
        '#attributes' => [
          'class' => [
            'checkoutcom-credit-card-exp-month-select',
            'checkoutcom-input',
          ],
          'data-checkout' => 'expiry-month',
        ],
      ];

      $pane_form['payment_details']['cc_exp_year'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Expiration Year'),
        '#required' => TRUE,
        '#attributes' => [
          'class' => [
            'checkoutcom-credit-card-exp-year-select',
            'checkoutcom-input',
          ],
          'data-checkout' => 'expiry-year',
        ],
      ];

      $pane_form['payment_details']['card_token'] = [
        '#type' => 'hidden',
        '#attributes' => [
          'id' => 'cardToken',
        ],
      ];

      $pane_form['payment_details']['save_card'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Save card for future use'),
      ];

      $debug = $this->configFactory->get('acq_checkoutcom.settings')->get('debug') ? 'true' : 'false';

      $string = "window.CKOConfig = {
        debugMode: {$debug},
        publicKey: '{$this->apiHelper->getSubscriptionKeys('public_key')}',
        ready: function (event) {
          CheckoutKit.monitorForm('.multistep-checkout', CheckoutKit.CardFormModes.CARD_TOKENISATION);
        },
        cardTokenised: function(event) {
          cardToken.value = event.data.cardToken
          document.getElementById('multistep-checkout').submit();
        },
        apiError: function (event) {
        }
      };";

      $pane_form['payment_details']['checkout_kit'] = [
        '#type' => 'html_tag',
        '#tag' => 'script',
        '#value' => $string,
      ];
    }

    return $pane_form;
  }

  /**
   * Ajax callback method to render cvv.
   */
  public function renderSelectedCardFields(&$form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();

    // Confirm it is a POST request and contains form data.
    if (empty($element)) {
      throw new NotFoundHttpException();
    }

    $acm_payment_methods = $form_state->getValue('acm_payment_methods');
    $response = new AjaxResponse();
    $url = Url::fromRoute(
      'acq_checkout.form',
      ['step' => 'payment'],
      [
        'query' => [
          'payment-card' => $acm_payment_methods['payment_details_wrapper']['payment_method_checkout_com']['payment_card'],
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
    // MDC will handle the part of payment just need to send card_token_id.
    $inputs = $form_state->getUserInput();
    // @todo: Replace this with APi call + cache / config.
    $process_type = '3d';
    if ($process_type == '2d') {
      $cart = $this->getCart();
      $cart->setPaymentMethod($this->getId(), ['card_token_id' => $inputs['cko-card-token']]);
    }
    elseif ($process_type == '3d') {
      $acm_payment_methods = $form_state->getValue('acm_payment_methods');
      $payment_card = $acm_payment_methods['payment_details_wrapper']['payment_method_checkout_com']['payment_card'];

      if ($payment_card == 'new' && !empty($inputs['cko-card-token'])) {
        $this->initiate3dSecurePayment($inputs);
      }
      else {
        $this->initiateStoredCardPayment($payment_card, (int) $form_state->getValue('acm_payment_methods')['payment_details_wrapper']['payment_method_checkout_com']['payment_details'][$payment_card]['cc_cvv']);
      }
    }
  }

  /**
   * Process 3d secure payment for new card.
   *
   * @param array $inputs
   *   The array of inputs from user.
   *
   * @throws \Exception
   */
  protected function initiate3dSecurePayment(array $inputs) {
    $cart = $this->getCart();
    $totals = $cart->totals();
    // Process 3d secure payment.
    $this->checkoutComApi->processCardPayment(
      $cart,
      [
        'value' => $totals['grand'] * 100,
        'cardToken' => $inputs['cko-card-token'],
        'email' => $cart->customerEmail(),
      ],
      TRUE
    );
  }

  /**
   * Process 3d secure payment for stored card.
   *
   * @param string $card_id
   *   The stored card unique id.
   * @param int $cvv
   *   The cvv of stored card.
   *
   * @throws \Exception
   */
  protected function initiateStoredCardPayment(string $card_id, int $cvv) {
    $cart = $this->getCart();
    $totals = $cart->totals();

    $this->checkoutComApi->processCardPayment($cart, [
      'cardId' => $card_id,
      'value' => $totals['grand'] * 100,
      'email' => $cart->customerEmail(),
      'cvv' => $cvv,
    ]);
  }

}
