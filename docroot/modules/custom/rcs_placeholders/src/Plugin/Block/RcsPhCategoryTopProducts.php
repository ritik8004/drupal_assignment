<?php

namespace Drupal\rcs_placeholders\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a decoupled block to display Top Products from Category.
 *
 * @Block(
 *   id = "rcs_ph_category_top_products",
 *   admin_label = @Translation("RCS Placeholders Top Products from Category"),
 *   category = @Translation("RCS Placeholders"),
 * )
 */
class RcsPhCategoryTopProducts extends RcsPhProductListBlock {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $current_configuration = $this->getConfiguration();

    // @todo implement autocomplete / dropwdown to choose the category from
    // Commerce Backend here.
    $form['category_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Category ID'),
      '#description' => $this->t('ID of the Category from Commerce Backend.'),
      '#default_value' => $current_configuration['category_id'] ?? '',
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['category_id'] = $form_state->getValue('category_id');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $build = parent::build();

    $build['wrapper']['#attributes']['data-param-id'] = $config['category_id'];

    return $build;
  }

}
