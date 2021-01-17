<?php

namespace Drupal\alshaya_bazaarvoice\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Alshaya Bazaarvoice settings.
 */
class AlshayaBazaarvoiceSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_bazaarvoice_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_bazaarvoice.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['alshaya_bazaarvoice']['local_storage_expire'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Local storage expire'),
      '#description' => $this->t('Provide expiry period for cache.'),
      '#default_value' => $this->config('alshaya_bazaarvoice.settings')->get('local_storage_expire'),
    ];

    $form['alshaya_bazaarvoice']['api_version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Version'),
      '#default_value' => $this->config('alshaya_bazaarvoice.settings')->get('api_version'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_bazaarvoice.settings')
      ->set('local_storage_expire', $form_state->getValue('local_storage_expire'))
      ->save();
    $this->config('alshaya_bazaarvoice.settings')
      ->set('api_version', $form_state->getValue('api_version'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
