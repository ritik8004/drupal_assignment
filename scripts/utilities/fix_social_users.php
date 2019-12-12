<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Drush scr script to fix social users.
 */

/** @var \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper */
$api_wrapper = \Drupal::service('acq_commerce.api');

$query = \Drupal::database()->select('users_field_data');
$query->innerJoin('social_auth', 'social_auth', 'social_auth.user_id = users_field_data.uid');
$query->fields('users_field_data', ['uid', 'mail']);
$query->where('users_field_data.acq_customer_id IS NULL');
$users = $query->execute()->fetchAllKeyed();

foreach ($users as $uid => $mail) {
  if (strpos($mail, 'example.com') > -1) {
    continue;
  }

  try {
    $customer = $api_wrapper->getCustomer($mail, FALSE);
  }
  catch (\Exception $e) {
    $customer_not_found[] = $mail;
    continue;
  }
  $customer_id = $customer['customer_id'];

  $query = \Drupal::database()->select('users_field_data');
  $query->fields('users_field_data', ['uid', 'mail']);
  $query->condition('users_field_data.acq_customer_id', $customer_id);
  $customers_for_id = $query->execute()->fetchAll();

  if ($customers_for_id) {
    $user_has_customer[] = [
      'mail' => $mail,
      'social_user_id' => $uid,
      'customer_user_id' => $customers_for_id->uid,
    ];
  }
  else {
    $social_users_no_customer_account[] = $mail;

    $account = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    $account->get('field_first_name')->setValue($customer['firstname']);
    $account->get('field_last_name')->setValue($customer['lastname']);
    $account->get('acq_customer_id')->setValue($customer_id);
    $account->save();
  }
}

if (isset($customer_not_found)) {
  print 'Customer not found for social users: ' . count($customer_not_found) . PHP_EOL;
  print_r($customer_not_found);
  print PHP_EOL . PHP_EOL;
}

if (isset($user_has_customer)) {
  print 'Found users which have separate social and customer accounts: ' . count($user_has_customer) . PHP_EOL;
  print_r($user_has_customer);
  print PHP_EOL . PHP_EOL;
}

if (isset($social_users_no_customer_account)) {
  print 'Users with social account and no customer account: ' . count($social_users_no_customer_account) . PHP_EOL;
  print_r($social_users_no_customer_account);
  print PHP_EOL . PHP_EOL;
}
