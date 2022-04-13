<?php

namespace Drupal\alshaya_online_returns\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Alshaya Return Confirmation configuration form.
 */
class AlshayaReturnConfirmationConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_return_confirmation_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_online_returns.return_confirmation'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_online_returns.return_confirmation');

    $hide_options = [
      0 => $this->t('Yes'),
      1 => $this->t('No'),
    ];

    $icon_class_options = [
      'print' => $this->t('print return label'),
      'packitem' => $this->t('pack item'),
      'refund' => $this->t('receive refund'),
    ];

    $form['return_confirmation']['row_1_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title for first row'),
      '#description' => $this->t('Label for first row in return confirmation page.'),
      '#default_value' => $config->get('row_1_title'),
    ];

    $form['return_confirmation']['row_1_description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description for first row'),
      '#description' => $this->t('Description text for first row in return confirmation page.'),
      '#default_value' => $config->get('row_1_description'),
    ];

    $form['return_confirmation']['row_1_icon_text'] = [
      '#type' => 'select',
      '#options' => $icon_class_options,
      '#title' => $this->t('Select corresponding icon text'),
      '#description' => $this->t('Icon text will be used to display respective icons.'),
      '#default_value' => $config->get('row_1_icon_text'),
    ];

    $form['return_confirmation']['row_1_hide_this_row'] = [
      '#type' => 'select',
      '#options' => $hide_options,
      '#title' => $this->t('Hide this row'),
      '#description' => $this->t('Hide first row from display'),
      '#default_value' => (int) $config->get('row_1_hide_this_row') ?? 0,
    ];

    $form['return_confirmation']['row_2_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title for second row'),
      '#description' => $this->t('Label for second row in return confirmation page.'),
      '#default_value' => $config->get('row_2_title'),
    ];

    $form['return_confirmation']['row_2_description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description for second row'),
      '#description' => $this->t('Description text for second row in return confirmation page.'),
      '#default_value' => $config->get('row_2_description'),
    ];

    $form['return_confirmation']['row_2_icon_text'] = [
      '#type' => 'select',
      '#options' => $icon_class_options,
      '#title' => $this->t('Select corresponding icon text'),
      '#description' => $this->t('Icon text will be used to display respective icons.'),
      '#default_value' => $config->get('row_2_icon_text'),
    ];

    $form['return_confirmation']['row_2_hide_this_row'] = [
      '#type' => 'select',
      '#options' => $hide_options,
      '#title' => $this->t('Hide this row'),
      '#description' => $this->t('Hide second row from display'),
      '#default_value' => (int) $config->get('row_2_hide_this_row') ?? 1,
    ];

    $form['return_confirmation']['row_3_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title for third row'),
      '#description' => $this->t('Label for third row in return confirmation page.'),
      '#default_value' => $config->get('row_3_title'),
    ];

    $form['return_confirmation']['row_3_description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description for third row'),
      '#description' => $this->t('Description text for third row in return confirmation page.'),
      '#default_value' => $config->get('row_3_description'),
    ];

    $form['return_confirmation']['row_3_icon_text'] = [
      '#type' => 'select',
      '#options' => $icon_class_options,
      '#title' => $this->t('Select corresponding icon text'),
      '#description' => $this->t('Icon text will be used to display respective icons.'),
      '#default_value' => $config->get('row_3_icon_text'),
    ];

    $form['return_confirmation']['row_3_hide_this_row'] = [
      '#type' => 'select',
      '#options' => $hide_options,
      '#title' => $this->t('Hide this row'),
      '#description' => $this->t('Hide third row from display'),
      '#default_value' => (int) $config->get('row_3_hide_this_row') ?? 1,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_online_returns.return_confirmation')
      ->set('row_1_title', $form_state->getValue('row_1_title'))
      ->set('row_1_description', $form_state->getValue('row_1_description'))
      ->set('row_1_icon_text', $form_state->getValue('row_1_icon_text'))
      ->set('row_1_hide_this_row', $form_state->getValue('row_1_hide_this_row'))
      ->set('row_2_title', $form_state->getValue('row_2_title'))
      ->set('row_2_description', $form_state->getValue('row_2_description'))
      ->set('row_2_icon_text', $form_state->getValue('row_2_icon_text'))
      ->set('row_2_hide_this_row', $form_state->getValue('row_2_hide_this_row'))
      ->set('row_3_title', $form_state->getValue('row_3_title'))
      ->set('row_3_description', $form_state->getValue('row_3_description'))
      ->set('row_3_icon_text', $form_state->getValue('row_3_icon_text'))
      ->set('row_3_hide_this_row', $form_state->getValue('row_3_hide_this_row'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
