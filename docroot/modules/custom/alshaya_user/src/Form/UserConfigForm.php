<?php

namespace Drupal\alshaya_user\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CartConfigForm.
 */
class UserConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_user_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_user.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_user.settings');
    $config->set('terms_conditions', $form_state->getValue('terms_conditions'));
    $config->set('user_register_complete', $form_state->getValue('user_register_complete'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_user.settings');

    $form['terms_conditions'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Terms and Conditions label'),
      '#required' => TRUE,
      '#default_value' => $config->get('terms_conditions.value'),
    ];

    $form['user_register_complete'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#description' => $this->t('Use [email] in the text where you want to show the dynamic email address.'),
      '#title' => $this->t('User registration completion message'),
      '#required' => TRUE,
      '#default_value' => $config->get('user_register_complete.value'),
    ];

    return $form;
  }

}
