<?php

namespace Drupal\alshaya_acm_dashboard\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Alshaya acm dashboard settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_acm_dashboard_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_acm_dashboard.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['mdc_queues'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('MDC Queues'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    $form['mdc_queues']['processing_rate_alshaya-pims-product-import-service'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Average Processing rate for PIMS import queue'),
      '#description' => $this->t('Average time taken to process 1 item in milliseconds.'),
      '#default_value' => $this->config('alshaya_acm_dashboard.settings')->get('processing_rate_alshaya-pims-product-import-service'),
    ];

    $form['mdc_queues']['processing_rate_connectorProductPushQueue'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Average Processing rate for Product Push queue'),
      '#description' => $this->t('Average time taken to process 1 item in milliseconds.'),
      '#default_value' => $this->config('alshaya_acm_dashboard.settings')->get('processing_rate_connectorProductPushQueue'),
    ];

    $form['mdc_queues']['processing_rate_connectorStockPushQueue'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Average Processing rate for Stock Push queue'),
      '#description' => $this->t('Average time taken to process 1 item in milliseconds.'),
      '#default_value' => $this->config('alshaya_acm_dashboard.settings')->get('processing_rate_connectorStockPushQueue'),
    ];

    $form['processing_rate_acm_queue'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Average Processing rate of Acm Queue'),
      '#description' => $this->t('Average time taken to process 1 item in milliseconds.'),
      '#default_value' => $this->config('alshaya_acm_dashboard.settings')->get('processing_rate_acm_queue'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_acm_dashboard.settings')
      ->set('processing_rate_alshaya-pims-product-import-service', $form_state->getValue('processing_rate_alshaya-pims-product-import-service'))
      ->set('processing_rate_connectorProductPushQueue', $form_state->getValue('processing_rate_connectorProductPushQueue'))
      ->set('processing_rate_connectorStockPushQueue', $form_state->getValue('processing_rate_connectorStockPushQueue'))
      ->set('processing_rate_acm_queue', $form_state->getValue('processing_rate_acm_queue'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
