<?php

namespace Drupal\alshaya_acm\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class Cart Config Form.
 */
class CartConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_acm_cart_config';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_acm.cart_config'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm.cart_config');
    $config->set('max_cart_qty', $form_state->getValue('max_cart_qty'));
    $config->set('checkout_feature', $form_state->getValue('checkout_feature'));
    $config->set('checkout_disabled_page', $form_state->getValue('checkout_disabled_page'));
    $config->set('version', $form_state->getValue('version'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm.cart_config');

    $form['max_cart_qty'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Maximum number of products user can buy from cart page.'),
      '#title' => $this->t('Maximum Cart Quantity'),
      '#required' => TRUE,
      '#default_value' => $config->get('max_cart_qty'),
    ];

    $form['checkout_feature'] = [
      '#type' => 'select',
      '#options' => [
        'enabled' => $this->t('Enabled'),
        'disabled' => $this->t('disabled'),
      ],
      '#description' => $this->t('Say whether users can place orders or not.'),
      '#title' => $this->t('Checkout feature status'),
      '#required' => TRUE,
      '#default_value' => $config->get('checkout_feature') ?? 'enabled',
    ];

    $form['checkout_disabled_page'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Checkout disabled page'),
      '#description' => $this->t('Page to redirect the users to when checkout is disabled. Leave blank to redirect to home page.'),
      '#required' => FALSE,
      '#default_value' => $config->get('checkout_disabled_page'),
    ];

    $form['version'] = [
      '#title' => $this->t('Cart version'),
      '#type' => 'radios',
      '#options' => [1 => 'v1', 2 => 'v2'],
      '#default_value' => $config->get('version') ?? 1,
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if (empty($form_state->getValue('checkout_disabled_page'))) {
      return;
    }

    try {
      Url::fromUserInput($form_state->getValue('checkout_disabled_page'));
    }
    catch (\Exception $e) {
      $form_state->setErrorByName('checkout_disabled_page', $e->getMessage());
    }
  }

}
