<?php

namespace Drupal\alshaya_egift_card\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Alshaya Egift Card settings.
 */
class AlshayaEgiftCardSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_egift_card_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_egift_card.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_egift_card.settings');
    $form['egift_card_configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuration'),
      '#open' => TRUE,
    ];
    $form['egift_card_configuration']['enable_disable_egift_card'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('egift_card_enabled'),
      '#title' => $this->t('Enable Egift card on site.'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_egift_card.settings')
      ->set('egift_card_enabled', $form_state->getValue('enable_disable_egift_card'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
