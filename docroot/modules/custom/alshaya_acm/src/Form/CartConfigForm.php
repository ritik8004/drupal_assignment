<?php

namespace Drupal\alshaya_acm\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CartConfigForm.
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
    $this->config('alshaya_acm.cart_config')
      ->set('max_cart_qty', $form_state->getValue('max_cart_qty'))
      ->save();

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

    return parent::buildForm($form, $form_state);
  }

}
