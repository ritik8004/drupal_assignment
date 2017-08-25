<?php

/**
 * @file
 * Drush commands for alshaya_acm_customer.
 */

/**
 * Implements hook_drush_command().
 */
function alshaya_acm_customer_drush_command() {
  $items = [];

  $items['customer-create'] = [
    'description' => 'Create a user account with the specified name.',
    'aliases' => ['acccrt'],
    'arguments' => [
      'firstname' => 'First name of the customer to add',
      'lastname' => 'Last name of the customer to add',
      'mail' => 'E-mail of the customer to add',
      'password' => 'Password of the customer account to add',
    ],
    'required-arguments' => TRUE,
    'examples' => [
      'drush customer-create fname lname mail pass@123' => 'Create a new customer account',
    ],
  ];

  return $items;
}

/**
 * Creates a new user account.
 */
function drush_alshaya_acm_customer_customer_create($firstname, $lastname, $mail, $password) {
  /** @var \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper */
  $api_wrapper = \Drupal::service('acq_commerce.api');

  try {
    $customer = [
      'firstname' => $firstname,
      'lastname' => $lastname,
      'email' => $mail,
    ];

    $customer = $api_wrapper->updateCustomer($customer, $password);

    \Drupal::moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.utility');
    $account = alshaya_acm_customer_create_drupal_user($customer);
    drush_print_r($account->toArray());
  }
  catch (\Exception $e) {
    drush_print(dt('Error: Could not create a new user account for the mail @mail.', [
      '@mail' => $mail,
    ]));

    drush_print(dt('Message: @message', [
      '@message' => $e->getMessage(),
    ]));
  }
}
