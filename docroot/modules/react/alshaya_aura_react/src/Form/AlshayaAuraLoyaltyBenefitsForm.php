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
    return 'alshaya_aura_react_loyalty_benefits_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_aura_react.loyalty_benefits_form'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['alshaya_aura_react']['loyalty_benefits'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('AURA Loyalty Benefits'),
      '#description' => $this->t('AURA Loyalty Benefits details for Loyalty Club page.'),
      '#default_value' => $this->config('alshaya_aura_react.loyalty_benefits_form')->get('loyalty_benefits.value'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_aura_react.loyalty_benefits_form')
      ->set('loyalty_benefits', $form_state->getValue('loyalty_benefits'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
