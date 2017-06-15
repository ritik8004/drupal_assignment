<?php

namespace Drupal\alshaya_acm_knet\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class KnetSettingsForm.
 */
class KnetSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_acm_knet_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_acm_knet.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_acm_knet.settings')
      ->set('resource_path', $form_state->getValue('resource_path'))
      ->set('alias', $form_state->getValue('alias'))
      ->set('payment_pending', $form_state->getValue('payment_pending'))
      ->set('payment_processed', $form_state->getValue('payment_processed'))
      ->set('payment_failed', $form_state->getValue('payment_failed'))
      ->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm_knet.settings');

    $form['resource_path'] = [
      '#type' => 'textfield',
      '#description' => $this->t('K-Net resources absolute path on server.'),
      '#title' => $this->t('Resource path'),
      '#required' => TRUE,
      '#default_value' => $config->get('resource_path'),
    ];

    $form['alias'] = [
      '#type' => 'textfield',
      '#description' => $this->t('K-Net key to use for decrypting zip.'),
      '#title' => $this->t('Alias'),
      '#required' => TRUE,
      '#default_value' => $config->get('alias'),
    ];

    $form['payment_pending'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Order status to check if payment processing is required/pending.'),
      '#title' => $this->t('Payment pending'),
      '#required' => TRUE,
      '#default_value' => $config->get('payment_pending'),
    ];

    $form['payment_processed'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Order status to set when payment succeeds.'),
      '#title' => $this->t('Payment processed'),
      '#required' => TRUE,
      '#default_value' => $config->get('payment_processed'),
    ];

    $form['payment_failed'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Order status to set when payment fails.'),
      '#title' => $this->t('Payment failed'),
      '#required' => TRUE,
      '#default_value' => $config->get('payment_failed'),
    ];

    return parent::buildForm($form, $form_state);
  }

}
