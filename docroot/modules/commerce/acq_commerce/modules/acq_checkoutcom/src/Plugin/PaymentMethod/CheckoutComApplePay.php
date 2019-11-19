<?php

namespace Drupal\acq_checkoutcom\Plugin\PaymentMethod;

use Drupal\acq_cart\CartInterface;
use Drupal\acq_checkout\Event\AcqCheckoutPaymentFailedEvent;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodBase;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the apple pay payment method.
 *
 * @ACQPaymentMethod(
 *   id = "checkout_com_applepay",
 *   label = @Translation("Apple Pay"),
 * )
 */
class CheckoutComApplePay extends PaymentMethodBase implements PaymentMethodInterface {

  /**
   * The api helper object.
   *
   * @var \Drupal\acq_checkoutcom\ApiHelper
   */
  protected $apiHelper;

  /**
   * Form helper.
   *
   * @var \Drupal\acq_checkoutcom\CheckoutComFormHelper
   */
  protected $formHelper;

  /**
   * CheckoutComApplePay constructor.
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
    $this->apiHelper = \Drupal::service('acq_checkoutcom.agent_api');
    $this->formHelper = \Drupal::service('acq_checkoutcom.form_helper');
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Apple Pay');
  }

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    // @todo: add check for "merchantIdentifier" once we receive in API.
    return $this->apiHelper->getCheckoutcomConfig('applepay_enabled');
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaymentSummary() {
    return $this->t('Proceed with apple pay.');
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $settings = $this->formHelper->getApplePayConfig();
    $settings['runningTotal'] = $this->getCart()->totals()['grand'];
    $settings['applePayAllowedIn'] = $this->apiHelper->getApplePayAllowedIn();

    $complete_form['actions']['apple_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['actions-toolbar', 'apple-pay-wrapper'],
      ],
      '#attached' => [
        'library' => [
          'acq_checkoutcom/applepay',
        ],
        'drupalSettings' => [
          'checkoutCom' => $settings,
        ],
      ],
    ];

    $lang = strtolower(\Drupal::languageManager()->getCurrentLanguage()->getId());
    $text = $this->t('Buy with');
    $complete_form['actions']['apple_wrapper']['apple_pay'] = [
      '#type' => 'inline_template',
      '#template' => '
        <button id="ckoApplePayButton" lang="' . $lang . '"
          class="apple-pay-button apple-pay-button-with-text apple-pay-button-black-with-text action primary checkout form-submit">
          <span class="text">' . $text . '</span>
          <span class="logo"></span>
        </button>
        <div data-bind="visible: launchApplePay()"></div>
      ',
    ];

    $complete_form['actions']['next']['#attributes']['class'][] = 'hidden';

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaymentForm(array &$pane_form,
                                      FormStateInterface $form_state,
                                      array &$complete_form) {

    if (!$form_state->isSubmitted() || $form_state->getErrors()) {
      return;
    }

    $cart = $this->getCart();
    $payload = $cart->getPaymentMethodData();

    if (empty($payload) || empty($payload['publicKeyHash'])) {
      $this->messenger()->addError($this->t('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.'));
      $form_state->setError($complete_form['actions']['apple_wrapper']['apple_pay'], $this->t('Payment failed'));

      $event = new AcqCheckoutPaymentFailedEvent('checkout_com_applepay', 'Invalid data in payload or empty publicKeyHash.');
      \Drupal::service('event_dispatcher')->dispatch(AcqCheckoutPaymentFailedEvent::EVENT_NAME, $event);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaymentForm(array &$pane_form,
                                    FormStateInterface $form_state,
                                    array &$complete_form) {
    // Do nothing, we have already set payment method and data.
  }

}
