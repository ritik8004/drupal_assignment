<?php

namespace Drupal\acq_cart\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CartConfigForm
 */
class CartConfigForm extends ConfigFormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'acq_cart_config';
  }

  /**
   * {@inheritDoc}
   */
  public function getEditableConfigNames()
  {
    return ['acq_cart.config'];
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config('acq_cart.config')
      ->set('max_cart_qty', $form_state->getValue('max_cart_qty'))
      ->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('acq_cart.config');
    $form['max_cart_qty'] = array(
      '#type' => 'textfield',
      '#description' => $this->t('Maximum number of products user can buy from cart page.'),
      '#title' => $this->t('Maximum Cart Quantity'),
      '#required' => TRUE,
      '#default_value' => $config->get('max_cart_qty'),
    );

    return parent::buildForm($form, $form_state);
  }
}
