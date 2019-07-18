<?php

namespace Drupal\acq_checkoutcom\Plugin\PaymentMethod;

use Drupal\acq_cart\CartInterface;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodBase;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the apple pay payment method.
 *
 * @ACQPaymentMethod(
 *   id = "checkout_com_applepay",
 *   label = @Translation("Checkout.com apple pay"),
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
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Apple pay');
  }

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
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
    $settings = [
      'merchantIdentifier' => 'merchant.com.checkoutmdcdemo.alshaya',
      'buttonStyle' => 'black',
      'supportedNetworks' => 'visa,masterCard,amex',
      'merchantCapabilities' => 'supports3DS,supportsCredit,supportsDebit',
      'supportedCountries' => 'KW',
      'runningTotal' => $this->getCart()->totals()['grand'],
      'storeName' => \Drupal::config('system.site')->get('name'),
      'countryId' => \Drupal::config('system.date')->get('country.default'),
      'currencyCode' => \Drupal::config('acq_commerce.currency')->get('iso_currency_code'),
    ];

    $complete_form['actions']['apple_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['actions-toolbar'],
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

    $complete_form['actions']['apple_wrapper']['apple_pay'] = [
      '#type' => 'button',
      '#title' => 'Apple pay',
      '#id' => 'ckoApplePayButton',
      '#attributes' => [
        'class' => [
          'apple-pay-button',
          'action',
          'primary',
          'checkout',
        ],
      ],
    ];

    $complete_form['actions']['apple_wrapper']['launch_markup'] = [
      '#type' => 'inline_template',
      '#template' => '
      <p style="display:none;" id="got_notactive">ApplePay is possible on this browser, but not currently activated.</p>
      <p style="display:none;" id="notgot">ApplePay is not available on this browser</p>
      <div data-bind="visible: launchApplePay()"></div>
    ',
      '#context' => [],
    ];

    return $pane_form;
  }

}
