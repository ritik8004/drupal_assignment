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
    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search orders by'),
      '#required' => FALSE,
      '#placeholder' => $this->t('ID, name, SKU'),
      '#default_value' => \Drupal::request()->query->get('search'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
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
