<?php

namespace Drupal\alshaya_main_menu\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AlshayaMainMenuConfigForm.
 */
class AlshayaMainMenuConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_main_menu_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_main_menu.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_main_menu.settings');

    $form['highlight_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Highlight text'),
      '#default_value' => $config->get('highlight_text'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_main_menu.settings');
    $config->set('highlight_text', $form_state->getValue('highlight_text'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
