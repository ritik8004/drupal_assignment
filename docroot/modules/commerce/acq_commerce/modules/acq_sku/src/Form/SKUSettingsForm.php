<?php

namespace Drupal\acq_sku\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ContentEntityExampleSettingsForm.
 *
 * @package Drupal\acq_sku\Form
 *
 * @ingroup acq_sku
 */
class SKUSettingsForm extends ConfigFormBase {

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['acq_sku.settings'];
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'acq_sku_settings';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $sku_settings = $this->config('acq_sku.settings');
    $sku_settings->set('stock_mode', $form_state->getValue('stock_mode'));
    $sku_settings->set('stock_cache_multiplier', $form_state->getValue('stock_cache_multiplier'));
    $sku_settings->set('stock_cache_max_lifetime', $form_state->getValue('stock_cache_max_lifetime'));
    $sku_settings->set('linked_skus_cache_max_lifetime', $form_state->getValue('linked_skus_cache_max_lifetime'));
    $sku_settings->save();
  }

  /**
   * Define the form used for settings.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $sku_settings = $this->config('acq_sku.settings');

    $form['stock_mode'] = [
      '#type' => 'select',
      '#options' => [
        'pull' => $this->t('Pull'),
        'push' => $this->t('Push'),
      ],
      '#title' => $this->t('Stock Mode'),
      '#default_value' => $sku_settings->get('stock_mode'),
    ];

    $form['stock_cache_multiplier'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Stock Cache Multiplier'),
      '#description' => $this->t("Multiplier used while generating the cache expiration for SKU's stock. e.g., cache_expire = stock_quantity * stock_cache_multiplier"),
      '#default_value' => $sku_settings->get('stock_cache_multiplier'),
    ];

    $form['stock_cache_max_lifetime'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Stock Cache Max Lifetime'),
      '#description' => $this->t("Maximum lifetime for the stock cache in seconds."),
      '#default_value' => $sku_settings->get('stock_cache_max_lifetime'),
    ];

    $form['linked_skus_cache_max_lifetime'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Linked SKUs Cache Max Lifetime'),
      '#description' => $this->t("Maximum lifetime for linked skus in seconds."),
      '#default_value' => $sku_settings->get('linked_skus_cache_max_lifetime'),
    ];

    return $form;
  }

}
