<?php

namespace Drupal\alshaya_hello_member\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Alshaya Hello Member settings.
 */
class AlshayaHelloMemberSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_hello_member_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_hello_member.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_hello_member.settings');
    $form['hello_member_configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuration'),
      '#open' => TRUE,
    ];
    $form['hello_member_configuration']['enable_disable_hello_member'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('enabled'),
      '#title' => $this->t('Enable Hello Member on site.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_hello_member.settings')
      ->set('enabled', $form_state->getValue('enable_disable_hello_member'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
