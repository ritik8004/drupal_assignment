<?php

namespace Drupal\alshaya_seo_transac\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Alshaya SEO Transac settings for this site.
 */
class AlshayaSeoTransacSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_seo_transac_alshaya_seo_transac_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_seo_transac.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['product_impression_queue_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Product impression GTM event queue size'),
      '#default_value' => $this->config('alshaya_seo_transac.settings')->get('product_impression_queue_size'),
    ];
    $form['product_impression_timer_time'] = [
      '#type' => 'number',
      '#title' => $this->t('Product Impression GTM event timer time'),
      '#default_value' => $this->config('alshaya_seo_transac.settings')->get('product_impression_timer_time'),
      '#description' => $this->t('The time(in MILLISECONDS) after which productImpression will be triggered automatically. Eg. for 60 seconds timer, enter "60000" in this field.'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('product_impression_queue_size') < 1) {
      $form_state->setErrorByName('product_impression_queue_size', $this->t('Please enter a positive value for queue size.'));
    }
    if ($form_state->getValue('product_impression_timer_time') < 1) {
      $form_state->setErrorByName('product_impression_timer_time', $this->t('Please enter a positive value for timer time.'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_seo_transac.settings')
      ->set('product_impression_queue_size', (int) $form_state->getValue('product_impression_queue_size'))
      ->set('product_impression_timer_time', (int) $form_state->getValue('product_impression_timer_time'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
