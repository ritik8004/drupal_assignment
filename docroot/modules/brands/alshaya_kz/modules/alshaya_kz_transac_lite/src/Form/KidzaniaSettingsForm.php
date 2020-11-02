<?php

namespace Drupal\alshaya_kz_transac_lite\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Kidzania Settings Form.
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
      ->set('tnc_url', $form_state->getValue('tnc_url'))
      ->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_kz_transac_lite.settings');
    $form['kidzania'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Kidzania settings'),
    ];
    $form['kidzania']['service_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Kidsoft Service API URL'),
      '#required' => TRUE,
      '#default_value' => $config->get('service_url'),
    ];
    $form['kidzania']['tnc_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Terms & condition external url'),
      '#required' => TRUE,
      '#default_value' => $config->get('tnc_url'),
    ];

    return parent::buildForm($form, $form_state);
  }

}
