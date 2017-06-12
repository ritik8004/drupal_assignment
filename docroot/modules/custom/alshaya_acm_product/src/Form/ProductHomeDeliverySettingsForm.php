<?php

namespace Drupal\alshaya_acm_product\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ProductHomeDeliverySettingsForm.
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
    $config->set('title_price', $form_state->getValue('title_price'));
    $config->set('options_standard_icon_class', $form_state->getValue('options_standard_title'));
    $config->set('options_standard_title', $form_state->getValue('options_standard_title'));
    $config->set('options_standard_subtitle', $form_state->getValue('options_standard_subtitle'));
    $config->set('options_standard_price_options_one_title', $form_state->getValue('options_standard_price_options_one_title'));
    $config->set('options_standard_price_options_one_price', $form_state->getValue('options_standard_price_options_one_price'));
    $config->set('options_standard_price_options_two_title', $form_state->getValue('options_standard_price_options_two_title'));
    $config->set('options_standard_price_options_two_price', $form_state->getValue('options_standard_price_options_two_price'));

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

    $form['title_price'] = [
      '#type' => 'textfield',
      '#required' => FALSE,
      '#title' => $this->t('Price displayed in title'),
      '#description' => $this->t('Leave blank for free'),
      '#default_value' => $config->get('title_price'),
    ];

    $form['options_standard_icon_class'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Icon class'),
      '#description' => $this->t('This is not translatable'),
      '#default_value' => $config->get('options_standard_icon_class'),
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

    $form['options_standard_price_options_one_title'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Standard option first line title'),
      '#default_value' => $config->get('options_standard_price_options_one_title'),
    ];

    $form['options_standard_price_options_one_price'] = [
      '#type' => 'textfield',
      '#required' => FALSE,
      '#title' => $this->t('Standard option first line price'),
      '#description' => $this->t('Leave blank for free'),
      '#default_value' => $config->get('options_standard_price_options_one_price'),
    ];

    $form['options_standard_price_options_two_title'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Standard option second line title'),
      '#default_value' => $config->get('options_standard_price_options_two_title'),
    ];

    $form['options_standard_price_options_two_price'] = [
      '#type' => 'textfield',
      '#required' => FALSE,
      '#title' => $this->t('Standard option second line price'),
      '#description' => $this->t('Leave blank for free'),
      '#default_value' => $config->get('options_standard_price_options_two_price'),
    ];

    return $form;
  }

}
