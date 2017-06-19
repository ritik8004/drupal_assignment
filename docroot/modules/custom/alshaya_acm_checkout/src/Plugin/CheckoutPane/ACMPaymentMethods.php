<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the contact information pane.
 *
 * @ACQCheckoutPane(
 *   id = "acm_payment_methods",
 *   label = @Translation("Payment Methods"),
 *   defaultStep = "payment",
 *   wrapperElement = "fieldset",
 * )
 */
class ACMPaymentMethods extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * Gets all of the payment method plugins available.
   */
  public function getPlugins() {
    $paymentMethodManager = \Drupal::service('plugin.manager.acq_payment_method');
    return $paymentMethodManager->getDefinitions();
  }

  /**
   * Gets a specific payment method plugin.
   *
   * @param string $plugin_id
   *   The plugin id.
   */
  public function getPlugin($plugin_id) {
    $cart = $this->getCart();
    $paymentMethodManager = \Drupal::service('plugin.manager.acq_payment_method');
    return $paymentMethodManager->createInstance($plugin_id, [], $cart);
  }

  /**
   * Gets the customer selected plugin.
   */
  public function getSelectedPlugin() {
    $cart = $this->getCart();
    $plugin_id = $cart->getPaymentMethod(FALSE);
    return $this->getPlugin($plugin_id);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'weight' => 2,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneSummary() {
    $plugin = $this->getSelectedPlugin();
    return $plugin->buildPaymentSummary();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $config = \Drupal::config('alshaya_acm_checkout.settings');

    // @TODO: After the payment details are entered, prevent this form from
    // showing again if a user navigates back to this step or present an option
    // for the user to cancel the last payment method and enter a new one.
    $cart = $this->getCart();
    $plugins = $this->getPlugins();

    // Get available payment methods and compare to enabled payment method
    // plugins.
    $apiWrapper = $this->getApiWrapper();
    $payment_methods = $apiWrapper->getPaymentMethods($cart->id());
    $payment_methods = array_intersect($payment_methods, array_keys($plugins));

    // Get the methods allowed for selected delivery method.
    $shipping_method = $cart->getShippingMethodAsString();
    $allowed_methods = $shipping_method == $config->get('click_collect_method') ? $config->get('payments_click_collect') : $config->get('payments_home_delivery');
    $allowed_methods = explode(',', $allowed_methods);

    // Update payment methods to show only those which are allowed for selected
    // delivery method.
    $payment_methods = array_intersect($payment_methods, $allowed_methods);

    $selected_plugin_id = $cart->getPaymentMethod(FALSE);

    // Avoid warnings because of empty array from getPaymentMethod.
    if (is_array($selected_plugin_id) && empty($selected_plugin_id)) {
      $selected_plugin_id = NULL;
    }

    // If selected method is no longer available, we start fresh.
    if ($selected_plugin_id && !in_array($selected_plugin_id, $payment_methods)) {
      $selected_plugin_id = '';
      $cart->setPaymentMethod(NULL, []);
    }

    // Only one payment method available, load and return that methods plugin.
    if (count($payment_methods) === 1) {
      $plugin_id = reset($payment_methods);
      $cart->setPaymentMethod($plugin_id);
      $plugin = $this->getPlugin($plugin_id);
      $pane_form += $plugin->buildPaneForm($pane_form, $form_state, $complete_form);
      return $pane_form;
    }
    elseif (empty($selected_plugin_id)) {
      $default_plugin_id = $config->get('payments_default');

      if (in_array($default_plugin_id, $payment_methods)) {
        $selected_plugin_id = $default_plugin_id;
      }
    }

    // More than one payment method available, so build a form to let the user
    // chose the option they want. Once they select an option, an ajax callback
    // will rebuild the payment details and show the selected payment method
    // plugin form instead.
    $payment_options = [];
    foreach ($payment_methods as $plugin_id) {
      if (!isset($plugins[$plugin_id])) {
        continue;
      }
      $payment_options[$plugin_id] = $plugins[$plugin_id]['label'];
    }

    $pane_form['payment_options'] = [
      '#type' => 'radios',
      '#title' => $this->t('Payment Options'),
      '#options' => $payment_options,
      '#default_value' => $selected_plugin_id,
      '#ajax' => [
        'wrapper' => 'payment_details',
        'callback' => [$this, 'rebuildPaymentDetails'],
      ],
      '#attributes' => [
        'gtm-type' => 'cart-checkout-payment',
      ],
    ];

    if ($selected_plugin_id) {
      $cart->setPaymentMethod($selected_plugin_id);
      $plugin = $this->getPlugin($selected_plugin_id);
      $pane_form += $plugin->buildPaneForm($pane_form, $form_state, $complete_form);
    }
    else {
      $pane_form['payment_details'] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => ['payment_details'],
        ],
      ];
    }

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public static function rebuildPaymentDetails(array $pane_form, FormStateInterface $form_state) {
    return $pane_form['acm_payment_methods']['payment_details'];
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);
    $payment_method = isset($values['payment_options']) ? $values['payment_options'] : NULL;
    if ($payment_method) {
      // Setting the payment method in the ajax callback is too late, but
      // validation runs before the ajax method is called, so we can get the
      // value selected by the user and update the cart in here so that when
      // the form rebuilds it shows the correct payment plugin form.
      $cart = $this->getCart();
      $cart->setPaymentMethod($payment_method);

      $plugin = $this->getSelectedPlugin();
      $plugin->validatePaymentForm($pane_form, $form_state, $complete_form);
    }
    else {
      $form_state->setErrorByName('acm_payment_methods][payment_options', $this->t('Please select a payment option to continue.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $plugin = $this->getSelectedPlugin();
    $plugin->submitPaymentForm($pane_form, $form_state, $complete_form);
  }

}
