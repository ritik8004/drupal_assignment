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

  $is_customer = alshaya_acm_customer_is_customer($user);

  // We don't update Magento for admin created users.
  if (empty($password) && !$is_customer) {
    return NULL;
  }

  /** @var \Drupal\alshaya_spc\Helper\AlshayaSpcApiHelper $api_wrapper */
  $api_wrapper = \Drupal::service('alshaya_spc.api_helper');

  $customer_array = [
    'customer_id' => $user->get('acq_customer_id')->getString(),
    'firstname' => $user->get('field_first_name')->getString(),
    'lastname' => $user->get('field_last_name')->getString(),
    'email' => $user->getEmail(),
  ];

  try {
    $customer = $api_wrapper->updateCustomer($customer_array, [
      'password' => $password,
    ]);
  }
  catch (\Exception $e) {
    // IF API Down or some other issue, log message is already added
    // in AgentRequestTrait. We can simply return empty array here.
    $customer = [];

    // Clear all messages first as there is success message available.
    \Drupal::messenger()->deleteAll();
    \Drupal::messenger()->addError(acq_commerce_api_down_global_error_message());
  }

  return $customer;
}

/**
 * Helper function to create user in Drupal.
 *
 * @param array $customer
 *   Customer data recieved from Magento.
 *
 * @return \Drupal\user\Entity\User|null
 *   User object or null.
 */
function alshaya_acm_customer_create_drupal_user(array $customer) {
  $lock = \Drupal::lock();
  $lock_key = 'alshaya_acm_customer_create_drupal_user_' . $customer['email'];
  // Lock the process for concurrent requests, to avoid creating multiple
  // accounts with same email-id.
  if ($lock->acquire($lock_key)) {
    try {
      /** @var \Drupal\user\Entity\User $user */
      $user = \Drupal::entityTypeManager()->getStorage('user')->create([]);
      $user->enforceIsNew();

      // Activate the user by default.
      $user->activate();

      // Set the email.
      $user->setEmail($customer['email']);

      // For Alshaya we know we are using email_registration module.
      $user->setUsername('email_registration_' . user_password());

      // Set a password entered by user for login.
      $user->setPassword(user_password(25));

      // Update the data in Drupal to match the values in Magento.
      alshaya_acm_customer_update_user_data($user, $customer);

      // Release the lock.
      $lock->release($lock_key);

      return $user;
    }
    catch (\Exception $e) {
      // Release the lock.
      $lock->release($lock_key);
      \Drupal::logger('alshaya_acm_customer')->error('Error occurred during creating user @message', [
        '@message' => $e->getMessage(),
      ]);
    }
  }
  else {
    \Drupal::logger('alshaya_acm_customer')->error('Trying to re-create the account with the email:@email', [
      '@email' => $customer['email'],
    ]);
  }

  return NULL;

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

  // Save here to ensure we use it while creating addresses.
  if ($user->isNew()) {
    $user->save();
  }

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
 * @return array
 *   Drupal value.
 */
function alshaya_acm_customer_get_communication_preference_drupal_value($value, $default = FALSE) {
  $values = [];

  // We get the values separated by comma from API.
  if (!empty($value)) {
    $values = explode(',', $value);
  }
  // If no value available and default is required.
  elseif ($default) {
    $values[] = 'email';
  }

  return $values;
}

/**
 * Function to get value of communication_preference as per keys used in API.
 *
 * @param array $value
 *   Drupal Value.
 * @param bool $default
 *   Whether function should return the default value.
 *
 * @return string
 *   API value.
 */
function alshaya_acm_customer_get_communication_preference_api_value(array $value, $default = TRUE) {
  $values = [];

  // We send array of values to API.
  if ($value) {
    $values = array_values($value);
  }
  // If value is empty and default is required.
  elseif ($default) {
    $values = ['email'];
  }

  return $values;
}
