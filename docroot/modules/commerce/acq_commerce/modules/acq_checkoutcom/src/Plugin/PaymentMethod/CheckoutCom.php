<?php

namespace Drupal\acq_checkoutcom\Plugin\PaymentMethod;

use Drupal\acq_cart\CartInterface;
use Drupal\acq_checkoutcom\CheckoutComAPIWrapper;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodBase;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodInterface;
use Drupal\Core\Ajax\AjaxResponse;
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
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    // Set the default payment card to display form to enter new card.
    $payment_card = 'new';

    // Display tokenised cards for logged in user.
    if ($this->currentUser->isAuthenticated()) {
      $user = $this->entityTypeManager->getStorage('user')->load(
        $this->currentUser->id()
      );
      $customer_stored_cards = $this->apiHelper->getCustomerCards($user);

      if (!empty($customer_stored_cards)) {
        $stored_cards_list = [];
        foreach ($customer_stored_cards as $stored_card) {
          $build = [
            '#theme' => 'payment_card_teaser',
            '#card_info' => $stored_card,
            '#user' => $user,
          ];
          $stored_cards_list[$stored_card['public_hash']] = $this->renderer->render($build);
        }
      }

      $payment_card = empty($stored_cards_list) ? $payment_card : $this->currentRequest->query->get('payment-card');
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

    $pane_form['payment_details'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['payment_details_checkout_com'],
      ],
    ];

    // Ask for cvv again when using existing card.
    if (!empty($payment_card) && $payment_card != 'new') {

      $pane_form['payment_details'][$payment_card]['card_id'] = [
        '#type' => 'hidden',
        '#value' => $customer_stored_cards[$payment_card]['gateway_token'],
      ];

      $pane_form['payment_details'][$payment_card]['cc_cvv'] = [
        '#type' => 'password',
        '#maxlength' => 4,
        '#title' => $this->t('Security code (CVV)'),
        '#default_value' => '',
        '#required' => TRUE,
      ];
    }
    elseif ($payment_card == 'new') {
      $pane_form['payment_details'] += $this->formHelper->newCardInfoForm($pane_form['payment_details'], $form_state);
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
    $response->addCommand(new RedirectCommand($url->toString()));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaymentForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    // cko-card-token is not available in form state values.
    $payment_method = $form_state->getValue($pane_form['#parents'])['payment_details_wrapper']['payment_method_checkout_com'];;

    $save_card = isset($payment_method['payment_details']['save_card'])
      ? $payment_method['payment_details']['save_card']
      : FALSE;
    if ($this->apiHelper->getCheckoutcomConfig('verify3dsecure')) {
      $payment_card = $payment_method['payment_card'];

      if ((empty($payment_card) || $payment_card == 'new') && !empty($payment_method['payment_details']['cko_card_token'])) {
        $this->initiate3dSecurePayment(
          $payment_method['payment_details']['cko_card_token'],
          $this->checkoutComApi->isMadaEnabled()
            ? $payment_method['payment_details']['card_bin']
            : NULL,
          $save_card
        );
      }
      else {
        $this->initiateStoredCardPayment(
          $payment_method['payment_details'][$payment_card]['card_id'],
          (int) $payment_method['payment_details'][$payment_card]['cc_cvv']
        );
      }
    }
    else {
      // For 2d process MDC will handle the part of payment with card_token_id.
      $this->initiate2dPayment(
        $payment_method['payment_details']['cko_card_token'],
        $save_card
      );
    }
  }

  /**
   * Process 2d payment for new card.
   *
   * @param string $card_token
   *   The card token from user.
   * @param bool $save
   *   (Optional) true to save card, otherwise false.
   *
   * @throws \Exception
   */
  protected function initiate2dPayment(string $card_token, $save = FALSE) {
    $this->getCart()->setPaymentMethod(
      $this->getId(),
      [
        'card_token_id' => $card_token,
        'udf3' => $save ? CheckoutComAPIWrapper::STORE_IN_VAULT_ON_SUCCESS : '',
      ]
    );
  }

  /**
   * Process 3d secure payment for new card.
   *
   * @param string $card_token
   *   The card token from user.
   * @param string|null $card_bin
   *   (Optional) The card bin.
   * @param bool $save
   *   (Optional) true to save card, otherwise false.
   *
   * @throws \Exception
   */
  protected function initiate3dSecurePayment(string $card_token, $card_bin = NULL, $save = FALSE) {
    $cart = $this->getCart();
    $totals = $cart->totals();
    // Process 3d secure payment.
    $this->checkoutComApi->processCardPayment(
      $cart,
      [
        'value' => $totals['grand'] * CheckoutComAPIWrapper::MULTIPLY_HUNDREDS,
        'cardToken' => $card_token,
        'email' => $cart->customerEmail(),
        'udf3' => $save ? CheckoutComAPIWrapper::STORE_IN_VAULT_ON_SUCCESS : '',
        'udf1' => !empty($card_bin) && $this->checkoutComApi->isMadaBin($card_bin)
        ? 'MADA'
        : '',
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
      'value' => $totals['grand'] * CheckoutComAPIWrapper::MULTIPLY_HUNDREDS,
      'email' => $cart->customerEmail(),
      'cvv' => $cvv,
    ]);
  }

}
