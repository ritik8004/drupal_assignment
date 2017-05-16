<?php

/**
 * @file
 * Utility function for alshaya_acm_customer.
 */

use Drupal\user\Entity\User;

/**
 * Update changes from Drupal to Magento.
 *
 * @param string $mail
 *   User's email address.
 * @param string $password
 *   Optional password to update password on Magento.
 *
 * @return array
 *   Customer array.
 */
function alshaya_acm_customer_update_customer_data($mail, $password = NULL) {
  // Get the fresh user object.
  /** @var \Drupal\user\Entity\User $user */
  $user = user_load_by_mail($mail);

  /** @var \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper */
  $api_wrapper = \Drupal::service('acq_commerce.api');
  $customer = $api_wrapper->updateCustomer(
    $user->get('acq_customer_id')->getString(),
    $user->get('field_first_name')->getString(),
    $user->get('field_last_name')->getString(),
    $user->getEmail(),
    $password
  );

  return $customer;
}

/**
 * Update changes from Magento to Drupal.
 *
 * @param \Drupal\user\Entity\User $user
 *   User object.
 * @param array $customer
 *   Customer data received from Magento.
 */
function alshaya_acm_customer_update_user_data(User $user, array $customer) {
  // Set fields.
  $user->get('field_first_name')->setValue($customer['firstname']);
  $user->get('field_last_name')->setValue($customer['lastname']);
  $user->get('acq_customer_id')->setValue($customer['customer_id']);

  // Invoke the alter hook to allow all modules to update the account.
  \Drupal::moduleHandler()->alter('alshaya_acm_customer_update_account', $user, $customer);

  $user->save();

  // Special case for communication_preference handled below.
  if (isset($customer['extension'], $customer['extension']['communication_preference'])) {
    $preference = alshaya_acm_customer_get_communication_preference_drupal_value($customer['extension']['communication_preference']);
    $user_data = \Drupal::service('user.data');
    $user_data->set('user', $user->id(), 'communication_preference', $preference);
  }
}

/**
 * Function to get value of communication_preference as per keys used in Drupal.
 *
 * @param string $value
 *   API Value.
 * @param bool $default
 *   Whether function should return the default value.
 *
 * @return string
 *   Drupal value.
 */
function alshaya_acm_customer_get_communication_preference_drupal_value($value, $default = FALSE) {
  $preferences = \Drupal::config('alshaya_acm_customer.api_mappings')->get('communication_preferences');

  // Flip the array to return reverse data.
  $preferences = array_flip($preferences);

  if (isset($preferences[$value])) {
    return $preferences[$value];
  }

  if ($default) {
    return reset($preferences);
  }

  return '';
}

/**
 * Function to get value of communication_preference as per keys used in API.
 *
 * @param string $value
 *   Drupal Value.
 * @param bool $default
 *   Whether function should return the default value.
 *
 * @return string
 *   API value.
 */
function alshaya_acm_customer_get_communication_preference_api_value($value, $default = TRUE) {
  $preferences = \Drupal::config('alshaya_acm_customer.api_mappings')->get('communication_preferences');

  if (isset($preferences[$value])) {
    return $preferences[$value];
  }

  if ($default) {
    return reset($preferences);
  }

  return '';
}
