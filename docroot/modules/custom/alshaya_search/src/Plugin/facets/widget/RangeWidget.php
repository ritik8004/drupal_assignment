<?php

namespace Drupal\alshaya_search\Plugin\facets\widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\FacetInterface;
use Drupal\facets\Plugin\facets\widget\CheckboxWidget;

/**
 * The Range checkbox / radios widget.
 *
 * @FacetsWidget(
 *   id = "range_checkbox",
 *   label = @Translation("List of ranged checkboxes"),
 *   description = @Translation("A configurable widget that shows a list of ranged checkboxes"),
 * )
 */
class RangeWidget extends CheckboxWidget {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'granularity' => 20,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $configuration = $this->getConfiguration();

    $form += parent::buildConfigurationForm($form, $form_state, $facet);

    $form['granularity'] = [
      '#type' => 'number',
      '#title' => $this->t('Granularity'),
      '#default_value' => $configuration['granularity'],
      '#description' => $this->t('The numeric size of the steps to group the result facets in.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryType() {
    // The `numeric` type maps to `search_api_granular` which will correctly
    // handle ranges.
    return 'numeric';
  }

}
