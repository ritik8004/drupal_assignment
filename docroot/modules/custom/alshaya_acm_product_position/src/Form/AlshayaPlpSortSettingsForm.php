<?php

namespace Drupal\alshaya_acm_product_position\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;

/**
 * Class AlshayaPlpSortSettingsForm.
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

    // Sort options from config.
    $sort_options = $this->config('alshaya_acm_product_position.settings')->get('sort_options');

    // Sort options.
    $options = [
      'nid' => $this->t('Position'),
      'created' => $this->t('New IN'),
      'name_1' => $this->t('Name'),
      'final_price' => $this->t('Final Price'),
    ];

    // Sort the form options based on the config.
    $options = array_replace(array_flip($sort_options), $options);

    // Maintaining the weight.
    $weight = 0;
    foreach ($options as $id => $title) {
      $option = isset($sort_options[$id]) ? $sort_options[$id] : 0;
      $form['sort_options'][$id]['#attributes']['class'][] = 'draggable';
      $form['sort_options'][$id]['#weight'] = $weight;

      $form['sort_options'][$id]['enable'] = [
        '#type' => 'checkbox',
        '#default_value' => (bool) $option,
      ];

      $form['sort_options'][$id]['label'] = [
        '#plain_text' => $title,
      ];

      $form['sort_options'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $title]),
        '#title_display' => 'invisible',
        '#default_value' => $weight,
        '#attributes' => ['class' => ['plp_sort_options-order-weight']],
      ];

      // Increase the weight.
      $weight++;
    }

    return $form;
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
  protected function weightArraySort($a, $b) {
    if (isset($a['weight']) && isset($b['weight'])) {
      return $a['weight'] < $b['weight'] ? -1 : 1;
    }
    return 0;
  }

}
