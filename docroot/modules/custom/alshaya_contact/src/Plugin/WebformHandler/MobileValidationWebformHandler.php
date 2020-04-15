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

    // Check web or app. This validation only on app.
    if (\Drupal::routeMatch()->getRouteName() == 'rest.webform_rest_submit.POST') {
      $mobile_number = $webform_submission->getElementData('mobile_number_full');
      $preference_channel = $webform_submission->getElementData('select_your_preference_of_channel_of_communication');
      $original_mobile_number = $webform_submission->getElementData('mobile_number');
      if ($preference_channel == 'Mobile' && empty($mobile_number)) {
        $error_msg = $this->t('Mobile Number is mandatory');
      }

      if (!empty($mobile_number)) {
        $util = \Drupal::service('mobile_number.util');
        if (!is_object($util->getMobileNumber($mobile_number))) {
          $error_msg = $this->t('The phone number %value provided for %field is not a valid mobile number for country %country.',
            [
              '%value' => $mobile_number,
              '%field' => $this->t('Mobile Number'),
              '%country' => $original_mobile_number['country-code'],
            ]);
        }
      }
      if (!empty($error_msg)) {
        $form_state->setErrorByName('mobile_number', $error_msg);
      }
    }
  }

}
