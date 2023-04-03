<?php

namespace Drupal\alshaya_geolocation\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Alshaya Geo location settings.
 */
class AlshayaGeolocationSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_geolocation_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_geolocation.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_geolocation.settings');
    $form['geolocation_configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuration'),
      '#open' => TRUE,
    ];
    $form['geolocation_configuration']['enable_disable_geolocation'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('geolocation_enabled'),
      '#description' => $this->t('Enable or Disable the geolocation feature.'),
      '#title' => $this->t('Enable Geolocation on site.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_geolocation.settings')
      ->set('geolocation_enabled', $form_state->getValue('enable_disable_geolocation'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
