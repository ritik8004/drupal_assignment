<?php

namespace Drupal\alshaya_contact\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Webform validate handler.
 *
 * @WebformHandler(
 *   id = "webform_mobile_number_validation",
 *   label = @Translation("Mobile Number Validation"),
 *   category = @Translation("Validation"),
 *   description = @Translation("Mobile Number Validation."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class MobileValidationWebformHandler extends WebformHandlerBase {

  use StringTranslationTrait;

  /**
   * Check the mobile number is valid or not.
   */
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $preference_channel = $webform_submission->getElementData('select_your_preference_of_channel_of_communication');
    $mobile_number = $webform_submission->getElementData('dummy_field_mobile_number');
    $original_mobile_number = $webform_submission->getElementData('mobile_number');
    if ($preference_channel == 'Mobile' && empty($mobile_number)) {
      $form_state->setErrorByName('mobile_number', $this->t('Mobile Number is mandatory'));
    }
    if (!empty($mobile_number)) {
      $util = \Drupal::service('mobile_number.util');
      $mobile_number_obj = $util->getMobileNumber($mobile_number);
      if (!is_object($mobile_number_obj)) {
        $form_state->setErrorByName('mobile_number', $this->t('The phone number %value provided for %field is not a valid mobile number for country %country.',
          [
            '%value' => $mobile_number,
            '%field' => $this->t('Mobile Number'),
            '%country' => $original_mobile_number['country-code'],
          ]));
      }
    }
  }

}
