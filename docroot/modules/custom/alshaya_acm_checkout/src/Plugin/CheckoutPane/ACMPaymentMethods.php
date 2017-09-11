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
    /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
    $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');

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

    // Get the default plugin id.
    if ($default_plugin_id = $checkout_options_manager->getDefaultPaymentCode()) {
      // Add that as first one.
      if ($default_plugin_index = array_search($default_plugin_id, $payment_methods)) {
        unset($payment_methods[$default_plugin_index]);
        array_unshift($payment_methods, $default_plugin_id);
      }
    }

    $selected_plugin_id = $cart->getPaymentMethod(FALSE);

    if ($form_values = $form_state->getValue($pane_form['#parents'])) {
      $selected_plugin_id = $form_values['payment_options'];
    }

    // Avoid warnings because of empty array from getPaymentMethod.
    if (is_array($selected_plugin_id) && empty($selected_plugin_id)) {
      $selected_plugin_id = NULL;
    }

    // If selected method is no longer available, we start fresh.
    if ($selected_plugin_id && !in_array($selected_plugin_id, $payment_methods)) {
      $selected_plugin_id = '';
      $cart->setPaymentMethod(NULL, []);
    }

    // If there is no plugin selected, we select the default one.
    if (empty($selected_plugin_id) && $default_plugin_id) {
      $selected_plugin_id = reset($payment_methods);
    }

    // More than one payment method available, so build a form to let the user
    // chose the option they want. Once they select an option, an ajax callback
    // will rebuild the payment details and show the selected payment method
    // plugin form instead.
    $payment_options = [];
    $payment_has_descriptions = [];
    foreach ($payment_methods as $plugin_id) {
      if (!isset($plugins[$plugin_id])) {
        continue;
      }

      $payment_term = $checkout_options_manager->loadPaymentMethod($plugin_id, $plugins[$plugin_id]['label']);

      $description = '';
      if ($description_value = $payment_term->get('description')->getValue()) {
        $description = $description_value[0]['value'];
      }

      $current_language_id = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $default_language_id = \Drupal::languageManager()->getDefaultLanguage()->getId();

      $payment_translations = [];

      if ($current_language_id !== $default_language_id) {
        if ($payment_term->hasTranslation($default_language_id)) {
          $default_language_payment_term = $payment_term->getTranslation($default_language_id);
          $payment_translations[$payment_term->getName()] = $default_language_payment_term->getName();
        }
      }
      $payment_has_descriptions[$plugin_id] = (bool) $description;

      $method_name = '
        <div class="payment-method-name">
          <div class="method-title">' . $payment_term->getName() . '</div>
          <div class="method-side-info method-' . $plugin_id . '"></div>
          <div class="method-description">' . $description . '</div>
        </div>
      ';

      $payment_options[$plugin_id] = $method_name;
    }

    if ($payment_translations) {
      $pane_form['#attached']['drupalSettings']['alshaya_payment_options_translations'] = $payment_translations;
    }

    $pane_form['payment_options'] = [
      '#type' => 'radios',
      '#title' => $this->t('Payment Options'),
      '#options' => $payment_options,
      '#default_value' => $selected_plugin_id,
      '#ajax' => [
        'wrapper' => 'payment_details_wrapper',
        'callback' => [$this, 'rebuildPaymentDetails'],
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
      $cart->setPaymentMethod($selected_plugin_id);
      $plugin = $this->getPlugin($selected_plugin_id);
      $pane_form['payment_details_wrapper']['payment_method_' . $selected_plugin_id] += $plugin->buildPaneForm($pane_form['payment_details_wrapper']['payment_method_' . $selected_plugin_id], $form_state, $complete_form);
    }

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public static function rebuildPaymentDetails(array $pane_form, FormStateInterface $form_state) {
    return $pane_form['acm_payment_methods']['payment_details_wrapper'];
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
