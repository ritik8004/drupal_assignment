<?php

namespace Drupal\alshaya_algolia_react\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Algolia predictive search configuration.
 */
class AlgoliaPredictiveSearchForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'algolia_predictive_search_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'alshaya_algolia_react.predictive_search',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_algolia_react.predictive_search');
    $form['enable_predictive_search'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Predictive Search'),
      '#default_value' => $config->get('enable_predictive_search') ?: FALSE,
    ];

    $form['predictive_search_layout'] = [
      '#type' => 'radios',
      '#title' => $this->t('Desktop Layout for Predictive Search'),
      '#default_value' => $config->get('predictive_search_layout') ?: '1-column',
      '#options' => [
        '1-column' => $this->t('One Column Layout'),
        '2-column' => $this->t('Two Column Layout'),
      ],
      '#description' => $this->t('Layout for placement of recent searches
       and trending suggestions.'),
      '#required' => TRUE,
    ];

    $form['recent_search_count'] = [
      '#title' => 'Number of recent searches to be displayed',
      '#type' => 'number',
      '#default_value' => $config->get('recent_search_count') ?: 5,
      '#required' => TRUE,
      '#min' => '1',
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * Save form values in config.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('alshaya_algolia_react.predictive_search')
      ->set('enable_predictive_search', $form_state->getValue('enable_predictive_search'))
      ->set('predictive_search_layout', $form_state->getValue('predictive_search_layout'))
      ->set('recent_search_count', $form_state->getValue('recent_search_count'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
