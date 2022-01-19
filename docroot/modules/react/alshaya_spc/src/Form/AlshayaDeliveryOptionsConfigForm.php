<?php

namespace Drupal\alshaya_spc\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Alshaya Delivery options configuration form.
 */
class AlshayaDeliveryOptionsConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_delivery_options_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_spc.express_delivery'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $delivery_options_config = $this->config('alshaya_spc.express_delivery');

    $form['alshaya_delivery_options']['same_day_delivery_status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Same Day Delivery Feature'),
      '#description' => $this->t('Same Day Delivery will be enabled if checkbox is checked.'),
      '#default_value' => $delivery_options_config->get('same_day_delivery_status'),
    ];

    $form['alshaya_delivery_options']['express_delivery_status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Express Delivery Feature'),
      '#description' => $this->t('Express Delivery will be enabled if checkbox is checked.'),
      '#default_value' => $delivery_options_config->get('express_delivery_status'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_spc.express_delivery')
      ->set('same_day_delivery_status', $form_state->getValue('same_day_delivery_status'))
      ->set('express_delivery_status', $form_state->getValue('express_delivery_status'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
