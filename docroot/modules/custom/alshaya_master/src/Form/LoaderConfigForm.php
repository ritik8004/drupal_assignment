<?php

namespace Drupal\alshaya_master\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Loyalty Config Form.
 */
class LoaderConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_progress_loader_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_master.loader_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_master.loader_settings');

    // Loader on/off feature.
    $form['loader_on_off'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Enable/Disable progress loader'),
      '#open' => TRUE,
    ];

    $form['loader_on_off']['alshaya_page_progress_loader'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('alshaya_page_progress_loader'),
      '#title' => $this->t('Display progress loader on site.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_master.loader_settings');
    $config->set('alshaya_page_progress_loader', $form_state->getValue('alshaya_page_progress_loader'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
