<?php

namespace Drupal\alshaya_password_policy\Plugin\PasswordConstraint;

use Drupal\Core\Form\FormStateInterface;
use Drupal\password_policy\PasswordConstraintBase;
use Drupal\password_policy\PasswordPolicyValidation;
use Drupal\user\UserInterface;

/**
 * Enforces exclude spaces in the password.
 *
 * @PasswordConstraint(
 *   id = "password_spaces",
 *   title = @Translation("Password exclude spaces"),
 *   description = @Translation("Verifying that a password has spaces"),
 *   error_message = @Translation("Your password contains spaces.")
 * )
 */
class PasswordSpaces extends PasswordConstraintBase {

  /**
   * {@inheritdoc}
   */
  public function validate($password, UserInterface $user_context) {
    $validation = new PasswordPolicyValidation();
    if (strpos($password, ' ') > -1 || empty(trim($password))) {
      $validation->setErrorMessage($this->t('Password must not contain spaces.'));
    }
    return $validation;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['operation_info'] = [
      '#markup' => 'This constraint will restrict users to use spaces in the password.',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // No configuration to save.
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return $this->t('Password must exclude spaces');
  }

}
