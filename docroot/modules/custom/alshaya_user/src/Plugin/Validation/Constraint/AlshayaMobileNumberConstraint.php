<?php

namespace Drupal\alshaya_user\Plugin\Validation\Constraint;

use Drupal\mobile_number\Plugin\Validation\Constraint\MobileNumberConstraint;

/**
 * Validates Mobile number fields.
 *
 * @Constraint(
 *   id = "AlshayaMobileNumber",
 *   label = @Translation("Mobile number constraint", context = "Validation"),
 * )
 */
class AlshayaMobileNumberConstraint extends MobileNumberConstraint {

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Drupal\alshaya_user\Plugin\Validation\Constraint\AlshayaMobileNumberValidator';
  }

}
