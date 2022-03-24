<?php

namespace Drupal\dynamic_yield\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Dynamic Yield Settings Form.
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

    $form['basic_settings']['section_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Section ID'),
      '#default_value' => $config->get('section_id'),
    ];

    $form['basic_settings']['dy_injection'] = [
      '#type' => 'select',
      '#title' => $this->t('Dynamic yield script injection type'),
      '#options' => [
        'backend' => $this->t('Backend'),
        'frontend' => $this->t('Frontend'),
      ],
      '#required' => TRUE,
      '#default_value' => $config->get('dy_injection') ?? 'backend',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('dynamic_yield.settings')
      ->set('section_id', $values['section_id'])
      ->set('dy_injection', $values['dy_injection'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
