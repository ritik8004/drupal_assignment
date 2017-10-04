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
    $config = $this->config('alshaya_acm_product_position.settings');
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
      '#default_value' => $config->get('sort_options'),
    ];
    return $form;
  }

}
