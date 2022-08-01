<?php

namespace Drupal\bazaar_voice\Form;

use Drupal\bazaar_voice\BazaarVoiceConfigValueBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class BazaarVoice Error Messages Settings Form.
 */
class ErrorMessagesSettingsForm extends BazaarVoiceConfigValueBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'error_messages_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['bazaar_voice_error_messages.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Error messages from config.
    $error_messages_settings = $this->config('bazaar_voice_error_messages.settings');
    $error_messages = static::schemaArrayToKeyValue($error_messages_settings->get('error_messages'));

    $form['error_messages'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Bazaar voice error messages'),
      '#default_value' => $this->arrayValuesToString($error_messages),
      '#rows' => 10,
      '#element_validate' => [[$this::class, 'validateLabelValues']],
      '#description' => $this->allowedValuesDescription(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get form values.
    $error_messages = $form_state->getValue('error_messages');
    $messages = static::valuesToSchemaLikeArray($error_messages);

    $config = $this->config('bazaar_voice_error_messages.settings');
    $config->set('error_messages', $messages);
    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
