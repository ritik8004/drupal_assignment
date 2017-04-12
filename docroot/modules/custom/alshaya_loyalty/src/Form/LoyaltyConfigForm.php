<?php

namespace Drupal\alshaya_loyalty\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class LoyaltyConfigForm.
 */
class LoyaltyConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_loyalty_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_loyalty.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_loyalty.settings');

    $form['string_validation'] = [
      '#type' => 'details',
      '#title' => t('validation'),
      '#open' => TRUE,
    ];
    // Choose which length of the string.
    $form['string_validation']['max_length'] = [
      '#type' => 'textfield',
      '#title' => t('Max. Length'),
      '#description' => t("Field's value length will not be acceptable longer then the entered value here."),
      '#default_value' => $config->get('apcn_max_length') ? $config->get('apcn_max_length') : 16,
    ];
    // Add validation for the value.
    $form['string_validation']['value_starts_with'] = [
      '#type' => 'textfield',
      '#title' => t('Value starts with'),
      '#description' => t("Enter a specific value that you want this field's value should start with."),
      '#default_value' => $config->get('apcn_value_starts_with') ? $config->get('apcn_value_starts_with') : 6362544,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_loyalty.settings');
    $config->set('apcn_max_length', $form_state->getValue('max_length'));
    $config->set('apcn_value_starts_with', $form_state->getValue('value_starts_with'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
