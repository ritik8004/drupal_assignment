<?php

namespace Drupal\alshaya_loyalty\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;

/**
 * Class Loyalty Config Form.
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
      '#title' => $this->t('validation'),
      '#open' => TRUE,
    ];
    // Choose which length of the string.
    $form['string_validation']['max_length'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Max. Length'),
      '#description' => $this->t("Field's value length will not be acceptable longer then the entered value here."),
      '#default_value' => $config->get('apcn_max_length') ?: '16',
    ];
    // Add validation for the value.
    $form['string_validation']['value_starts_with'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value starts with'),
      '#description' => $this->t("Enter a specific value that you want this field's value should start with."),
      '#default_value' => $config->get('apcn_value_starts_with') ?: '6362544',
    ];

    $form['loyalty_configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuration'),
      '#open' => FALSE,
    ];
    $form['loyalty_configuration']['privilege_card_earn_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PRIVILEGES CLUB card earn text'),
      '#description' => $this->t('PRIVILEGES CLUB card earn text'),
      '#default_value' => $config->get('privilege_card_earn_text') ?: '',
    ];

    // Loyalty on/off feature.
    $form['loyalty_on_off'] = [
      '#type' => 'details',
      '#title' => $this->t('Loyalty ON/OFF'),
      '#open' => FALSE,
    ];
    $form['loyalty_on_off']['enable_disable_loyalty'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable or disable loyalty on site'),
      '#required' => TRUE,
      '#default_value' => $config->get('enable_disable_loyalty'),
      '#options' => [0 => $this->t('Disable'), 1 => $this->t('Enable')],
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
    $config->set('enable_disable_loyalty', $form_state->getValue('enable_disable_loyalty'));
    $config->set('privilege_card_earn_text', $form_state->getValue('privilege_card_earn_text'));
    $config->save();

    // Invalidate the cache tag.
    $tags = ['loyalty-on-off'];
    Cache::invalidateTags($tags);

    return parent::submitForm($form, $form_state);
  }

}
