<?php

namespace Drupal\bazaar_voice\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Review Sort Settings Form.
 */
class ReviewSortSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'review_sort_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['bazaar_voice_sort_review.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get form values.
    $sort_options = $form_state->getValue('sort_options');
    // Sort the options based on weight.
    uasort($sort_options, $this->weightArraySort(...));
    $result = [];
    // Prepare sort option array for saving in config.
    foreach ($sort_options as $key => $sort_option) {
      $result[$key] = $sort_option['enable'] ? $key : 0;
    }

    $config = $this->config('bazaar_voice_sort_review.settings');
    $config->set('sort_options', $result);
    $config->save();

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
          'group' => 'review_sort_options-order-weight',
        ],
      ],
    ];

    // Sort options from config by weight.
    self::arrangeOptionsByWeight(
      $form['sort_options'],
      $this->config('bazaar_voice_sort_review.settings')->get('sort_options')
    );

    return $form;
  }

  /**
   * Arrange given element by given order.
   *
   * @param array $element
   *   The array of elements.
   * @param array $sort_options
   *   Available sorting options.
   */
  public static function arrangeOptionsByWeight(array &$element, array $sort_options) {
    // Maintaining the weight.
    $weight = 0;
    foreach ($sort_options as $id => $title) {
      $option = $sort_options[$id] ?? 0;
      $element[$id]['#attributes']['class'][] = 'draggable';
      $element[$id]['#weight'] = $weight;

      $element[$id]['enable'] = [
        '#type' => 'checkbox',
        '#default_value' => (bool) $option,
      ];

      $element[$id]['label'] = [
        '#plain_text' => $id,
      ];

      $element[$id]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', ['@title' => $id]),
        '#title_display' => 'invisible',
        '#default_value' => $weight,
        '#attributes' => ['class' => ['review_sort_options-order-weight']],
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

}
