<?php

namespace Drupal\alshaya_search\Form;

use Drupal\alshaya_custom\AlshayaDynamicConfigValueBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Alshaya Srp Sort Label Settings Form.
 */
class AlshayaSrpSortLabelSettingsForm extends AlshayaDynamicConfigValueBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_srp_sort_label_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_search.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Sort options from config.
    $position_settings = $this->config('alshaya_search.settings');
    $sort_options_label = static::schemaArrayToKeyValue($position_settings->get('sort_options_labels'));

    $form['sort_options_labels'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Search sort options labels'),
      '#default_value' => $this->arrayValuesToString($sort_options_label),
      '#rows' => 10,
      '#element_validate' => [[get_class($this), 'validateLabelValues']],
      '#description' => $this->allowedValuesDescription(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get form values.
    $sort_options_labels = $form_state->getValue('sort_options_labels');
    $labels = static::valuesToSchemaLikeArray($sort_options_labels);

    $config = $this->config('alshaya_search.settings');
    $config->set('sort_options_labels', $labels);
    $config->save();
    return parent::submitForm($form, $form_state);
  }

}
