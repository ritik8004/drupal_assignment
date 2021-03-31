<?php

namespace Drupal\track_js\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Track JS Settings Form.
 *
 * @package Drupal\track_js\Form
 */
class TrackJsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'track_js_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['track_js.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('track_js.settings');

    $form['library'] = [
      '#title' => $this->t('TrackJS Library'),
      '#type' => 'textfield',
      '#default_value' => $config->get('library'),
      '#required' => TRUE,
    ];

    $form['token'] = [
      '#title' => $this->t('TrackJS Token'),
      '#type' => 'textfield',
      '#default_value' => $config->get('token'),
      '#description' => $this->t('Keep this empty to disable the feature.'),
    ];

    $form['application'] = [
      '#title' => $this->t('TrackJS Application'),
      '#type' => 'textfield',
      '#default_value' => $config->get('application'),
      '#description' => $this->t('Optional, used to separate your data by code base, environment, or anything else that makes sense.'),
    ];

    $form['admin_pages'] = [
      '#title' => $this->t('Track for admin section'),
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

    $this->config('track_js.settings')
      ->set('library', $values['library'])
      ->set('token', $values['token'])
      ->set('application', $values['application'])
      ->set('admin_pages', $values['admin_pages'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
