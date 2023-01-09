<?php

namespace Drupal\sprinklr\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure sprinklr settings.
 */
class SprinklrSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sprinklr_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['sprinklr.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('sprinklr.settings');
    $allowed_urls = $config->get('allowed_urls');
    $form['sprinklr_configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuration'),
      '#open' => TRUE,
    ];
    $form['sprinklr_configuration']['enable_disable_sprinklr'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('sprinklr_enabled'),
      '#title' => $this->t('Enable sprinklr chatbot.'),
    ];
    $form['sprinklr_configuration']['app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App Id'),
      '#required' => TRUE,
      '#description' => $this->t('Provide app id to connect with sprinklr chatbot server.'),
      '#default_value' => $config->get('app_id'),
    ];
    $form['sprinklr_configuration']['allowed_urls'] = [
      '#type' => 'textarea',
      '#title' => $this->t('URLs for sprinklr chatbot integration.'),
      '#description' => $this->t('Provide URL aliases to show sprinklr chatbot on these pages, enter one alias per line.'),
      '#default_value' => $config->get('allowed_urls'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('sprinklr.settings')
      ->set('sprinklr_enabled', $form_state->getValue('enable_disable_sprinklr'))
      ->set('app_id', $form_state->getValue('app_id'))
      ->set('allowed_urls', $form_state->getValue('allowed_urls'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
