<?php

namespace Drupal\bazaar_voice\Form;

use Drupal\bazaar_voice\BazaarVoiceConfigValueBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Review Sort Label Settings Form.
 */
class ReviewSortLabelSettingsForm extends BazaarVoiceConfigValueBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'review_sort_label_settings_form';
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Sort options from config.
    $position_settings = $this->config('bazaar_voice_sort_review.settings');
    $sort_options_label = static::schemaArrayToKeyValue($position_settings->get('sort_options_labels'));

    $form['sort_options_labels'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Review sort options labels'),
      '#default_value' => $this->arrayValuesToString($sort_options_label),
      '#rows' => 10,
      '#element_validate' => [[$this::class, 'validateLabelValues']],
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

    $config = $this->config('bazaar_voice_sort_review.settings');
    $config->set('sort_options_labels', $labels);
    $config->save();
    return parent::submitForm($form, $form_state);
  }

}
