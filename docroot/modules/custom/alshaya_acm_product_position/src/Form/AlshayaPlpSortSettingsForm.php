<?php

namespace Drupal\alshaya_acm_product_position\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;

/**
 * Class Alshaya Plp Sort Settings Form.
 */
class AlshayaPlpSortSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_plp_sort_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_acm_product_position.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get form values.
    $sort_options = $form_state->getValue('sort_options');
    // Sort the options based on weight.
    uasort($sort_options, [$this, 'weightArraySort']);
    $result = [];
    // Prepare sort option array for saving in config.
    foreach ($sort_options as $key => $sort_option) {
      $result[$key] = $sort_option['enable'] ? $key : 0;
    }

    $config = $this->config('alshaya_acm_product_position.settings');
    $config->set('sort_options', $result);
    $config->save();

    // Invalidate cache so that the change takes effect.
    Cache::invalidateTags([
      'search_api_list:product',
      'config:block.block.exposedformalshaya_product_listblock_1',
      'config:block.block.exposedformalshaya_product_listblock_2',
    ]);

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['sort_options'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Enable/Disable'),
        $this->t('Name'),
        $this->t('Weight'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'plp_sort_options-order-weight',
        ],
      ],
    ];

    // Sort options from config by weight.
    self::arrangeOptionsByWeight(
      $form['sort_options'],
      $this->config('alshaya_acm_product_position.settings')->get('available_sort_options'),
      $this->config('alshaya_acm_product_position.settings')->get('sort_options')
    );

    return $form;
  }

  /**
   * Arrange given element by given order.
   *
   * @param array $element
   *   The array of elements.
   * @param array $available_sort_options
   *   The list of all available sort options.
   * @param array $default_order
   *   The default sort order by which it needs to be arranged.
   */
  public static function arrangeOptionsByWeight(array &$element, array $available_sort_options, array $default_order) {
    // Remove empty options.
    $default_order = array_filter($default_order);
    // Sort the form options based on available_sort_options.
    $options = array_replace(array_flip($default_order), $available_sort_options);

    // Get labels from sort labels config.
    $labels = \Drupal::service('alshaya_acm_product_position.sort_labels')
      ->getSortOptionsLabels();

    // Maintaining the weight.
    $weight = 0;
    foreach ($options as $id => $title) {
      $option = $default_order[$id] ?? 0;
      $element[$id]['#attributes']['class'][] = 'draggable';
      $element[$id]['#weight'] = $weight;

      $element[$id]['enable'] = [
        '#type' => 'checkbox',
        '#default_value' => (bool) $option,
      ];

      // Get available label.
      $available_label = self::getAvailableLabel($labels, $id);

      // If label is not available default label(title)
      // Will be treated as label.
      $element[$id]['label'] = [
        '#plain_text' => $available_label ?: $title,
      ];

      $element[$id]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', ['@title' => $title]),
        '#title_display' => 'invisible',
        '#default_value' => $weight,
        '#attributes' => ['class' => ['plp_sort_options-order-weight']],
      ];

      // Increase the weight.
      $weight++;
    }
  }

  /**
   * Sort the two weights.
   *
   * @param int $a
   *   Weight 1.
   * @param int $b
   *   Weight 2.
   *
   * @return int
   *   Sort status.
   */
  public static function weightArraySort($a, $b) {
    if (isset($a['weight']) && isset($b['weight'])) {
      return $a['weight'] < $b['weight'] ? -1 : 1;
    }
    return 0;
  }

  /**
   * Returns label by combining all available keys for passed id.
   */
  protected static function getAvailableLabel($labels, $id) {
    $available_label = NULL;
    if (!empty($labels)) {
      foreach ($labels as $label_key => $label) {
        if (str_contains($label_key, $id)) {
          if ($available_label === NULL) {
            $available_label = $label;
          }
          else {
            // Append label with |, if got more than 1 for given id.
            // For example- name_1 has to labels available.
            // name_1 ASC - Name A to Z,
            // name_1 DESC - Name Z to A,
            // In this case label will be Name A to Z | Name Z to A.
            $available_label .= ' | ' . $label;
          }
        }
      }
    }
    return $available_label;
  }

}
