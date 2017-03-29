<?php
/**
 * @file
 * Contains Drupal\acq_commerce\Form\ConductorSettingsForm
 */

namespace Drupal\acq_commerce\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class OrderSettingsForm
 * @package Drupal\acq_commerce\Form
 * @ingroup acq_commerce
 */
class ConductorSettingsForm extends ConfigFormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'acq_commerce_conductor_settings';
  }

  /**
   * {@inheritDoc}
   */
  public function getEditableConfigNames()
  {
    return ['acq_commerce.conductor'];
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // TODO Validate Conductor URL endpoints with watchdog request.
    $this->config('acq_commerce.conductor')
      ->set('url_agent', $form_state->getValue('url_agent'))
      ->set('url_ingest', $form_state->getValue('url_ingest'))
      ->set('timeout', (int) $form_state->getValue('timeout'))
      ->set('verify_ssl', (bool) $form_state->getValue('verify_ssl'))
      ->set('product_page_size', (int) $form_state->getValue('page_size'))
      ->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('acq_commerce.conductor');
    $form['url_agent'] = array(
      '#type' => 'url',
      '#title' => $this->t('Conductor Agent (Synchronous) URL'),
      '#required' => TRUE,
      '#default_value' => $config->get('url_agent'),
    );

    $form['url_ingest'] = array(
      '#type' => 'url',
      '#title' => $this->t('Conductor Ingest (Asynchronous) URL'),
      '#required' => TRUE,
      '#default_value' => $config->get('url_ingest'),
    );

    $form['media_base_url'] = array(
      '#type' => 'url',
      '#title' => $this->t('Base URL for media files'),
      '#required' => TRUE,
      '#default_value' => $config->get('media_base_url'),
    );

    $form['timeout'] = array(
      '#type' => 'number',
      '#title' => $this->t('Conductor Connection Timeout'),
      '#required' => TRUE,
      '#default_value' => $config->get('timeout'),
    );

    $form['verify_ssl'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Conductor Verify SSL'),
      '#default_value' => $config->get('verify_ssl'),
    );

    $form['debug'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Debug Level Logging Of API Connections'),
      '#default_value' => $config->get('debug'),
    );

    $form['page_size'] = array(
      '#type' => 'number',
      '#title' => $this->t('Conductor Product Synchronization Page Size'),
      '#default_value' => $config->get('product_page_size'),
    );

    return parent::buildForm($form, $form_state);
  }
}
