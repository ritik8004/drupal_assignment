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
    $settings = $this->formHelper->getApplePayConfig();
    $settings['runningTotal'] = $this->getCart()->totals()['grand'];

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
          'apple-pay-button-with-text',
          'apple-pay-button-white-with-text',
          'action',
          'primary',
          'checkout',
        ],
        'lang' => \Drupal::languageManager()->getCurrentLanguage()->getId(),
      ],
      '#value' => '<span class="text">' . $this->t('Buy with') . '</span><span class="logo"></span>',
    ];

    $complete_form['actions']['apple_wrapper']['launch_markup'] = [
      '#type' => 'inline_template',
      '#template' => '<div data-bind="visible: launchApplePay()"></div>',
      '#context' => [],
    ];

    $complete_form['actions']['next']['#access'] = FALSE;

    return $pane_form;
  }

}
