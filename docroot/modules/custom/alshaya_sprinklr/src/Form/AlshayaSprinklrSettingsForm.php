<?php

namespace Drupal\alshaya_sprinklr\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure alshaya_sprinklr settings for this site.
 */
class AlshayaSprinklrSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_sprinklr_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_sprinklr.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_sprinklr.settings');
    $allowed_urls = $config->get('allowed_urls');
    $form['alshaya_sprinklr_configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuration'),
      '#open' => TRUE,
    ];

    $form['alshaya_sprinklr_configuration']['enable_disable_sprinklr'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('sprinklr_enabled'),
      '#title' => $this->t('Enable sprinklr chatbot feature.'),
    ];

    $form['alshaya_sprinklr_configuration']['app_id_en'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App Id (EN)'),
      '#required' => TRUE,
      '#description' => $this->t('Provide app id to connect with sprinklr English chatbot server.'),
      '#default_value' => $config->get('app_id_en'),
    ];

    $form['alshaya_sprinklr_configuration']['app_id_ar'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App Id (AR)'),
      '#required' => TRUE,
      '#description' => $this->t('Provide app id to connect with sprinklr Arabic chatbot server.'),
      '#default_value' => $config->get('app_id_ar'),
    ];

    $form['alshaya_sprinklr_configuration']['allowed_urls'] = [
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
    $this->config('alshaya_sprinklr.settings')
      ->set('sprinklr_enabled', $form_state->getValue('enable_disable_sprinklr'))
      ->set('app_id_en', $form_state->getValue('app_id_en'))
      ->set('app_id_ar', $form_state->getValue('app_id_ar'))
      ->set('allowed_urls', $form_state->getValue('allowed_urls'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
