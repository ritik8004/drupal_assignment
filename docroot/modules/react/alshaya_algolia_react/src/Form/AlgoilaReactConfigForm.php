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
    ];

    $form['search_api_key'] = [
      '#title' => $this->t('Search api key.'),
      '#type' => 'textfield',
      '#default_value' => $config->get('search_api_key') ?? '',
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

    $form['enable_hits_per_page'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable hits per page value for algolia calls.'),
      '#default_value' => $config->get('enable_hits_per_page') ?: FALSE,
      '#description' => $this->t('Checkbox to enable or disable hitsPerPage key in algolia call.'),
    ];

    $form['render_single_result_facets'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show single value Facets'),
      '#default_value' => $config->get('render_single_result_facets') ?: FALSE,
      '#description' => $this->t('Checkbox to show/hide single value facets.'),
    ];

    $form['exclude_render_single_result_facets'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Exclude the following filters from single result check.'),
      '#default_value' => $config->get('exclude_render_single_result_facets'),
      '#description' => $this->t('Enter filter keys that you always want to display. Please input comma seprated values.'),
    ];

    $form['default_col_grid'] = [
      '#type' => 'radios',
      '#title' => $this->t('Default col grid for desktop'),
      '#default_value' => $config->get('default_col_grid'),
      '#options' => ['small' => $this->t('Small'), 'large' => $this->t('Large')],
      '#description' => $this->t('Set value for default col grid for desktop view.'),
    ];

    $form['default_col_grid_mobile'] = [
      '#type' => 'radios',
      '#title' => $this->t('Default col grid for mobile'),
      '#default_value' => $config->get('default_col_grid_mobile'),
      '#options' => ['small' => $this->t('Small'), 'large' => $this->t('Large')],
      '#description' => $this->t('Set value for default col grid for mobile view.'),
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
      ->set('default_col_grid', $form_state->getValue('default_col_grid'))
      ->set('default_col_grid_mobile', $form_state->getValue('default_col_grid_mobile'))
      ->set('enable_hits_per_page', $form_state->getValue('enable_hits_per_page'))
      ->set('render_single_result_facets', $form_state->getValue('render_single_result_facets'))
      ->set('exclude_render_single_result_facets', $form_state->getValue('exclude_render_single_result_facets'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
