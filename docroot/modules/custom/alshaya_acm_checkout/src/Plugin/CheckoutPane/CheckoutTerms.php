<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Terms and Conditions pane on Payment page.
 *
 * @ACQCheckoutPane(
 *   id = "checkout_terms",
 *   label = @Translation("Terms & Conditions"),
 *   defaultStep = "payment",
 *   wrapperElement = "fieldset",
 * )
 */
class CheckoutTerms extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'weight' => 20,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $config = \Drupal::config('alshaya_acm_checkout.settings');

    $pane_form['terms'] = [
      '#type' => 'checkbox',
      '#title' => $config->get('checkout_terms_condition.value'),
    ];

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);
    if (empty($values['terms'])) {
      $form_state->setErrorByName('checkout_terms][terms', $this->t('Please agree to the Terms and Conditions.', [], ['context' => 'alshaya_static_text|agree_terms_and_condition']));
    }
  }

}
