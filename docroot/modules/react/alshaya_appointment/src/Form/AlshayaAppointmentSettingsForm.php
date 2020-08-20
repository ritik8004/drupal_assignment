<?php

namespace Drupal\alshaya_appointment\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Alshaya Appointment Booking settings.
 */
class AlshayaAppointmentSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_appointment_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_appointment.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['alshaya_appointment']['customer_details_disclaimer_text'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Customer Details Disclaimer Text'),
      '#description' => $this->t('Disclaimer text to be displayed on customer details section of appointment booking.'),
      '#default_value' => $this->config('alshaya_appointment.settings')->get('customer_details_disclaimer_text.value'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_appointment.settings')
      ->set('customer_details_disclaimer_text', $form_state->getValue('customer_details_disclaimer_text'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
