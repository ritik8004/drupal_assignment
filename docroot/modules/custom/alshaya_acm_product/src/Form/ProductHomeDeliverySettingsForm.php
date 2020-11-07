<?php

namespace Drupal\alshaya_acm_product\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Product Home Delivery Settings Form.
 */
class ProductHomeDeliverySettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'product_home_delivery_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_acm_product.home_delivery'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm_product.home_delivery');

    $config->set('title', $form_state->getValue('title'));
    $config->set('subtitle', $form_state->getValue('subtitle'));
    $config->set('options_standard_title', $form_state->getValue('options_standard_title'));
    $config->set('options_standard_subtitle', $form_state->getValue('options_standard_subtitle'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_acm_product.home_delivery');

    $form['title'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Title'),
      '#default_value' => $config->get('title'),
    ];

    $form['subtitle'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Subitle'),
      '#default_value' => $config->get('subtitle'),
    ];

    $form['options_standard_title'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Standard option title'),
      '#default_value' => $config->get('options_standard_title'),
    ];

    $form['options_standard_subtitle'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Standard option subtitle'),
      '#default_value' => $config->get('options_standard_subtitle'),
    ];

    return $form;
  }

}
