<?php

namespace Drupal\alshaya_user\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\mobile_number\Plugin\Validation\Constraint\MobileNumberValidator;

/**
 * Alter unique type validation on mobile number.
 */
class AlshayaMobileNumberValidator extends MobileNumberValidator {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function validate($item, Constraint $constraint) {
    // Overriding the original validate().
    parent::validate($item, $constraint);
    // Get all violations.
    $violations = $this->context->getViolations();
    // Check if any violation available.
    if (!empty($violations)) {
      for ($i = 0; $i < (is_countable($violations) ? count($violations) : 0); $i++) {
        $violation = $violations->get($i);
        $parameters = $violation->getParameters();
        $message_template = $violation->getMessageTemplate();
        // Remove violation which is of type unique and add new violation.
        if ($message_template == $constraint->unique) {
          $violations->remove($i);
          $this->context->addViolation($constraint->unique, [
            '@value' => $parameters['@value'],
            '@entity_type' => $this->t('customer'),
            '@field_name' => $parameters['@field_name'],
          ]);
        }
      }
    }
  }

}
