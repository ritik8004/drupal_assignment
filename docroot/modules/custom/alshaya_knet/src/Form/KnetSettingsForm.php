<?php

namespace Drupal\alshaya_knet\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Knet Settings Form.
 */
class KnetSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_knet_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_knet.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_knet.settings')
      ->set('use_new_knet_toolkit', $form_state->getValue('use_new_knet_toolkit'))
      ->set('knet_url', $form_state->getValue('knet_url'))
      ->set('resource_path', $form_state->getValue('resource_path'))
      ->set('use_secure_response_url', $form_state->getValue('use_secure_response_url'))
      ->set('alias', $form_state->getValue('alias'))
      ->set('knet_language_code', $form_state->getValue('knet_language_code'))
      ->set('knet_currency_code', $form_state->getValue('knet_currency_code'))
      ->set('knet_udf5_prefix', $form_state->getValue('knet_udf5_prefix'))
      ->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_knet.settings');

    $form['use_new_knet_toolkit'] = [
      '#type' => 'checkbox',
      '#description' => $this->t('If checked, then new K-Net toolkit will be used and requires tranportal id, password and key.'),
      '#title' => $this->t('Use new K-Net toolkit.'),
      '#default_value' => $config->get('use_new_knet_toolkit'),
    ];

    $form['new_toolkit_container'] = [
      '#type' => 'fieldset',
      '#states' => [
        'visible' => [
          [':input[name="use_new_knet_toolkit"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['new_toolkit_container']['knet_url'] = [
      '#type' => 'textfield',
      '#description' => $this->t('K-Net PG url where the user will be redirected for the payment.'),
      '#title' => $this->t('K-Net url'),
      '#default_value' => $config->get('knet_url'),
    ];

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

    $form['knet_udf5_prefix'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Prefix for the UDF5.'),
      '#title' => $this->t('UDF5 prefix'),
      '#default_value' => $config->get('knet_udf5_prefix'),
    ];

    return parent::buildForm($form, $form_state);
  }

}
