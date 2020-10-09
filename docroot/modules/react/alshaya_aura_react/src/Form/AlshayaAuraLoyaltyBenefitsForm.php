<?php

namespace Drupal\alshaya_aura_react\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Alshaya AURA Loyalty Benefits.
 */
class AlshayaAuraLoyaltyBenefitsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_aura_react_loyalty_benefits';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_aura_react.loyalty_benefits'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['alshaya_aura_react']['loyalty_benefits_title1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('AURA Loyalty Benefits Title 1'),
      '#description' => $this->t('AURA Loyalty Benefits Title 1 for Loyalty Club page.'),
      '#default_value' => $this->config('alshaya_aura_react.loyalty_benefits')->get('loyalty_benefits_title1.value'),
    ];

    $form['alshaya_aura_react']['loyalty_benefits_title2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('AURA Loyalty Benefits Title 2'),
      '#description' => $this->t('AURA Loyalty Benefits Title 2 for Loyalty Club page.'),
      '#default_value' => $this->config('alshaya_aura_react.loyalty_benefits')->get('loyalty_benefits_title2.value'),
    ];

    $form['alshaya_aura_react']['loyalty_benefits_content'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('AURA Loyalty Benefits Content'),
      '#description' => $this->t('AURA Loyalty Benefits content for Loyalty Club page.'),
      '#default_value' => $this->config('alshaya_aura_react.loyalty_benefits')->get('loyalty_benefits_content.value'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_aura_react.loyalty_benefits')
      ->set('loyalty_benefits_title1', $form_state->getValue('loyalty_benefits_title1'))
      ->set('loyalty_benefits_title2', $form_state->getValue('loyalty_benefits_title2'))
      ->set('loyalty_benefits_content', $form_state->getValue('loyalty_benefits_content'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
