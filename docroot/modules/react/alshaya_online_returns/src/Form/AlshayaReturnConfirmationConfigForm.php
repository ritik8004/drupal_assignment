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

    // Fetch row labels for config field rows.
    $row_labels = $this->getRows();
    // Rendering form fields for title, description, icon
    // and hide for each row item defined in config.
    foreach ($row_labels as $key => $label) {
      $form['return_confirmation']['rows']['row_' . $key . '_title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Title for @row_label row', [
          '@row_label' => $label,
        ]),
        '#description' => $this->t('Label for @row_label row in whats next section.', [
          '@row_label' => $label,
        ]),
        '#default_value' => $config->get('rows')[$key]['title'],
      ];

      $form['return_confirmation']['rows'][$key]['row_' . $key . '_description'] = [
        '#type' => 'text_format',
        '#format' => 'rich_text',
        '#title' => $this->t('Description for @row_label row', [
          '@row_label' => $label,
        ]),
        '#description' => $this->t('Description text for @row_label row in whats next section.', [
          '@row_label' => $label,
        ]),
        '#default_value' => $config->get('rows')[$key]['description']['value'],
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
        '#description' => $this->t('Hide @row_label row from display', [
          '@row_label' => $label,
        ]),
        '#default_value' => $config->get('rows')[$key]['hide_this_row'],
      ];
    }

    $form['return_confirmation']['return_date_format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Return Date format'),
      '#description' => $this->t('Date format for return info, please note this will be used in JAVASCRIPT.'),
      '#default_value' => $config->get('return_date_format'),
    ];

    $form['return_confirmation']['customer_service_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Customer service number'),
      '#description' => $this->t('Number to contact customer service to return orders not available for online returns like big ticket, white glove items.'),
      '#default_value' => $config->get('customer_service_number'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $rows = [];
    $config = $this->config('alshaya_online_returns.return_confirmation');
    // Fetch row labels for config field rows.
    $row_labels = $this->getRows();
    // Get all row fields values and map them into an empty array.
    $row_values = $form_state->getValues();
    if (!empty($row_values)) {
      foreach ($row_labels as $key => $value) {
        if (isset($row_values['row_' . $key . '_title'])) {
          $rows[$key]['title'] = $row_values['row_' . $key . '_title'];
        }
        if (isset($row_values['row_' . $key . '_description'])) {
          $rows[$key]['description']['value'] = $row_values['row_' . $key . '_description']['value'];
          $rows[$key]['description']['format'] = 'rich_text';
        }
        if (isset($row_values['row_' . $key . '_icon'])) {
          $rows[$key]['icon'] = $row_values['row_' . $key . '_icon'];
        }
        if (isset($row_values['row_' . $key . '_hide_this_row'])) {
          $rows[$key]['hide_this_row'] = $row_values['row_' . $key . '_hide_this_row'] === '0' ? FALSE : TRUE;
        }
      }
      // Save the row fields array in config.
      $config->set('rows', $rows);
    }
    // Save date format for return date.
    $config->set('return_date_format', $form_state->getValue('return_date_format'));
    $config->set('customer_service_number', $form_state->getValue('customer_service_number'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * Wrapper function to prepare row labels for configuration form.
   *
   * @return array
   *   Row labels array.
   */
  private function getRows() {
    // Adding key value pair for each row label.
    return [
      0 => $this->t('first'),
      1 => $this->t('second'),
      2 => $this->t('third'),
    ];
  }

}
