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
    $config->set('magento_host', $form_state->getValue('magento_host'));
    $config->set('magento_lang_prefix', $form_state->getValue('magento_lang_prefix'));
    $config->set('magento_api_base', $form_state->getValue('magento_api_base'));
    $config->set('verify_ssl', $form_state->getValue('verify_ssl'));
    $config->set('consumer_key', $form_state->getValue('consumer_key'));
    $config->set('consumer_secret', $form_state->getValue('consumer_secret'));
    $config->set('access_token', $form_state->getValue('access_token'));
    $config->set('access_token_secret', $form_state->getValue('access_token_secret'));

    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_api.settings');

    $form['magento_host'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Host'),
      '#default_value' => $config->get('magento_host'),
    ];

    $form['magento_lang_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Language prefix'),
      '#required' => FALSE,
      '#default_value' => $config->get('magento_lang_prefix'),
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

    $form['consumer_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Consumer Key'),
      '#required' => TRUE,
      '#default_value' => $config->get('consumer_key'),
    ];

    $form['consumer_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Consumer Secret'),
      '#required' => TRUE,
      '#default_value' => $config->get('consumer_secret'),
    ];

    $form['access_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Access Token'),
      '#required' => TRUE,
      '#default_value' => $config->get('access_token'),
    ];

    $form['access_token_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Access Token Secret'),
      '#required' => TRUE,
      '#default_value' => $config->get('access_token_secret'),
    ];

    return $form;
  }

}
