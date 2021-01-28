<?php

namespace Drupal\alshaya_olapic\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Alshaya Olapic Config settings.
 */
class OlapicConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'olapic_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'alshaya_olapic.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_olapic.settings');
    $form['development_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Development mode'),
      '#required' => TRUE,
      '#default_value' => $config->get('development_mode') ?? 0,
      '#options' => [0 => 'No', 1 => 'Yes'],
    ];
    $form['olapic_en_data_apikey'] = [
      '#title' => $this->t('Olapic En Data Apikey'),
      '#type' => 'textfield',
      '#default_value' => $config->get('olapic_en_data_apikey') ?? '',
      '#required' => TRUE,
    ];
    $form['olapic_ar_data_apikey'] = [
      '#title' => $this->t('Olapic Ar Data Apikey'),
      '#type' => 'textfield',
      '#default_value' => $config->get('olapic_ar_data_apikey') ?? '',
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('alshaya_olapic.settings')
      ->set('development_mode', $form_state->getValue('development_mode'))
      ->set('olapic_en_data_apikey', $form_state->getValue('olapic_en_data_apikey'))
      ->set('olapic_ar_data_apikey', $form_state->getValue('olapic_ar_data_apikey'))
      ->set('olapic_home_en_data_instance', $form_state->getValue('olapic_home_en_data_instance'))
      ->set('olapic_home_ar_data_instance', $form_state->getValue('olapic_home_ar_data_instance'))
      ->set('olapic_gallery_en_data_instance', $form_state->getValue('olapic_gallery_en_data_instance'))
      ->set('olapic_gallery_ar_data_instance', $form_state->getValue('olapic_gallery_ar_data_instance'))
      ->set('olapic_plp_en_data_instance', $form_state->getValue('olapic_plp_en_data_instance'))
      ->set('olapic_plp_ar_data_instance', $form_state->getValue('olapic_plp_ar_data_instance'))
      ->set('olapic_pdp_en_data_instance', $form_state->getValue('olapic_pdp_en_data_instance'))
      ->set('olapic_pdp_ar_data_instance', $form_state->getValue('olapic_pdp_ar_data_instance'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
