<?php

namespace Drupal\acq_commerce\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class OrderSettingsForm.
 *
 * @package Drupal\acq_commerce\Form
 *
 * @ingroup acq_commerce
 */
class ConductorSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'acq_commerce_conductor_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {

    return ['acq_commerce.conductor'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // TODO Validate Conductor URL endpoints with watchdog request.
    $this->config('acq_commerce.conductor')
      ->set('url', $form_state->getValue('url'))
      ->set('timeout', (int) $form_state->getValue('timeout'))
      ->set('verify_ssl', (bool) $form_state->getValue('verify_ssl'))
      ->set('product_page_size', (int) $form_state->getValue('page_size'))
      ->set('filter_root_category', (bool) $form_state->getValue('filter_root_category'))
      ->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('acq_commerce.conductor');
    $form['url'] = [
      '#type' => 'url',
      '#title' => $this->t('Conductor URL'),
      '#required' => TRUE,
      '#default_value' => $config->get('url'),
    ];
    $form['api_version'] = [
      '#type' => 'select',
      '#title' => $this->t('API version'),
      '#required' => TRUE,
      '#default_value' => $config->get('api_version'),
      '#options' => ['v1' => 'V1'],
    ];

    $form['timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Conductor Connection Timeout'),
      '#required' => TRUE,
      '#default_value' => $config->get('timeout'),
    ];

    $form['verify_ssl'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Conductor Verify SSL'),
      '#default_value' => $config->get('verify_ssl'),
    ];

    $form['debug'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Debug Level Logging Of API Connections'),
      '#default_value' => $config->get('debug'),
    ];

    $form['page_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Conductor Product Synchronization Page Size'),
      '#default_value' => $config->get('product_page_size'),
    ];

    $form['filter_root_category'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Filter root level category'),
      '#default_value' => $config->get('filter_root_category'),
    ];

    return parent::buildForm($form, $form_state);
  }

}
