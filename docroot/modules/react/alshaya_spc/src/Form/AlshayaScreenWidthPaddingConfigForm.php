<?php

namespace Drupal\alshaya_spc\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Alshaya Width padding configuration form.
 */
class AlshayaScreenWidthPaddingConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_screen_width_padding';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_spc.screen_width_padding'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $screen_width_padding_config = $this->config('alshaya_spc.screen_width_padding');
    $form['alshaya_screen_width_padding']['screen_width_padding_status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Screen width padding'),
      '#description' => $this->t('Screen width padding enabled if checkbox is checked.'),
      '#default_value' => $screen_width_padding_config->get('screen_width_padding_status'),
    ];
    $form['alshaya_screen_width_padding']['screen_width_320'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Padding for Screen Width 320, 360 and 375'),
      '#description' => $this->t('Sets the padding for screen Width 320, 360, 375'),
      '#default_value' => $screen_width_padding_config->get('screen_width_320'),
    ];
    $form['alshaya_screen_width_padding']['screen_width_480'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Enter the padding for Screen Width 480'),
      '#description' => $this->t('Set the padding if screen Width 480.'),
      '#default_value' => $screen_width_padding_config->get('screen_width_480'),
    ];
    $form['alshaya_screen_width_padding']['screen_width_720'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Enter the padding for Screen Width 720, 768'),
      '#description' => $this->t('Set the padding if screen Width 720, 768.'),
      '#default_value' => $screen_width_padding_config->get('screen_width_720'),
    ];
    $form['alshaya_screen_width_padding']['screen_width_1024'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Enter the padding for Screen Width 1024, 1280'),
      '#description' => $this->t('Set the padding if screen Width 1024, 1280.'),
      '#default_value' => $screen_width_padding_config->get('screen_width_1024'),
    ];
    $form['alshaya_screen_width_padding']['screen_width_1680'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Enter the padding for Screen Width 1680, 1920'),
      '#description' => $this->t('Set the padding if screen Width 1680, 1920.'),
      '#default_value' => $screen_width_padding_config->get('screen_width_1680'),
    ];
    $form['alshaya_screen_width_padding']['screen_width_2560'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Enter the padding for Screen Width 2560'),
      '#description' => $this->t('Set the padding if screen Width 2560.'),
      '#default_value' => $screen_width_padding_config->get('screen_width_2560'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_spc.screen_width_padding')
      ->set('screen_width_padding_status', $form_state->getValue('screen_width_padding_status'))
      ->set('screen_width_320', $form_state->getValue('screen_width_320'))
      ->set('screen_width_480', $form_state->getValue('screen_width_480'))
      ->set('screen_width_720', $form_state->getValue('screen_width_720'))
      ->set('screen_width_1024', $form_state->getValue('screen_width_1024'))
      ->set('screen_width_1680', $form_state->getValue('screen_width_1680'))
      ->set('screen_width_2560', $form_state->getValue('screen_width_2560'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
