<?php

namespace Drupal\acq_commerce\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Currency Settings Form.
 *
 * @package Drupal\acq_commerce\Form
 * @ingroup acq_commerce
 */
class CurrencySettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'acq_commerce_currency_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['acq_commerce.currency'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('acq_commerce.currency')
      ->set('currency_code', $form_state->getValue('currency_code'))
      ->set('iso_currency_code', $form_state->getValue('iso_currency_code'))
      ->set('currency_code_position', $form_state->getValue('currency_code_position'))
      ->set('decimal_points', $form_state->getValue('decimal_points'))
      ->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('acq_commerce.currency');
    $form['currency_code'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Currency code.'),
      '#title' => $this->t('currency code to display on site. It can be your local currency code.'),
      '#required' => TRUE,
      '#default_value' => $config->get('currency_code'),
    ];

    $form['iso_currency_code'] = [
      '#type' => 'textfield',
      '#description' => $this->t('ISO 4217 standard currency code.'),
      '#title' => $this->t('ISO currency code'),
      '#required' => TRUE,
      '#default_value' => $config->get('iso_currency_code'),
    ];

    $options = [
      'before' => $this->t('Before Price'),
      'after' => $this->t('After Price'),
    ];
    $form['currency_code_position'] = [
      '#type' => 'radios',
      '#title' => $this->t('Currency Code Position'),
      '#default_value' => $config->get('currency_code_position'),
      '#options' => $options,
      '#description' => $this->t('The position for the currency code.'),
      '#required' => TRUE,
    ];

    $form['decimal_points'] = [
      '#type' => 'select',
      '#title' => $this->t('Decimal points'),
      '#default_value' => $config->get('decimal_points'),
      '#options' => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5],
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

}
