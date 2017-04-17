<?php

namespace Drupal\alshaya_acm_customer\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class CartConfigForm.
 */
class OrderSearchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_acm_customer_order_list_search';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search orders by'),
      '#required' => FALSE,
      '#placeholder' => $this->t('ID, name, SKU'),
      '#default_value' => \Drupal::request()->query->get('search'),
    ];

    $filterOptions = ['' => $this->t('All Orders')];

    $account = \Drupal::request()->attributes->get('user');
    $filterOptions += alshaya_acm_customer_get_available_user_order_status($account);

    $form['filter'] = [
      '#type' => 'select',
      '#weight' => 10,
      '#title' => $this->t('Show'),
      '#required' => FALSE,
      '#options' => $filterOptions,
      '#default_value' => \Drupal::request()->query->get('filter'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => 2,
    ];

    $form['actions']['submit'] = [
      // Prevent from showing up in \Drupal::request()->query.
      '#name' => '',
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#id' => Html::getUniqueId('edit-submit-orders'),
    ];

    $form['#action'] = Url::fromRoute('<current>')->toString();
    $form['#method'] = 'get';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
