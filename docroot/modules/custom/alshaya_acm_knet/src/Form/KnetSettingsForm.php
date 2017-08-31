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
      ->set('use_secure_response_url', $form_state->getValue('use_secure_response_url'))
      ->set('alias', $form_state->getValue('alias'))
      ->set('payment_pending', $form_state->getValue('payment_pending'))
      ->set('payment_processed', $form_state->getValue('payment_processed'))
      ->set('payment_failed', $form_state->getValue('payment_failed'))
      ->set('knet_language_code', $form_state->getValue('knet_language_code'))
      ->set('knet_currency_code', $form_state->getValue('knet_currency_code'))
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

    $form['use_secure_response_url'] = [
      '#type' => 'select',
      '#options' => [0 => $this->t('No'), 1 => $this->t('Yes')],
      '#description' => $this->t('Use secure (https) for response url. Should be enabled on production, requires valid SSL certificate.'),
      '#title' => $this->t('Use secure response url'),
      '#required' => TRUE,
      '#default_value' => $config->get('use_secure_response_url'),
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

    $form['knet_language_code'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Language code to be used for K-Net.'),
      '#title' => $this->t('K-Net Language code'),
      '#required' => TRUE,
      '#default_value' => $config->get('knet_language_code'),
    ];

    $form['knet_currency_code'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Currency code to be used for K-Net.'),
      '#title' => $this->t('K-Net Currency code'),
      '#required' => TRUE,
      '#default_value' => $config->get('knet_currency_code'),
    ];

    return parent::buildForm($form, $form_state);
  }

}
