<?php

namespace Drupal\alshaya_tabby\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Alshaya Tabby settings.
 */
class AlshayaTabbySettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_tabby_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_tabby.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_tabby.settings');
    $form['tabby_configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuration'),
      '#open' => TRUE,
    ];
    $form['tabby_configuration']['show_tabby_widget'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('show_tabby_widget'),
      '#title' => $this->t('Show tabby widget on PDP & Cart page.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_tabby.settings')
      ->set('show_tabby_widget', $form_state->getValue('show_tabby_widget'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
