<?php

namespace Drupal\bazaar_voice\Form;

use Drupal\bazaar_voice\BazaarVoiceConfigValueBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Review Sort option settings Form.
 */
class ReviewSortOptionSettingsForm extends BazaarVoiceConfigValueBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'review_sort_option_settings_form';
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
    $review_sort_settings = $this->config('bazaar_voice_sort_review.settings');
    $sort_options = $review_sort_settings->get('sort_options');

    $form['sort_options'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Review sort options'),
      '#default_value' => $this->arrayValuesToString($sort_options),
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
    $sort_options = $form_state->getValue('sort_options');

    $config = $this->config('bazaar_voice_sort_review.settings');
    $config->set('sort_options', $sort_options);
    $config->save();
    return parent::submitForm($form, $form_state);
  }

}
