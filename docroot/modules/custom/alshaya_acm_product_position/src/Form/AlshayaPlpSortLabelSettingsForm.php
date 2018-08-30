<?php

namespace Drupal\alshaya_acm_product_position\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class AlshayaPlpSortSettingsForm.
 */
class AlshayaPlpSortLabelSettingsForm extends AlshayaSortOptionsLabelBase {

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

    // Sort options from config.
    $position_settings = $this->config('alshaya_acm_product_position.settings');
    $sort_options_label = static::simplifyAllowedValues($position_settings->get('sort_options_labels'));

    $form['sort_options_labels'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Product sort options labels'),
      '#default_value' => $this->allowedValuesString($sort_options_label),
      '#rows' => 10,
      '#element_validate' => [[get_class($this), 'validateAllowedValues']],
    ];

    return $form;
  }

  /**
   * Callback for #element_validate options field allowed values.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form for the form this element belongs to.
   *
   * @see \Drupal\Core\Render\Element\FormElement::processPattern()
   */
  public static function validateAllowedValues(array $element, FormStateInterface $form_state) {
    $values = static::extractAllowedValues($element['#value']);

    if (!is_array($values)) {
      $form_state->setError($element, t('Allowed values list: invalid input.'));
    }
    else {
      $form_state->setValueForElement($element, $values);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get form values.
    $sort_options = $form_state->getValue('sort_options_labels');
    $labels = static::structureAllowedValues($sort_options);

    $config = $this->config('alshaya_acm_product_position.settings');
    $config->set('sort_options_labels', $labels);
    $config->save();
    return parent::submitForm($form, $form_state);
  }

}
