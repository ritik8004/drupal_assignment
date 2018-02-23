<?php

namespace Drupal\alshaya_acm_product_position\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

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
    $config = $this->config('alshaya_acm_product_position.settings');
    $config->set('sort_options', $form_state->getValue('sort_options'));
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
          'group' => 'plp_sort_options-order-weight',
        ],
      ],
    ];

    // Sort options.
    $sort_options = $this->config('alshaya_acm_product_position.settings')->get('sort_options');
    $options = [
      'created' => [
        'weight' => $sort_options['created']['weight'],
        'label' => $this->t('New IN'),
        'enable' => (bool) $sort_options['created']['enable'],
      ],
      'nid' => [
        'weight' => $sort_options['nid']['weight'],
        'label' => $this->t('Position'),
        'enable' => (bool) $sort_options['nid']['enable'],
      ],
      'name_1' => [
        'weight' => $sort_options['name_1']['weight'],
        'label' => $this->t('Name'),
        'enable' => (bool) $sort_options['name_1']['enable'],
      ],
      'final_price' => [
        'weight' => $sort_options['final_price']['weight'],
        'label' => $this->t('Final Price'),
        'enable' => (bool) $sort_options['final_price']['enable'],
      ],
    ];

    // Sort the dataset based on weight.
    uasort($options, [$this, 'weightArraySort']);

    foreach ($options as $id => $option) {
      $form['sort_options'][$id]['#attributes']['class'][] = 'draggable';
      $form['sort_options'][$id]['#weight'] = $option['weight'];

      $form['sort_options'][$id]['enable'] = [
        '#type' => 'checkbox',
        '#default_value' => $option['enable'],
      ];

      $form['sort_options'][$id]['label'] = [
        '#plain_text' => $option['label'],
      ];

      $form['sort_options'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $option['label']]),
        '#title_display' => 'invisible',
        '#default_value' => $option['weight'],
        '#attributes' => ['class' => ['plp_sort_options-order-weight']],
      ];
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
