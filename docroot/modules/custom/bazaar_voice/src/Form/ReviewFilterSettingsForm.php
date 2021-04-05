<?php

namespace Drupal\bazaar_voice\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class BazaarVoice filter Settings Form.
 *
 * @package Drupal\bazaar_voice\Form
 */
class ReviewFilterSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bazaar_voice_filter_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['bazaar_voice_filter_review.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('bazaar_voice_filter_review.settings');

    $form['pdp_filter_options'] = [
      '#type' => 'textarea',
      '#title' => $this->t('PDP filter options'),
      '#default_value' => $config->get('pdp_filter_options'),
      '#description' => $this->t('Provide filter options to be shown in pdp review page, each option value should added in new line.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configFactory()->getEditable('bazaar_voice_filter_review.settings')
      ->set('pdp_filter_options', $values['pdp_filter_options'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
