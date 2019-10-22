<?php

namespace Drupal\alshaya_ve_non_transac\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;

/**
 * Class VESettingsForm.
 *
 * @package Drupal\alshaya_ve_non_transac\Form
 */
class VeSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 've_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_ve_non_transac.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_ve_non_transac.settings')
      ->set('book_appointment_url', $form_state->getValue('book_appointment_url'))
      ->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_ve_non_transac.settings');
    $form['book_appointment_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Book appointment url'),
      '#required' => TRUE,
      '#default_value' => ($config->get('book_appointment_url')) ?? Settings::get('alshaya_ve_non_transac.settings')['book_appointment_url'],
      '#description' => $this->t('Vision express book an appointment API url.'),
    ];
    return parent::buildForm($form, $form_state);
  }

}
