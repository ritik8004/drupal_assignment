<?php

/**
 * @file
 * Provides Drush commands for alshaya_master.
 */

use Drupal\lightning_core\ConfigHelper;
use Drupal\locale\Locale;

/**
 * Implements hook_drush_command().
 */
function alshaya_master_drush_command() {
  $commands = [];

  $commands['alshaya-post-drupal-install'] = [
    'description' => 'Code to be executed only once post install.',
    'aliases' => ['apdi'],
    'options' => [
      'brand' => 'The brand to install.',
    ],
  ];

  return $commands;
}

/**
 * Implements drush command alshaya-post-drupal-install.
 */
function drush_alshaya_master_alshaya_post_drupal_install() {
  $post_install_status = \Drupal::state()->get('alshaya_master_post_drupal_install', 'not done');

  // Determine which brand to install.
  $brand = drush_get_option('brand', '');
  if (!empty($brand)) {
    if (empty(\Drupal::configFactory()->get('alshaya.installed_brand')->get('name'))) {
      switch ($brand) {
        case 'victoria_secret':
          $module = 'alshaya_vs';
          break;

        default:
          $brand = 'mothercare';
          $module = 'alshaya_mc';
      }

      drush_print(dt('Configuring the site for @brand brand.', ['@brand' => $brand]));

      // Install the module.
      \Drupal::service('module_installer')->install([$module]);

      // Update config with installed brand and module names.
      \Drupal::configFactory()->getEditable('alshaya.installed_brand')
        ->set('name', $brand)
        ->set('module', $module)
        ->save();

      // Get langcodes for all languages.
      $langcodes = array_keys(\Drupal::languageManager()->getLanguages());

      // Get all config names avaiable in current module.
      $names = ConfigHelper::forModule($module)->optional()->listAll();

      // Update config translations from string translations.
      Locale::config()->updateConfigTranslations($names, $langcodes);
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
