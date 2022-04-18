<?php

namespace Drupal\alshaya_online_returns\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Alshaya Return Confirmation configuration form.
 */
class AlshayaReturnConfirmationConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_return_confirmation_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_online_returns.return_confirmation'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_online_returns.return_confirmation');

    // Preparing options for hide options.
    $hide_options = [
      0 => $this->t('No'),
      1 => $this->t('Yes'),
    ];

    // Preparing options for icon classes.
    $icon_class_options = [
      'print' => $this->t('print return label'),
      'packitem' => $this->t('pack item'),
      'refund' => $this->t('receive refund'),
    ];

    $field_rows = $config->get('rows');

    // Rendering form fields for title, description, icon
    // and hide for each row item defined in config.
    foreach ($field_rows as $key => $value) {
      $form['return_confirmation']['rows']['row_' . $key . '_title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Title for first row'),
        '#description' => $this->t('Label for first row in return confirmation page.'),
        '#default_value' => $config->get('rows')[$key]['title'],
      ];

      $form['return_confirmation']['rows'][$key]['row_' . $key . '_description'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Description for first row'),
        '#description' => $this->t('Description text for first row in return confirmation page.'),
        '#default_value' => $config->get('rows')[$key]['description'],
      ];

      $form['return_confirmation']['rows'][$key]['row_' . $key . '_icon'] = [
        '#type' => 'select',
        '#options' => $icon_class_options,
        '#title' => $this->t('Select corresponding icon text'),
        '#description' => $this->t('Icon text will be used to display respective icons.'),
        '#default_value' => $config->get('rows')[$key]['icon'],
      ];

      $form['return_confirmation']['rows'][$key]['row_' . $key . '_hide_this_row'] = [
        '#type' => 'select',
        '#options' => $hide_options,
        '#title' => $this->t('Hide this row'),
        '#description' => $this->t('Hide first row from display'),
        '#default_value' => $config->get('rows')[$key]['hide_this_row'],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_online_returns.return_confirmation');
    $field_rows = $config->get('rows');
    // Get all row fields values and map them into an empty array.
    $row_values = $form_state->getValues();
    if (!empty($row_values)) {
      foreach ($field_rows as $key => $value) {
        if (isset($values['row_' . $key . '_title'])) {
          $rows[$key]['title'] = $form_state->getValue('row_' . $key . '_title');
        }
        if (isset($values['row_' . $key . '_description'])) {
          $rows[$key]['description'] = $form_state->getValue('row_' . $key . '_description');
        }
        if (isset($values['row_' . $key . '_icon'])) {
          $rows[$key]['icon'] = $form_state->getValue('row_' . $key . '_icon');
        }
        if (isset($values['row_' . $key . '_hide_this_row'])) {
          $rows[$key]['hide_this_row'] = $form_state->getValue('row_' . $key . '_hide_this_row');
        }
      }
      // Save the row fields array in config.
      $config->set('rows', $rows)->save();
    }

    parent::submitForm($form, $form_state);
  }

}
