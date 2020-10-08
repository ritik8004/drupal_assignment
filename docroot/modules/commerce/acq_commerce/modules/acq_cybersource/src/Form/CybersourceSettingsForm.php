<?php

namespace Drupal\acq_cybersource\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Cybersource Settings Form.
 *
 * @package Drupal\acq_cybersource\Form
 *
 * @ingroup acq_cybersource
 */
class CybersourceSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'acq_cybersource_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['acq_cybersource.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('acq_cybersource.settings');

    $config->set('env', $form_state->getValue('env'));
    $config->set('test_url', rtrim($form_state->getValue('test_url'), '/'));
    $config->set('prod_url', rtrim($form_state->getValue('prod_url'), '/'));
    $config->set('allowed_cc_types', $form_state->getValue('allowed_cc_types'));

    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('acq_cybersource.settings');

    $form['env'] = [
      '#type' => 'select',
      '#title' => $this->t('Environment'),
      '#options' => [
        'test' => $this->t('Test'),
        'prod' => $this->t('Production'),
      ],
      '#default_value' => $config->get('env'),
    ];

    $form['test_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Test Environment URL'),
      '#default_value' => $config->get('test_url'),
    ];

    $form['prod_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Production Environment URL'),
      '#default_value' => $config->get('prod_url'),
    ];

    $form['allowed_cc_types'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed Card types'),
      '#default_value' => $config->get('allowed_cc_types'),
    ];

    return $form;
  }

}
