<?php

namespace Drupal\acq_commerce\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Store Settings Form.
 *
 * @package Drupal\acq_commerce\Form
 *
 * @ingroup acq_commerce
 */
class StoreSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'acq_commerce_store_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['acq_commerce.store'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('acq_commerce.store')
      ->set('store_id', $form_state->getValue('store_id'))
      ->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('acq_commerce.store');

    $form['store_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Store id'),
      '#default_value' => $config->get('store_id'),
    ];

    return parent::buildForm($form, $form_state);
  }

}
