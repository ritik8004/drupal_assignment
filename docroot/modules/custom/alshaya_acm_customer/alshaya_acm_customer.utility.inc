<?php

/**
 * @file
 * Utility function for alshaya_acm_customer.
 */

use Drupal\user\Entity\User;

/**
 * Update changes in Drupal to Magento.
 *
 * @param \Drupal\user\Entity\User $user
 *   User object.
 */
function alshaya_acm_customer_update_customer_data(User $user) {
  /** @var \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper */
  $api_wrapper = \Drupal::service('acq_commerce.api');
  $api_wrapper->updateCustomer(
    $user->get('acq_customer_id')->getString(),
    $user->get('field_first_name')->getString(),
    $user->get('field_last_name')->getString(),
    $user->getEmail()
  );
}

/**
 * Function to get value of communication_preference as per keys used in Drupal.
 *
 * @param string $value
 *   API Value.
 *
 * @return string
 *   Drupal value.
 */
function alshaya_acm_customer_get_communication_preference_drupal_value($value) {
  $preference = \Drupal::config('alshaya_acm_customer.api_mappings')->get('communication_preferences');
  $preference = array_flip($preference);
  return isset($preference[$value]) ? $preference[$value] : '';
}

/**
 * Function to get value of communication_preference as per keys used in API.
 *
 * @param string $value
 *   Drupal Value.
 *
 * @return string
 *   API value.
 */
function alshaya_acm_customer_get_communication_preference_api_value($value) {
  $preference = \Drupal::config('alshaya_acm_customer.api_mappings')->get('communication_preferences');
  return isset($preference[$value]) ? $preference[$value] : '';
}
