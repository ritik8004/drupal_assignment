<?php

namespace Drupal\alshaya_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AlshayaApiSettingsForm.
 */
class AlshayaApiSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_api_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_api.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_api.settings');
    $config->set('store_id', $form_state->getValue('store_id'));
    $config->set('magento_host', $form_state->getValue('magento_host'));
    $config->set('magento_api_base', $form_state->getValue('magento_api_base'));
    $config->set('verify_ssl', $form_state->getValue('verify_ssl'));
    $config->set('token_cache_time', $form_state->getValue('token_cache_time'));
    $config->set('username', $form_state->getValue('username'));
    $config->set('password', $form_state->getValue('password'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_acm_product.settings');

    $form['store_id'] = [
      '#type' => 'number',
      '#title' => $this->t('Store id'),
      '#required' => TRUE,
      '#default_value' => $config->get('store_id'),
    ];

    $form['magento_host'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Host'),
      '#default_value' => $config->get('magento_host'),
    ];

    $form['magento_api_base'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Base'),
      '#default_value' => $config->get('magento_api_base'),
    ];

    $form['verify_ssl'] = [
      '#type' => 'select',
      '#title' => $this->t('Verify SSL'),
      '#required' => TRUE,
      '#default_value' => $config->get('verify_ssl'),
      '#options' => [0 => $this->t('Disable'), 1 => $this->t('Enable')],
    ];

    $form['token_cache_time'] = [
      '#type' => 'number',
      '#title' => $this->t('Token cache time'),
      '#required' => TRUE,
      '#default_value' => $config->get('token_cache_time'),
    ];

    $form['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#default_value' => $config->get('username'),
    ];

    $form['password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Password'),
      '#default_value' => $config->get('password'),
    ];

    return $form;
  }

}
