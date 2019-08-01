<?php

namespace Drupal\acq_checkoutcom\Plugin\PaymentMethod;

use Drupal\acq_cart\CartInterface;
use Drupal\acq_checkoutcom\CheckoutComAPIWrapper;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodBase;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides the CheckoutCom payment method.
 *
 * @ACQPaymentMethod(
 *   id = "checkout_com",
 *   label = @Translation("Credit / Debit Card"),
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
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Form helper.
   *
   * @var \Drupal\acq_checkoutcom\CheckoutComFormHelper
   */
  protected $formHelper;

  /**
   * Payment types and associated callbacks.
   *
   * @var array
   */
  protected static $paymentTypes = [
    'new' => 'initiate2dPayment',
    'existing' => 'initiate2dPayment',
    'new_mada' => 'initiate3dSecurePayment',
    'existing_mada' => 'initiateStoredCardPayment',
  ];

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
    $this->currentUser = \Drupal::service('current_user');
    $this->entityTypeManager = \Drupal::service('entity_type.manager');
    $this->currentRequest = \Drupal::service('request_stack')->getCurrentRequest();
    $this->renderer = \Drupal::service('renderer');
    $this->formHelper = \Drupal::service('acq_checkoutcom.form_helper');
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->currentUser->isAuthenticated()
      ? $this->t('Saved Credit/Debit Cards')
      : $this->t('Credit/Debit Cards');
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaymentSummary() {
    return $this->t('checkout.com details here.');
  }

  /**
   * Return correct library based on correct environment.
   *
   * @return string
   *   Return string of library.
   */
  protected function getCheckoutKitLibrary() {
    return $this->apiHelper->getCheckoutcomConfig('environment') == 'sandbox'
      ? 'acq_checkoutcom/sandbox_kit'
      : 'acq_checkoutcom/live_kit';
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    // Get the default payment card to display form, to enter new card.
    $session = $this->currentRequest->getSession();
    $payment_card = $session->get('checkout_com_payment_card', 'new');

    $customer_stored_cards = [];
    // Display tokenised cards for logged in user.
    if ($this->currentUser->isAuthenticated()) {
      $user = $this->entityTypeManager->getStorage('user')->load(
        $this->currentUser->id()
      );
      $customer_stored_cards = $this->apiHelper->getCustomerCards($user);
      $customer_stored_cards = $this->apiHelper->filterExpiredCards($customer_stored_cards);
      $stored_cards_list = $this->prepareRadioOptionsMarkup($customer_stored_cards);

      $payment_card = empty($customer_stored_cards) ? 'new' : $payment_card;
      $values = $form_state->getValue('acm_payment_methods');
      if (!empty($values) && !empty($values['payment_details_wrapper']['payment_method_checkout_com']['payment_card'])) {
        $payment_card = $values['payment_details_wrapper']['payment_method_checkout_com']['payment_card'];
      }

      if (!empty($stored_cards_list)) {
        $pane_form['payment_card'] = [
          '#type' => 'radios',
          '#options' => $stored_cards_list + ['new' => $this->t('New Card')],
          '#default_value' => $payment_card,
          '#required' => TRUE,
          '#ajax' => [
            'callback' => [$this, 'renderSelectedCardFields'],
            'wrapper' => 'payment_details_checkout_com',
            'method' => 'replace',
            'effect' => 'fade',
          ],
        ];
      }
    }

    $pane_form['payment_card_details'] = [
      '#type' => 'container',
      '#id' => 'payment_details_checkout_com',
      '#attached' => [
        'library' => [
          $this->getCheckoutKitLibrary(),
          'acq_checkoutcom/checkoutcom.form',
        ],
      ],
    ];

    // Ask for cvv again when using existing card.
    if (!empty($payment_card) && $payment_card != 'new') {
      $pane_form['payment_card_details']['payment_card_' . $payment_card] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => ['payment_method_' . $payment_card],
        ],
      ];

      $pane_form['payment_card_details']['payment_card_' . $payment_card]['card_id'] = [
        '#type' => 'hidden',
        '#value' => $customer_stored_cards[$payment_card]['gateway_token'] ?? '',
      ];

      $pane_form['payment_card_details']['payment_card_' . $payment_card]['mada'] = [
        '#type' => 'hidden',
        '#value' => $customer_stored_cards[$payment_card]['mada'] ?? FALSE,
      ];
    }
    else {
      $pane_form['payment_card_details']['new'] = [
        '#type' => 'container',
        '#tree' => FALSE,
        '#id' => 'payment_method_new',
        '#attributes' => [
          'class' => ['payment_card_new'],
        ],
      ];

      $pane_form['payment_card_details']['new'] += $this->formHelper->newCardInfoForm($pane_form['payment_card_details']['new'], $form_state);
    }

    return $pane_form;
  }

  /**
   * Prepare markup to show for radio options.
   *
   * @param array $customer_stored_cards
   *   The array of stored cards.
   *
   * @return array
   *   Return array of prepared markup.
   *
   * @throws \Exception
   */
  protected function prepareRadioOptionsMarkup(array $customer_stored_cards): array {
    $stored_cards_list = [];
    foreach ($customer_stored_cards as $stored_card) {
      $build = [
        '#theme' => 'payment_card_teaser',
        '#card_info' => $stored_card,
      ];
      $stored_cards_list[$stored_card['public_hash']] = $this->renderer->render($build);
    }
    return $stored_cards_list;
  }

  /**
   * Ajax callback method to render cvv or display form to add new card.
   */
  public function renderSelectedCardFields(&$form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();

    if (empty($element)) {
      throw new NotFoundHttpException();
    }

    $values = $form_state->getValue('acm_payment_methods');
    if (!empty($values) && !empty($values['payment_details_wrapper']['payment_method_checkout_com']['payment_card'])) {
      $payment_card = $values['payment_details_wrapper']['payment_method_checkout_com']['payment_card'];
      $session = $this->currentRequest->getSession();
      $session->set('checkout_com_payment_card', $payment_card);
    }

    return $form['acm_payment_methods']['payment_details_wrapper']['payment_method_checkout_com']['payment_card_details'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaymentForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    // cko_card_token is not available in form state values.
    $payment_method = !empty($form_state->getValue($pane_form['#parents'])['payment_details_wrapper'])
      ? $form_state->getValue($pane_form['#parents'])['payment_details_wrapper']['payment_method_checkout_com']
      : ['payment_card' => 'new'];

    $is_new_card = (empty($payment_method['payment_card']) || $payment_method['payment_card'] == 'new') && !empty($form_state->getValue('cko_card_token'));

    $payment_card = $payment_method['payment_card'] ?? '';

    $is_mada_card = ($is_new_card == FALSE && isset($payment_method['payment_card_details']['payment_card_' . $payment_card]['mada']))
      ? $payment_method['payment_card_details']['payment_card_' . $payment_card]['mada']
      : FALSE;

    if ($is_new_card) {
      if ($this->checkoutComApi->isMadaEnabled()  && !empty($form_state->getValue('card_bin'))) {
        $is_mada_card = $this->checkoutComApi->isMadaBin($form_state->getValue('card_bin'));
      }
      $card = [
        'type' => 'new',
        'mada' => $is_mada_card,
        'card_save' => $form_state->getValue('save_card'),
        'card_token' => $form_state->getValue('cko_card_token'),
      ];
    }
    else {
      $card = [
        'type' => 'existing',
        'mada' => $is_mada_card,
        'card_hash' => $payment_card,
        'card_id' => $payment_method['payment_card_details']['payment_card_' . $payment_card]['card_id'],
      ];
    }

    $this->selectCheckoutComPayment($card);
  }

  /**
   * Process with correct payment type for given card info.
   *
   * @param array $card
   *   Card info.
   */
  protected function selectCheckoutComPayment(array $card) {
    $current_type = ($card['mada']) ? $card['type'] . '_mada' : $card['type'];

    call_user_func_array(
      [$this, static::$paymentTypes[$current_type]],
      [$card]
    );
  }

  /**
   * Process 2d payment for new card.
   *
   * @param array $card
   *   The array of card token containing type, card_hash or card_token.
   */
  protected function initiate2dPayment(array $card) {
    if ($card['type'] == 'existing') {
      $this->getCart()->setPaymentMethod($this->getId() . '_cc_vault', ['public_hash' => $card['card_hash']]);
    }
    else {
      $this->getCart()->setPaymentMethod($this->getId(), ['card_token_id' => $card['card_token']]);
    }
  }

  /**
   * Process 3d secure payment for new card.
   *
   * @param array $card
   *   The array of card info with card_token, mada and save.
   *
   * @throws \Exception
   */
  protected function initiate3dSecurePayment(array $card) {
    $cart = $this->getCart();
    $totals = $cart->totals();
    // Process 3d secure payment.
    $this->checkoutComApi->processCardPayment(
      $cart,
      [
        'value' => $this->checkoutComApi->getCheckoutAmount($totals['grand']),
        'cardToken' => $card['card_token'],
        'email' => $cart->customerEmail(),
        'udf3' => $card['card_save'] ? CheckoutComAPIWrapper::STORE_IN_VAULT_ON_SUCCESS : NULL,
        'udf1' => $card['mada'] ? 'MADA' : NULL,
      ]
    );
  }

  /**
   * Process 3d secure payment for stored card.
   *
   * @param array $card
   *   The array of card info with card_id and card_cvv.
   *
   * @throws \Exception
   */
  protected function initiateStoredCardPayment(array $card) {
    $cart = $this->getCart();
    $totals = $cart->totals();

    $this->checkoutComApi->processCardPayment($cart, [
      'cardId' => $card['card_id'],
      'value' => $this->checkoutComApi->getCheckoutAmount($totals['grand']),
      'email' => $cart->customerEmail(),
      'udf2' => CheckoutComAPIWrapper::CARD_ID_CHARGE,
    ]);
  }

}
