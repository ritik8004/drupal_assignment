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
    $form['alshaya_bazaarvoice']['conversations_apikey'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Conversations API Key'),
      '#description' => $this->t('A passkey provided by BazaarVoice to get and submit brand specific reviews.'),
      '#default_value' => $this->config('alshaya_bazaarvoice.settings')->get('conversations_apikey'),
    ];

    $form['alshaya_bazaarvoice']['api_version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Version'),
      '#default_value' => $this->config('alshaya_bazaarvoice.settings')->get('api_version'),
    ];

    $form['alshaya_bazaarvoice']['locale'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Locale'),
      '#default_value' => $this->config('alshaya_bazaarvoice.settings')->get('locale'),
      '#description' => $this->t('Locale is required to get regional reviews data. It can be set as comma saparated, e.g. en_KW,ar_KW,en_AE,ar_AE'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_bazaarvoice.settings')
      ->set('conversations_apikey', $form_state->getValue('conversations_apikey'))
      ->save();
    $this->config('alshaya_bazaarvoice.settings')
      ->set('api_version', $form_state->getValue('api_version'))
      ->save();
    $this->config('alshaya_bazaarvoice.settings')
      ->set('locale', $form_state->getValue('locale'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
