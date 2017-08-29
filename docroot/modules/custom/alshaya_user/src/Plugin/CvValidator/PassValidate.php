<?php

namespace Drupal\alshaya_user\Plugin\CvValidator;

use Drupal\clientside_validation\CvValidatorBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'passvalidate' validator.
 *
 * @CvValidator(
 *   id = "passvalidate",
 *   name = @Translation("Password Validate"),
 *   supports = {
 *     "attributes" = {"passvalidate"}
 *   }
 * )
 */
class PassValidate extends CvValidatorBase {

  /**
   * {@inheritdoc}
   */
  protected function getRules($element, FormStateInterface $form_state) {
    // Password validate message.
    return [
      'messages' => [
        'passvalidate' => $this->t('The password does not satisfy the password policies.'),
      ],
    ];
  }

}
