<?php

namespace Drupal\alshaya_aura_react\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Alshaya AURA settings.
 */
class AlshayaAuraSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_aura_react_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_aura_react.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['alshaya_aura_react'] = [
      'aura_rewards_header_learn_more_link' => [
        '#type' => 'textfield',
        '#title' => $this->t('AURA Rewards Header Learn More Link'),
        '#description' => $this->t('Learn More link to be added in AURA Rewards popup in header.'),
        '#default_value' => $this->config('alshaya_aura_react.settings')->get('aura_rewards_header_learn_more_link'),
      ],
      'aura_app_store_link' => [
        '#type' => 'textfield',
        '#title' => $this->t('AURA Apple App Store Link'),
        '#description' => $this->t('App Store link to be added in AURA blocks.'),
        '#default_value' => $this->config('alshaya_aura_react.settings')->get('aura_app_store_link'),
      ],
      'aura_google_play_link' => [
        '#type' => 'textfield',
        '#title' => $this->t('AURA Google Play Store Link'),
        '#description' => $this->t('Play Store link to be added in AURA blocks.'),
        '#default_value' => $this->config('alshaya_aura_react.settings')->get('aura_google_play_link'),
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_aura_react.settings')
      ->set('aura_rewards_header_learn_more_link', $form_state->getValue('aura_rewards_header_learn_more_link'))
      ->set('aura_app_store_link', $form_state->getValue('aura_app_store_link'))
      ->set('aura_google_play_link', $form_state->getValue('aura_google_play_link'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
