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
    $config->set('checkout_terms_condition', $form_state->getValue('checkout_terms_condition'));
    $config->set('checkout_customer_service', $form_state->getValue('checkout_customer_service'));
    $config->set('click_collect_method_method_code', $form_state->getValue('click_collect_method_method_code'));
    $config->set('click_collect_method_carrier_code', $form_state->getValue('click_collect_method_carrier_code'));
    $config->set('checkout_display_magento_error', $form_state->getValue('checkout_display_magento_error'));

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

    $form['checkout_terms_condition'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Checkout Terms and Conditions'),
      '#required' => TRUE,
      '#default_value' => $config->get('checkout_terms_condition.value'),
    ];

    $form['checkout_customer_service'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Checkout Customer Service'),
      '#required' => TRUE,
      '#default_value' => $config->get('checkout_customer_service.value'),
    ];

    $form['click_collect_method_method_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Click and Collect delivery method - method code'),
      '#required' => TRUE,
      '#default_value' => $config->get('click_collect_method_method_code'),
    ];

    $form['click_collect_method_carrier_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Click and Collect delivery method - carrier code'),
      '#required' => TRUE,
      '#default_value' => $config->get('click_collect_method_carrier_code'),
    ];

    $form['checkout_display_magento_error'] = [
      '#type' => 'select',
      '#options' => [
        0 => $this->t('No - Generic message'),
        1 => $this->t('Yes'),
      ],
      '#title' => $this->t('Display error message from magento'),
      '#required' => TRUE,
      '#default_value' => $config->get('checkout_display_magento_error'),
    ];

    return $form;
  }

}
