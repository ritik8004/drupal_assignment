<?php

namespace Drupal\datadog_js\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DataDog JS Settings Form.
 *
 * @package Drupal\datadog_js\Form
 */
class DatadogJsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'datadog_js_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['datadog_js.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('datadog_js.settings');

    $form['library'] = [
      '#title' => $this->t('DataDogJS Library'),
      '#type' => 'textfield',
      '#default_value' => $config->get('library'),
      '#required' => TRUE,
    ];

    $form['token'] = [
      '#title' => $this->t('DataDogJS Token'),
      '#type' => 'textfield',
      '#default_value' => $config->get('token'),
      '#description' => $this->t('Keep this empty to disable the feature.'),
    ];

    $form['site'] = [
      '#title' => $this->t('DataDogJS Site'),
      '#type' => 'textfield',
      '#default_value' => $config->get('site'),
      '#description' => $this->t('The Datadog site of your organization. US: datadoghq.com, EU: datadoghq.eu.'),
    ];

    $form['admin_pages'] = [
      '#title' => $this->t('DataDog for admin section'),
      '#type' => 'select',
      '#options' => [
        'track' => $this->t('Track for admin section too'),
        'hidden' => $this->t('Do not track for admin section'),
      ],
      '#default_value' => $config->get('admin_pages'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('datadog_js.settings')
      ->set('library', $values['library'])
      ->set('token', $values['token'])
      ->set('application', $values['application'])
      ->set('admin_pages', $values['admin_pages'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
