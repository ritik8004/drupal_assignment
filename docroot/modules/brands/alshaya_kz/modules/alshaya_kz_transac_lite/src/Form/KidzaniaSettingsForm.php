<?php

namespace Drupal\alshaya_kz_transac_lite\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class KidzaniaSettingsForm.
 *
 * @package Drupal\alshaya_kz_transac_lite\Form
 */
class KidzaniaSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'kidzania_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_kz_transac_lite.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_kz_transac_lite.settings')
      ->set('service_url', $form_state->getValue('service_url'))
      ->set('user', $form_state->getValue('user'))
      ->set('passwd', $form_state->getValue('passwd'))
      ->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_kz_transac_lite.settings');
    $form['kidsoft_api_info'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Kidsoft API information'),
    ];
    $form['kidsoft_api_info']['service_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Service URL'),
      '#required' => TRUE,
      '#default_value' => $config->get('service_url'),
    ];
    $form['kidsoft_api_info']['user'] = [
      '#type' => 'password',
      '#title' => $this->t('User'),
      '#required' => TRUE,
    ];
    $form['kidsoft_api_info']['passwd'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

}
