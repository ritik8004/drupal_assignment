<?php

namespace Drupal\acq_checkoutcom\Plugin\PaymentMethod;

use Drupal\acq_cart\CartInterface;
use Drupal\acq_checkoutcom\CheckoutComCardInfoFormTrait;
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

  use CheckoutComCardInfoFormTrait;

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
      $existing_cards = $this->apiHelper->getCustomerCards(
        $this->entityTypeManager->getStorage('user')->load(
          $this->currentUser->id()
        )
      );

      if (!empty($existing_cards)) {
        $options = [];
        foreach ($existing_cards as $card) {
          $build = [
            '#theme' => 'payment_card_teaser',
            '#card_info' => $card,
          ];
          $options[$card['id']] = $this->renderer->render($build);
        }
      }

      $payment_card = empty($options) ? $payment_card : $this->currentRequest->query->get('payment-card');
      $values = $form_state->getValue('acm_payment_methods');
      if (!empty($values) && !empty($values['payment_details_wrapper']['payment_method_checkout_com']['payment_card'])) {
        $payment_card = $values['payment_details_wrapper']['payment_method_checkout_com']['payment_card'];
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
      $pane_form['payment_details'][$payment_card]['cc_cvv'] = [
        '#type' => 'password',
        '#maxlength' => 4,
        '#title' => $this->t('Security code (CVV)'),
        '#default_value' => '',
        '#required' => TRUE,
      ];
    }
    elseif ($payment_card == 'new') {
      $pane_form['payment_details'] += CheckoutComCardInfoFormTrait::newCardInfoForm($pane_form['payment_details'], $form_state);
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
      $payment_card = $payment_method['payment_card'];

      if ((empty($payment_card) || $payment_card == 'new') && !empty($inputs['cko-card-token'])) {
        $this->initiate3dSecurePayment($inputs);
      }
      else {
        $this->initiateStoredCardPayment(
          $payment_card,
          (int) $payment_method['payment_details'][$payment_card]['cc_cvv']
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
