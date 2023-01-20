<?php

namespace Drupal\alshaya_spc\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Alshaya Delivery options configuration form.
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
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_spc.screen_width_padding')
      ->set('screen_width_padding_status', $form_state->getValue('screen_width_padding_status'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
