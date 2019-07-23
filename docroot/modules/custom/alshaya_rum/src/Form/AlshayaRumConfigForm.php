<?php

namespace Drupal\alshaya_rum\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class AlshayaRumConfigForm extends ConfigFormBase {

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return [
      'alshaya_rum.settings',
    ];
  }

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'alshaya_rum_config_form';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['rum_user_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('RUM user id'),
      '#default_value' => $this->config('alshaya_rum.settings')->get('rum_user_id'),
    ];

    $form['whitelisted_rum_paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('List of paths on which we need to load rum script.'),
      '#default_value' => $this->config('alshaya_rum.settings')->get('whitelisted_rum_paths'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_rum.settings')->set('rum_user_id', $form_state->get('rum_user_id'));
    $this->config('alshaya_rum.settings')->set('whitelisted_rum_paths', $form_state->get('whitelisted_rum_paths'));
    parent::submitForm($form, $form_state);
  }

}
