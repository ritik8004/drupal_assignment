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
    $form['hello_member_configuration']['status'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('status'),
      '#title' => $this->t('Enable Hello Member on site.'),
    ];
    $form['hello_member_configuration']['points_history_page_size'] = [
      '#type' => 'text',
      '#default_value' => $config->get('points_history_page_size'),
      '#title' => $this->t('Points history page size.'),
      '#description' => $this->t('Enter page size for points history page.'),
    ];
    $form['hello_member_configuration']['minimum_age'] = [
      '#type' => 'number',
      '#title' => $this->t('Minimum age'),
      '#default_value' => $config->get('minimum_age') ?? 18,
      '#required' => TRUE,
    ];

    $form['hello_member_configuration']['aura_integration_status'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('aura_integration_status'),
      '#title' => $this->t('Enable aura integration with hello member..'),
      '#description' => $this->t('When aura integration is status with hello member,
        customer can choose to redeem aura points.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_hello_member.settings')
      ->set('status', $form_state->getValue('status'))
      ->set('aura_integration_status', $form_state->getValue('aura_integration_status'))
      ->set('points_history_page_size', $form_state->getValue('points_history_page_size'))
      ->set('minimum_age', $form_state->getValue('minimum_age'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
