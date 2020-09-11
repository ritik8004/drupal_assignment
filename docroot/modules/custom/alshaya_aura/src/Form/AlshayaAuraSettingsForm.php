<?php

namespace Drupal\alshaya_aura\Form;

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
    return 'alshaya_aura_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_aura.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['alshaya_aura']['aura_rewards_header_learn_more_link'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('AURA Rewards Header Learn More'),
      '#description' => $this->t('Learn More link to be added in AURA Rewards popup in header.'),
      '#default_value' => $this->config('alshaya_aura.settings')->get('aura_rewards_header_learn_more_link.value'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_aura.settings')
      ->set('aura_rewards_header_learn_more_link', $form_state->getValue('aura_rewards_header_learn_more_link'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
