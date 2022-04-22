<?php

namespace Drupal\datadog_js\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Datadog JS Settings Form.
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
      '#title' => $this->t('Datadog Logs Library'),
      '#type' => 'textfield',
      '#default_value' => $config->get('library'),
      '#description' => $this->t('Required to enable Datadog Logs.'),
    ];

    $form['token'] = [
      '#title' => $this->t('Datadog Logs Client Token'),
      '#type' => 'textfield',
      '#default_value' => $config->get('token'),
      '#description' => $this->t('Required to enable Datadog Logs.'),
    ];

    $form['rum_library'] = [
      '#title' => $this->t('Datadog RUM Library'),
      '#type' => 'textfield',
      '#default_value' => $config->get('rum_library'),
      '#description' => $this->t('Required to enable Datadog RUM.'),
    ];

    $form['rum_application_id'] = [
      '#title' => $this->t('Datadog RUM Application ID'),
      '#type' => 'textfield',
      '#default_value' => $config->get('rum_application_id'),
      '#description' => $this->t('Required to enable Datadog RUM.'),
    ];

    $form['rum_client_token'] = [
      '#title' => $this->t('Datadog RUM Client Token'),
      '#type' => 'textfield',
      '#default_value' => $config->get('rum_client_token'),
      '#description' => $this->t('Required to enable Datadog RUM.'),
    ];

    $form['application'] = [
      '#title' => $this->t('Datadog Site'),
      '#type' => 'textfield',
      '#default_value' => $config->get('application'),
      '#description' => $this->t('The Datadog site of your organization. US: datadoghq.com, EU: datadoghq.eu.'),
    ];

    $form['admin_pages'] = [
      '#title' => $this->t('Datadog for admin section'),
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
      ->set('rum_library', $values['rum_library'])
      ->set('rum_application_id', $values['rum_application_id'])
      ->set('rum_client_token', $values['rum_client_token'])
      ->set('application', $values['application'])
      ->set('admin_pages', $values['admin_pages'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
