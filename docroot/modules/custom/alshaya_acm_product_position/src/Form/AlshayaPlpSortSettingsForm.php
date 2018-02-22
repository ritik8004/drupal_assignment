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
    // Get config.
    $config = $this->config('alshaya_acm_product_position.settings');
    $sort_options = [];

    // Iterate over value to prepare array to store config. Mostly this is to
    // set the status value for sort option.
    foreach ($form_state->getValue('sort_options') as $key => $value) {
      $sort_options[$key] = [
        'status' => (bool) $value,
        'default_sort' => $config->get('sort_options.' . $key)['default_sort'],
      ];
    }

    $config->set('sort_options', $sort_options);
    $config->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['sort_options'] = [
      '#type' => 'checkboxes',
      '#options' => [
        'nid' => $this->t('Best Seller'),
        'created' => $this->t('New In'),
        'name_1' => $this->t('Name'),
        'final_price' => $this->t('Price'),
      ],
      '#title' => $this->t('Sort options'),
      '#description' => $this->t('Enabled sort options will show on PLP page.'),
      '#default_value' => array_keys(_alshaya_acm_product_position_get_config()),
    ];
    return $form;
  }

}
