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
    $form['hello_member_configuration']['points_history_page_size'] = [
      '#type' => 'text',
      '#default_value' => $config->get('points_history_page_size'),
      '#title' => $this->t('Points history page size.'),
      '#description' => $this->t('Enter page size for points history page.'),
    ];
    $form['hello_member_configuration']['terms_conditions'] = [
      '#type' => 'text_format',
      '#format' => $config->get('terms_conditions.format'),
      '#title' => $this->t('Terms and conditions'),
      '#default_value' => $config->get('terms_conditions.value'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_hello_member.settings')
      ->set('enabled', $form_state->getValue('enable_disable_hello_member'))
      ->set('points_history_page_size', $form_state->getValue('points_history_page_size'))
      ->set('terms_conditions', $form_state->getValue('terms_conditions'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
