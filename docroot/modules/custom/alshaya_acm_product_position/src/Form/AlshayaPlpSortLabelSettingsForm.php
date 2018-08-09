<?php

namespace Drupal\alshaya_acm_product_position\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AlshayaPlpSortSettingsForm.
 */
class AlshayaPlpSortLabelSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_plp_sort_label_settings_form';
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['sort_options'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Admin Label'),
        $this->t('Label'),
      ],
    ];

    // Sort options from config.
    $position_settings = $this->config('alshaya_acm_product_position.settings');
    $sort_options_label = $position_settings->get('sort_options_labels');

    foreach ($sort_options_label as $key => $value) {
      $form['sort_options'][$key]['admin_label'] = [
        '#plain_text' => $key,
      ];

      $form['sort_options'][$key]['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('label for @title', ['@title' => $value]),
        '#title_display' => 'invisible',
        // @codingStandardsIgnoreLine
        '#default_value' => $this->t($value),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get form values.
    $sort_options = $form_state->getValue('sort_options');
    $labels = array_map(function ($sort_option) {
      return $sort_option['label'];
    }, $sort_options);

    $config = $this->config('alshaya_acm_product_position.settings');
    $config->set('sort_options_labels', $labels);
    $config->save(TRUE);
    return parent::submitForm($form, $form_state);
  }

}
