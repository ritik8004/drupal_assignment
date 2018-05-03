<?php

/**
 * @file
 * Provides Drush commands for alshaya_master.
 */

use Drupal\Core\Site\Settings;
use Drupal\lightning_core\ConfigHelper;
use Drupal\locale\Locale;
use Drupal\user\Entity\User;

/**
 * Implements hook_drush_command().
 */
function alshaya_master_drush_command() {
  $commands = [];

  $commands['alshaya-post-drupal-install'] = [
    'description' => 'Code to be executed only once post install.',
    'aliases' => ['apdi'],
    'options' => [
      'brand_module' => 'The brand module to install.',
      'country_code' => 'The country code the module.',
    ],
  ];

  // As this is dangerous operation, it intentionally doesn't have an alias.
  $commands['alshaya-scrub-users'] = [
    'description' => 'Delete all users that have only autheticated user role.',
  ];

  return $commands;
}

/**
 * Implements drush command alshaya-post-drupal-install.
 */
function drush_alshaya_master_alshaya_post_drupal_install() {
  $post_install_status = \Drupal::state()->get('alshaya_master_post_drupal_install', 'not done');
  $modules = \Drupal::state()->get('system.module.files', []);

  // Determine which country module to install.
  $country_code = drush_get_option('country_code', '');
  $country_code = empty($country_code) ? Settings::get('country_code') : $country_code;
  $country_module = 'alshaya_' . strtolower($country_code);

  if (isset($modules[$country_module])) {
    if (empty(\Drupal::configFactory()->get('alshaya.installed_country')->get('module'))) {
      drush_print(dt('Enabling the @country_module country module.', ['@country_module' => $country_module]));

      // Install the module.
      \Drupal::service('module_installer')->install([$country_module]);

      // Update config with installed brand and module names.
      \Drupal::configFactory()->getEditable('alshaya.installed_country')
        ->set('module', $country_module)
        ->save();
    }
    else {
      drush_print(dt("Country module @country_module can't be enabled because the site is already configured with @existing_country_module.",
        [
          '@country_module' => $country_module,
          '@existing_country_module' => \Drupal::configFactory()->get('alshaya.installed_country')->get('module'),
        ]));
    }
  }
  else {
    drush_print(dt('Country module @country_module does not exists.', ['@country_module' => $country_module]));
  }

  // Determine which brand to install.
  $brand_module = drush_get_option('brand_module', '');

  // Get the current installed profile.
  $profile = str_replace('alshaya_', '_', \Drupal::service('config.storage')->read('core.extension')['profile']);

  // Try to look for transac and non transac specific module for brand before
  // brand installing brand module. i.e. alshaya_vs_transac.
  if (isset($modules[$brand_module . $profile])) {
    $brand_module = $brand_module . $profile;
  }

  if (!empty($brand_module)) {
    if (isset($modules[$brand_module])) {
      if (empty(\Drupal::configFactory()->get('alshaya.installed_brand')->get('module'))) {
        drush_print(dt('Enabling the @brand_module brand module.', ['@brand_module' => $brand_module]));

        // Install the module.
        \Drupal::service('module_installer')->install([$brand_module]);

        // Update config with installed brand and module names.
        \Drupal::configFactory()->getEditable('alshaya.installed_brand')
          ->set('module', $brand_module)
          ->save();

        // Get langcodes for all languages.
        $langcodes = array_keys(\Drupal::languageManager()->getLanguages());

        // Get all config names available in current module.
        $names = ConfigHelper::forModule($brand_module)->optional()->listAll();

        // Update config translations from string translations.
        Locale::config()->updateConfigTranslations($names, $langcodes);

        // Get all config names available in current profile.
        $names = ConfigHelper::forModule(\Drupal::installProfile())->optional()->listAll();

        // Update config translations from string translations.
        Locale::config()->updateConfigTranslations($names, $langcodes);
      }
      else {
        drush_print(dt("Brand module @brand_module can't be enabled because the site is already configured with @existing_brand_module.",
          [
            '@brand_module' => $brand_module,
            '@existing_brand_module' => \Drupal::configFactory()->get('alshaya.installed_brand')->get('module'),
          ]));
      }
    }
    else {
      drush_print(dt('Brand module @brand_module does not exists.', ['@brand_module' => $brand_module]));
    }
  }

  if ($post_install_status == 'not done') {
    drush_print(dt('Executing post install code, please wait...'));

    // Invoke a hook to allow all modules to run code once post install.
    \Drupal::moduleHandler()->invokeAll('alshaya_master_post_drupal_install');

    // Set the state variable to done to avoid redoing it.
    \Drupal::state()->set('alshaya_master_post_drupal_install', 'done');

    drush_print(dt('Done.'));
  }
  else {
    drush_print(dt('Post install code executed already, not doing again.'));
  }
}

/**
 * Implements drush command alshaya-scrub-users.
 *
 * Removes all users that don't only have authenticated user role.
 */
function drush_alshaya_master_alshaya_scrub_users() {
  $ids = \Drupal::entityQuery('user')
    ->execute();
  $ids_to_delete = [];

  foreach ($ids as $id) {
    $user = User::load($id);
    $roles = $user->getRoles();

    $num_roles = count($roles);

    // Only if a user has just a single role of authenticated user,
    // we will delete him.
    if (($num_roles == 1) && ($roles[0] == 'authenticated')) {
      $ids_to_delete[] = $id;
    }
  }

  if ($ids_to_delete) {
    drush_print(dt('Following user ids are about to be deleted: @ids', ['@ids' => implode(', ', $ids_to_delete)]));

    if (drush_confirm(dt('Are you sure you want to delete @num non-privileged users?', ['@num' => count($ids_to_delete)]))) {
      foreach ($ids_to_delete as $id_to_delete) {
        drush_print(dt('Deleting user: @id', ['@id' => $id_to_delete]));
        user_delete($id_to_delete);
      }

      drush_print(dt('Done.'));
    }
    else {
      drush_user_abort();
    }
  }
  else {
    drush_print(dt('No matching users found to be deleted.'));
  }
}
