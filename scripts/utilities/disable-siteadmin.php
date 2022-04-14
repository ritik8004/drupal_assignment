<?php
// phpcs:ignoreFile

/**
 * @file
 * Drush scr script to disable users with example.com mail.
 */

/** @var \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper */
$api_wrapper = \Drupal::service('acq_commerce.api');
$logger = \Drupal::logger('disable-siteadmin');

$query = \Drupal::database()->select('users_field_data');
$query->fields('users_field_data', ['uid', 'mail']);
$query->condition('mail', '%example.com%', 'like');
$query->condition('status', '0', '>');
$users = $query->execute()->fetchAllKeyed();

foreach ($users as $uid => $mail) {
  $user = user_load_by_mail($mail);

  // Do nothing if not able to load the user.
  if (empty($user)) {
    continue;
  }

  $roles = array_flip($user->getRoles());
  unset($roles['authenticated']);

  // Do nothing if user doesn't have any other role.
  if (count($roles) == 0) {
    continue;
  }

  $logger->notice('Disabling user with mail @mail, uid: @uid', [
    '@mail' => $mail,
    '@uid' => $uid,
  ]);

  $user->block();
  $user->setPassword(user_password(20));
  $user->save();
}
