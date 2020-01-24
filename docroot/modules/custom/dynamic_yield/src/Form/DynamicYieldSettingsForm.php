<?php

namespace Drupal\dynamic_yield\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DynamicYieldSettingsForm.
 *
 * @package Drupal\dynamic_yield\Form
 */
class DynamicYieldSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dynamic_yield_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['dynamic_yield.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('dynamic_yield.settings');

    $form['basic_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Basic settings'),
      '#open' => FALSE,
    ];

    $form['basic_settings']['site_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site ID'),
      '#default_value' => $config->get('site_id'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('dynamic_yield.settings')
      ->set('site_id', $values['site_id'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
