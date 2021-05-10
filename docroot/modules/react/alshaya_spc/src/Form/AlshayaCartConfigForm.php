<?php

namespace Drupal\alshaya_spc\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Alshaya Cart settings.
 */
class AlshayaCartConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'algolia_cart_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'alshaya_spc.cart_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_spc.cart_settings');
    $form['version'] = [
      '#title' => $this->t('Cart version'),
      '#type' => 'radios',
      '#options' => [1 => 'v1', 2 => 'v2'],
      '#default_value' => $config->get('version') ?? 2,
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('alshaya_spc.cart_settings')
      ->set('version', $form_state->getValue('version'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
