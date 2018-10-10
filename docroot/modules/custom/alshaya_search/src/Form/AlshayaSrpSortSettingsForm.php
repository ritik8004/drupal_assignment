<?php

namespace Drupal\alshaya_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AlshayaPlpSortSettingsForm.
 */
class AlshayaSrpSortSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_srp_sort_settings_form';
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_search.settings');
    $config->set('sort_options', $form_state->getValue('sort_options'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('alshaya_search.settings');
    $form['sort_options'] = [
      '#type' => 'checkboxes',
      '#options' => [
        'created' => $this->t('New In'),
        'title' => $this->t('Name'),
        'final_price' => $this->t('Price'),
      ],
      '#title' => $this->t('Sort options'),
      '#description' => $this->t('Enabled sort options will show on SRP page. NOTE: Relevance option cannot be disable for search page.'),
      '#default_value' => $config->get('sort_options'),
    ];
    return $form;
  }

}
