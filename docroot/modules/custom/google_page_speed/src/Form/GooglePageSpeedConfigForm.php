<?php

namespace Drupal\google_page_speed\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Google Page Speed integration settings for this site.
 *
 * Class GooglePageSpeedConfigForm.
 */
class GooglePageSpeedConfigForm extends ConfigFormBase {

  const CONFIG_NAME = 'google_page_speed.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'google_page_speed_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [self::CONFIG_NAME];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#description' => $this->t('The Google Page Speed API key.'),
      '#default_value' => $this->config(self::CONFIG_NAME)->get('api_key'),
      '#required' => TRUE,
    ];

    $form['page_url'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Page URLs'),
      '#description' => $this->t('Please fill different URLs one per line.'),
      '#default_value' => $this->config(self::CONFIG_NAME)->get('page_url'),
      '#required' => TRUE,
    ];

    $form['screen'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Analysis screen'),
      '#description' => $this->t('Select the screen(s) for which analysis is to be done.'),
      '#options' => [
        'desktop' => $this->t('Desktop'),
        'mobile' => $this->t('Mobile'),
      ],
      '#default_value' => $this->config(self::CONFIG_NAME)->get('screen'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config(self::CONFIG_NAME)
      ->set('api_key', $form_state->getValue('api_key'))
      ->save();

    $this->config(self::CONFIG_NAME)
      ->set('page_url', $form_state->getValue('page_url'))
      ->save();

    $screen_items = array_keys(array_filter($form_state->getValues()['screen']));

    $this->config(self::CONFIG_NAME)
      ->set('screen', $screen_items)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
