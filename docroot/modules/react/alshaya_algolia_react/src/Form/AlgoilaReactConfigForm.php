<?php

namespace Drupal\alshaya_algolia_react\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Alshaya Algolia React Config settings.
 */
class AlgoilaReactConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'algolia_react_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'alshaya_algolia_react.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_algolia_react.settings');
    $form['application_id'] = [
      '#title' => $this->t('Application id'),
      '#type' => 'textfield',
      '#default_value' => $config->get('application_id') ?? '',
      '#required' => TRUE,
    ];

    $form['search_api_key'] = [
      '#title' => $this->t('Search api key.'),
      '#type' => 'textfield',
      '#default_value' => $config->get('search_api_key') ?? '',
      '#required' => TRUE,
    ];

    $form['hits'] = [
      '#type' => 'number',
      '#min' => 3,
      '#step' => 3,
      '#max' => 96,
      '#title' => $this->t('Number of results to show'),
      '#default_value' => $config->get('hits') ?? 12,
    ];

    $form['top_results'] = [
      '#type' => 'number',
      '#min' => 2,
      '#step' => 2,
      '#max' => 10,
      '#title' => $this->t('Number of top results to show for autocomplete'),
      '#default_value' => $config->get('top_results') ?? 4,
    ];

    $form['items_per_page'] = [
      '#type' => 'number',
      '#min' => 12,
      '#step' => 3,
      '#max' => 100,
      '#title' => $this->t('Number of items to show for search results.'),
      '#default_value' => $config->get('items_per_page') ?? 12,
    ];

    $form['hide_grid_toggle'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide grid toggle on listing pages.'),
      '#default_value' => $config->get('hide_grid_toggle'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('alshaya_algolia_react.settings')
      ->set('application_id', $form_state->getValue('application_id'))
      ->set('search_api_key', $form_state->getValue('search_api_key'))
      ->set('hits', $form_state->getValue('hits'))
      ->set('top_results', $form_state->getValue('top_results'))
      ->set('items_per_page', $form_state->getValue('items_per_page'))
      ->set('hide_grid_toggle', $form_state->getValue('hide_grid_toggle'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
