<?php

namespace Drupal\alshaya_acm_product\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Basket Delivery Settings Form.
 */
class BasketDeliverySettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'basket_delivery_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_acm_product.basket_delivery'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm_product.basket_delivery');

    $config->set('home_delivery_title', $form_state->getValue('home_delivery_title'));
    $config->set('home_delivery_tooltip', $form_state->getValue('home_delivery_tooltip'));
    $config->set('click_collect_title', $form_state->getValue('click_collect_title'));
    $config->set('click_collect_tooltip', $form_state->getValue('click_collect_tooltip'));

    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_acm_product.basket_delivery');

    $form['home_delivery_title'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Home Delivery title'),
      '#default_value' => $config->get('home_delivery_title'),
    ];

    $form['home_delivery_tooltip'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Home Delivery tooltip'),
      '#default_value' => $config->get('home_delivery_tooltip'),
    ];

    $form['click_collect_title'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Click and Collect title'),
      '#default_value' => $config->get('click_collect_title'),
    ];

    $form['click_collect_tooltip'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Click and Collect tooltip'),
      '#default_value' => $config->get('click_collect_tooltip'),
    ];

    return $form;
  }

}
