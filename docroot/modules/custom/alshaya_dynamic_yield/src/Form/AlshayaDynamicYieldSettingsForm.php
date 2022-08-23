<?php

namespace Drupal\alshaya_dynamic_yield\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Alshaya Dynamic Yield Settings Form.
 *
 * @package Drupal\alshaya_dynamic_yield\Form
 */
class AlshayaDynamicYieldSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_dynamic_yield_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_dynamic_yield.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_dynamic_yield.settings');

    $form['basic_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Basic settings'),
      '#open' => TRUE,
    ];

    $form['basic_settings']['pdp_div_placeholder_count'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of empty divs for PDP'),
      '#description' => $this->t('Enter count of divs required on PDP pages for dynamic yield recommendations.'),
      '#default_value' => $config->get('pdp_div_placeholder_count'),
    ];

    $form['basic_settings']['cart_div_placeholder_count'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of empty divs for cart'),
      '#description' => $this->t('Enter count of divs required on cart pages for dynamic yield recommendations.'),
      '#default_value' => $config->get('cart_div_placeholder_count'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('alshaya_dynamic_yield.settings')
      ->set('pdp_div_placeholder_count', $values['pdp_div_placeholder_count'])
      ->set('cart_div_placeholder_count', $values['cart_div_placeholder_count'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
