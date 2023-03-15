<?php

namespace Drupal\acq_payment\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure BNPL Payment methods Form.
 */
class BnplPaymentMethodsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'acq_payment_bnpl_payment_config';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['acq_payment.bnpl_payment_config'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('acq_payment.bnpl_payment_config')
      ->set('bnpl_payment_methods', $form_state->getValue('bnpl_payment_methods'))
      ->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $bnpl_config = $this->config('acq_payment.bnpl_payment_config');

    $form['bnpl_payment_methods'] = [
      '#type' => 'textfield',
      '#title' => $this->t('BNPL Payment Methods'),
      '#description' => $this->t('A comma-separated list of the names of the payment methods.'),
      '#default_value' => $bnpl_config->get('bnpl_payment_methods'),
    ];

    return parent::buildForm($form, $form_state);
  }

}
