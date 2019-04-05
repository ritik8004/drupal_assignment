<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

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
    $plugin_id = $this->getCheckoutHelper()->getSelectedPayment();
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
    /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
    $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');

    // @TODO: After the payment details are entered, prevent this form from
    // showing again if a user navigates back to this step or present an option
    // for the user to cancel the last payment method and enter a new one.
    $cart = $this->getCart();
    $plugins = $this->getPlugins();

    try {
      // Get available payment methods and compare to enabled payment method
      // plugins.
      $apiHelper = \Drupal::service('alshaya_acm.api_helper');
      $payment_methods = $apiHelper->getPaymentMethods();
      $payment_methods[] = 'checkout_com';
      $payment_methods = array_intersect($payment_methods, array_keys($plugins));
    }
    catch (\Exception $e) {
      drupal_set_message($e->getMessage(), 'error');
      $form_state->setErrorByName('custom', $e->getMessage());
      return $pane_form;
    }

    // Get the default plugin id.
    if ($default_plugin_id = $checkout_options_manager->getDefaultPaymentCode()) {
      // Add that as first one.
      if ($default_plugin_index = array_search($default_plugin_id, $payment_methods)) {
        unset($payment_methods[$default_plugin_index]);
        array_unshift($payment_methods, $default_plugin_id);
      }
    }

    $cart_payment = $this->getCheckoutHelper()->getSelectedPayment();

    // By default we use payment method from history, if not found we use from
    // cart as selected plugin.
    $selected_plugin_id = empty($cart_payment)
      ? $cart->getPaymentMethod(FALSE)
      : $cart_payment;

    // Avoid warnings because of empty array from getPaymentMethod.
    $selected_plugin_id = is_array($selected_plugin_id) ? '' : $selected_plugin_id;

    // Show payment method selected in form values if available as default.
    $form_values = $form_state->getValue($pane_form['#parents']);
    if ($form_values['payment_options']) {
      $selected_plugin_id = $form_values['payment_options'];
    }

    // If selected method is no longer available, we start fresh.
    if (!in_array($selected_plugin_id, $payment_methods)) {
      $selected_plugin_id = '';
    }

    // Select first payment method as selected if none available.
    $selected_plugin_id = empty($selected_plugin_id)
      ? reset($payment_methods)
      : $selected_plugin_id;

    // Since introduction of Surcharge, we inform Magento about selected
    // payment method even before user does place order. By default we select
    // a payment method, we inform Magento about that here.
    // @TODO: Re-visit when working on CORE-4483.
    if (empty($cart_payment)) {
      $isSurchargeEnabled = $this->getCheckoutHelper()->isSurchargeEnabled();

      if ($isSurchargeEnabled) {
        $this->getCheckoutHelper()->setBillingFromShipping(FALSE);
      }

      $this->getCheckoutHelper()->setSelectedPayment(
        $selected_plugin_id,
        [],
        $isSurchargeEnabled
      );
    }

    // More than one payment method available, so build a form to let the user
    // chose the option they want. Once they select an option, on reload the
    // page and we show the full form for that particular method.
    $payment_options = [];
    $payment_has_descriptions = [];
    $payment_translations = [];

    $languageManager = \Drupal::languageManager();

    $current_language_id = $languageManager->getCurrentLanguage()->getId();
    $default_language_id = $languageManager->getDefaultLanguage()->getId();

    foreach ($payment_methods as $plugin_id) {
      if (!isset($plugins[$plugin_id])) {
        continue;
      }

      $payment_term = $checkout_options_manager->loadPaymentMethod($plugin_id, $plugins[$plugin_id]['label']);

      $description = '';
      if ($description_value = $payment_term->get('description')->getValue()) {
        $description = $description_value[0]['value'];
      }

      if ($current_language_id !== $default_language_id) {
        if ($payment_term->hasTranslation($default_language_id)) {
          $default_language_payment_term = $payment_term->getTranslation($default_language_id);
          $payment_translations[$payment_term->getName()] = $default_language_payment_term->getName();
        }

        if ($plugin_id === 'cashondelivery') {
          $config = $languageManager->getLanguageConfigOverride(
            $current_language_id, 'alshaya_acm_checkout.settings'
          );
        }
      }
      elseif ($plugin_id === 'cashondelivery') {
        $config = \Drupal::config('alshaya_acm_checkout.settings');
      }

      $sub_title = '';

      if ($this->getCheckoutHelper()->isSurchargeEnabled() && $plugin_id === 'cashondelivery') {
        $surcharge = $cart->getExtension('surcharge');

        if ($surcharge) {
          $surcharge['amount'] = (float) $surcharge['amount'];
          if ($surcharge['amount'] > 0) {
            $surcharge_value = alshaya_acm_price_get_formatted_price($surcharge['amount']);
            $sub_title = $config->get('cod_surcharge_short_description');
            $description = $config->get('cod_surcharge_description');

            $sub_title = str_replace('[surcharge]', $surcharge_value, $sub_title);
            $description = str_replace('[surcharge]', $surcharge_value, $description);
          }
        }
      }

      $payment_has_descriptions[$plugin_id] = (bool) $description;

      $method_name = '
        <div class="payment-method-name">
          <div class="method-title">' . $payment_term->getName() . '</div>
          <div class="method-sub-title">' . $sub_title . '</div>
          <div class="method-side-info method-' . $plugin_id . '"></div>
          <div class="method-description">' . $description . '</div>
        </div>
      ';

      $payment_options[$plugin_id] = $method_name;
    }

    if (isset($payment_translations)) {
      $pane_form['#attached']['drupalSettings']['alshaya_payment_options_translations'] = $payment_translations;
    }

    $pane_form['payment_options'] = [
      '#type' => 'radios',
      '#title' => $this->t('Payment Methods'),
      '#options' => $payment_options,
      '#default_value' => $selected_plugin_id,
      '#ajax' => [
        'wrapper' => 'payment_details_wrapper',
        'url' => Url::fromRoute('alshaya_acm_checkout.select_payment_method'),
      ],
      '#attributes' => [
        'gtm-type' => 'cart-checkout-payment',
      ],
    ];

    $pane_form['payment_details_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['payment_details_wrapper'],
      ],
    ];

    foreach ($payment_options as $payment_plugin => $name) {
      $pane_form['payment_details_wrapper']['payment_method_' . $payment_plugin] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => ['payment_method_' . $payment_plugin],
        ],
      ];

      // To avoid issues in JS we always add the cybersource js library.
      if ($payment_plugin == 'cybersource') {
        $pane_form['payment_details_wrapper']['payment_method_' . $payment_plugin]['#attached']['library'][] = 'acq_cybersource/cybersource';
      }

      $title_class = [];
      $title_class[] = 'payment-plugin-wrapper-div';

      if ($payment_has_descriptions[$payment_plugin]) {
        $title_class[] = 'has-description';
      }

      if ($payment_plugin == $selected_plugin_id) {
        $title_class[] = 'plugin-selected';
      }

      $title = '<div id="payment_method_title_' . $payment_plugin . '"';
      $title .= ' class="' . implode(' ', $title_class) . '" ';
      $title .= ' data-value="' . $payment_plugin . '" ';
      $title .= '>';
      $title .= $name;
      $title .= '</div>';

      $pane_form['payment_details_wrapper']['payment_method_' . $payment_plugin]['title'] = [
        '#markup' => $title,
      ];
    }

    if ($selected_plugin_id) {
      $plugin = $this->getPlugin($selected_plugin_id);
      $pane_form['payment_details_wrapper']['payment_method_' . $selected_plugin_id] += $plugin->buildPaneForm($pane_form['payment_details_wrapper']['payment_method_' . $selected_plugin_id], $form_state, $complete_form);
    }

    return $pane_form;
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
      $this->getCheckoutHelper()->setSelectedPayment($payment_method, [], FALSE);

      $plugin = $this->getSelectedPlugin();
      $plugin->validatePaymentForm($pane_form, $form_state, $complete_form);
    }
    else {
      $form_state->setErrorByName('acm_payment_methods][payment_options', $this->t('Please select a payment option to continue.', [], ['context' => 'alshaya_static_text|empty_payment_option']));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $plugin = $this->getSelectedPlugin();
    $plugin->submitPaymentForm($pane_form, $form_state, $complete_form);

    // Set the payment method id in session as it is not available in cart.
    $session = \Drupal::request()->getSession();
    $session->set('selected_payment_method', $plugin->getId());
  }

  /**
   * Get checkout helper service object.
   *
   * @return \Drupal\alshaya_acm_checkout\CheckoutHelper
   *   Checkout Helper service object.
   */
  protected function getCheckoutHelper() {
    static $helper;

    if (empty($helper)) {
      /** @var \Drupal\alshaya_acm_checkout\CheckoutHelper $helper */
      $helper = \Drupal::service('alshaya_acm_checkout.checkout_helper');
    }

    return $helper;
  }

}
