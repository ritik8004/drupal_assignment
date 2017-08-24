<?php

namespace Drupal\alshaya_user\Plugin\CvValidator;

use Drupal\clientside_validation\CvValidatorBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'specialchar' validator.
 *
 * @CvValidator(
 *   id = "specialchar",
 *   name = @Translation("Special Character"),
 *   supports = {
 *     "attributes" = {"specialchar"}
 *   }
 * )
 */
class SpecialChar extends CvValidatorBase {

  /**
   * {@inheritdoc}
   */
  protected function getRules($element, FormStateInterface $form_state) {
    // Drupal already adds the specialchar attribute, so we don't need to set
    // the specialchar rule.
    return [
      'messages' => [
        'specialchar' => $this->t('Please enter a valid @title with no special characters.', ['@title' => $this->getElementTitle($element)]),
      ],
    ];
  }

}
