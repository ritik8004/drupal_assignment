<?php

namespace Drupal\alshaya_options_list\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Autocomplete search form for the attributes page.
 *
 * @internal
 */
class AlshayaOptionsListAutocompleteForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_options_list_autocomplete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['alshaya_options_list_autocomplete_form'] = [
      '#type' => 'textfield',
      '#title' => 'Search',
      '#autocomplete_route_name' => 'alshaya_options_list.autocomplete',
      '#autocomplete_route_parameters' => ['param' => 'value'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
