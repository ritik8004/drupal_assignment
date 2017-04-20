<?php

namespace Drupal\alshaya_acm_checkout\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CheckoutSettingsForm.
 */
class CheckoutSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'checkout_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_acm_checkout.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm_checkout.settings');
    $config->set('checkout_guest_summary', $form_state->getValue('checkout_guest_summary'));
    $config->set('checkout_guest_email_usage', $form_state->getValue('checkout_guest_email_usage'));
    $config->set('checkout_guest_login', $form_state->getValue('checkout_guest_login'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_acm_checkout.settings');

    $form['checkout_guest_summary'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Checkout as guest summary'),
      '#required' => TRUE,
      '#default_value' => $config->get('checkout_guest_summary.value'),
    ];

    $form['checkout_guest_email_usage'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Guest email usage description'),
      '#required' => TRUE,
      '#default_value' => $config->get('checkout_guest_email_usage.value'),
    ];

    $form['checkout_guest_login'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Checkout login help'),
      '#required' => TRUE,
      '#default_value' => $config->get('checkout_guest_login.value'),
    ];

    return $form;
  }

}
