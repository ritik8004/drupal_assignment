<?php

namespace Drupal\alshaya_optimizely\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AlshayaOptimizely Settings Form.
 *
 * @package Drupal\alshaya_optimizely\Form
 */
class AlshayaOptimizelySettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_optimizely_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_optimizely.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_optimizely.settings');

    $form['basic_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Basic settings'),
      '#open' => FALSE,
    ];

    $form['basic_settings']['project_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Project ID'),
      '#default_value' => $config->get('project_id'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('alshaya_optimizely.settings')
      ->set('project_id', $values['project_id'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
