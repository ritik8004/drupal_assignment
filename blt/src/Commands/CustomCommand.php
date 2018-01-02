<?php

namespace Acquia\Blt\Custom\Commands;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\RandomString;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "custom" namespace.
 */
class CustomCommand extends BltTasks {

  /**
   * Allow editing settings.php file.
   *
   * @command local:reset-settings-file
   * @description Allow editing settings.php file.
   */
  public function localResetSettingsFile() {
    $this->say('Allow editing settings.php file');

    $taskFilesystemStack = $this->taskFilesystemStack();
    $multisites = $this->getConfigValue('multisite.name');
    $docroot = $this->getConfigValue('docroot');
    foreach ($multisites as $multisite) {
      $multisite_dir = $docroot . '/sites/' . $multisite;
      $settings_file = $multisite_dir . '/settings.php';
      $taskFilesystemStack->chmod($multisite_dir, 0755);
      $taskFilesystemStack->chmod($settings_file, 0644);
    }
    $taskFilesystemStack->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);
    $taskFilesystemStack->run();

    foreach ($multisites as $multisite) {
      $settings_file = $docroot . '/sites/' . $multisite . '/settings.php';
      $this->say('Revert ' . $settings_file . ' file to avoid GIT issues.');
      $this->taskExecStack()
        ->dir($docroot)
        ->stopOnFail(FALSE)
        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);
      $this->_exec('git checkout ' . $settings_file);
    }
  }

  /**
   * Allow editing settings.php file.
   *
   * @command local:reset-local-settings
   * @description Resets local settings file.
   */
  public function localResetLocalSettings() {
    $this->say('Resetting local.settings.php file');

    $taskFilesystemStack = $this->taskFilesystemStack();
    $multisites = $this->getConfigValue('multisite.name');
    $docroot = $this->getConfigValue('docroot');
    foreach ($multisites as $multisite) {
      $multisite_dir = $docroot . '/sites/' . $multisite;
      $settings_file = $multisite_dir . '/settings.php';
      $taskFilesystemStack->chmod($multisite_dir, 0755);
      $taskFilesystemStack->chmod($settings_file, 0644);

      $multisite_settings_file = $multisite_dir . '/settings/local.settings.php';

      if (file_exists($multisite_settings_file)) {
        $taskFilesystemStack->chmod($multisite_settings_file, 0644);
      }

      $this->say('Generating ' . $multisite_settings_file);
      $taskFilesystemStack->copy($docroot . '/sites/default/settings/default.local.settings.php', $multisite_dir . '/settings/local.settings.php', TRUE);
    }
    $taskFilesystemStack->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

  }

  /**
   * Allow all modules to run code once post drupal install.
   *
   * @command local:post-install
   * @description Allow all modules to run code once post drupal install.
   */
  public function localPostInstall($args = [
    'uri' => 'https://local.alshaya.com',
    'brand' => 'mothercare',
  ]) {

    $arguments = array_intersect_key($args, array_flip(['uri', 'brand']));
    $this->say('Allow all modules to run code once post drupal install.');
    $dursh_alias = $this->getConfigValue('drush.alias');
    $task = $this->taskDrush()
      ->stopOnFail()
      ->assume(TRUE)
      ->drush('alshaya-post-drupal-install')
      ->alias($dursh_alias)
      ->options($arguments)
      ->run();

  }

  /**
   * Sync the Product and Categories.
   *
   * @command sync:products
   * @description Sync the Product and Categories.
   */
  public function syncProducts($args = [
    'uri' => 'https://local.alshaya.com',
    'limit' => 200,
  ]) {

    $arguments = array_intersect_key($args, array_flip(['uri', 'limit']));
    $this->say('Sync the categories, product option and products');
    $dursh_alias = $this->getConfigValue('drush.alias');
    $task = $this->taskDrush()
      ->stopOnFail()
      ->assume(TRUE)
      ->alias($dursh_alias)
      ->drush('alshaya-acm-offline-categories-sync')
      ->drush('sync-commerce-product-options')
      ->drush('alshaya-acm-offline-products-sync')
      ->options($arguments)
      ->run();
  }

  /**
   * Sync the Stores.
   *
   * @command sync:stores
   * @description Sync the Stores.
   */
  public function syncStores($args = ['uri' => 'https://local.alshaya.com']) {

    $arguments = array_intersect_key($args, array_flip(['uri']));
    $this->say('Sync the stores');
    $dursh_alias = $this->getConfigValue('drush.alias');
    $task = $this->taskDrush()
      ->stopOnFail()
      ->assume(TRUE)
      ->alias($dursh_alias)
      ->drush('alshaya-api-sync-stores')
      ->options($arguments)
      ->run();

  }

  /**
   * Sync the Promotions.
   *
   * @command sync:promotions
   * @description Sync the Promotions.
   */
  public function syncPromotions($args = ['uri' => 'https://local.alshaya.com']) {

    $arguments = array_intersect_key($args, array_flip(['uri']));
    $this->say('Sync the promotions');
    $dursh_alias = $this->getConfigValue('drush.alias');
    $task = $this->taskDrush()
      ->stopOnFail()
      ->assume(TRUE)
      ->alias($dursh_alias)
      ->drush('sync-commerce-promotions')
      ->options($arguments)
      ->run();
  }

  /**
   * Setup local dev environment.
   *
   * @command refresh:local
   * @description Setup local dev environment.
   */
  public function refreshLocal() {
    $this->invokeCommand('local:reset-local-settings');
    $this->invokeCommand('local:drupal:install', ['uri' => 'https://local.alshaya.com']);
    $this->invokeCommand('local:reset-settings-file');
    $this->invokeCommand('local:post-install', ['brand' => 'mothercare']);
    $this->invokeCommand('sync:products', ['uri' => 'https://local.alshaya.com']);
    $this->invokeCommand('sync:promotions', ['uri' => 'https://local.alshaya.com']);
    $this->invokeCommand('sync:stores', ['uri' => 'https://local.alshaya.com']);

  }

  /**
   * Setup local dev environment with non-transac profile.
   *
   * @command refresh:non-transac-local
   * @description Setup local dev environment with non-transac profile.
   */
  public function refreshNonTransLocal() {
    $this->invokeCommand('local:reset-local-settings');
    $this->invokeCommand('local:drupal:install', [
      'uri' => 'https://local.non-transac.com',
      'project.profile.name' => 'alshaya_non_transac',
    ]);
    $this->invokeCommand('local:reset-settings-file');
    $this->invokeCommand('local:post-install', ['brand' => 'victoria_secret']);
  }

  /**
   * Reinstall local dev environment.
   *
   * @command refresh:local:drupal
   * @description Reinstall local dev environment.
   */
  public function refreshLocalDrupal() {
    $this->invokeCommand('setup:composer:install');
    $this->invokeCommand('frontend:build');
    $this->invokeCommand('local:reset-local-settings');
    $this->invokeCommand('local:drupal:install', ['uri' => 'https://local.alshaya.com']);
    $this->invokeCommand('setup:toggle-modules');
    $this->invokeCommand('local:reset-settings-file');
    $this->invokeCommand('local:post-install', ['brand' => 'mothercare']);
    $this->invokeCommand('sync:products', ['uri' => 'https://local.alshaya.com']);
    $this->invokeCommand('sync:promotions', ['uri' => 'https://local.alshaya.com']);
    $this->invokeCommand('sync:stores', ['uri' => 'https://local.alshaya.com']);
  }

  /**
   * Reinstall local dev environment with non-transac profile.
   *
   * @command refresh:non-transac-local:drupal
   * @description Reinstall local dev environment with non-transac profile.
   */
  public function refreshNonTransLocalDrupal() {
    $this->invokeCommand('setup:composer:install');
    $this->invokeCommand('frontend:build');
    $this->invokeCommand('local:reset-local-settings');
    $this->invokeCommand('local:drupal:install', [
      'uri' => 'https://local.non-transac.com',
      'project.profile.name' => 'alshaya_non_transac',
    ]);
    $this->invokeCommand('setup:toggle-modules');
    $this->invokeCommand('local:reset-settings-file');
    $this->invokeCommand('local:post-install', ['brand' => 'victoria_secret']);
  }

  /**
   * Installs Drupal and imports configuration.
   *
   * @command local:drupal:install
   *
   * @validateMySqlAvailable
   *
   * @return \Robo\Result
   *   The `drush site-install` command result.
   */
  public function localDrupalInstall($args = [
    'uri' => 'https://local.alshaya.com',
    'project.profile.name' => 'alshaya_transac',
  ]) {

    $arguments = array_intersect_key($args, array_flip(['uri']));
    // Generate a random, valid username.
    // @see \Drupal\user\Plugin\Validation\Constraint\UserNameConstraintValidator
    $username = RandomString::string(10, FALSE,
      function ($string) {
        return !preg_match('/[^\x{80}-\x{F7} a-z0-9@+_.\'-]/i', $string);
      },
      'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!#%^&*()_?/.,+=><'
    );

    /** @var \Acquia\Blt\Robo\Tasks\DrushTask $task */
    $task = $this->taskDrush()
      ->drush("site-install")
      ->arg($args['project.profile.name'])
      ->options($arguments)
      ->rawArg("install_configure_form.update_status_module='array(FALSE,FALSE)'")
      ->rawArg("install_configure_form.enable_update_status_module=NULL")
      ->option('site-name', $this->getConfigValue('project.human_name'))
      ->option('site-mail', $this->getConfigValue('drupal.account.mail'))
      ->option('account-name', $username, '=')
      ->option('account-mail', $this->getConfigValue('drupal.account.mail'))
      ->option('locale', $this->getConfigValue('drupal.locale'))
      ->assume(TRUE)
      ->printOutput(TRUE);

    $config_strategy = $this->getConfigValue('cm.strategy');

    if (!$config_strategy != 'none') {
      $cm_core_key = $this->getConfigValue('cm.core.key');
      $task->option('config-dir', $this->getConfigValue("cm.core.dirs.$cm_core_key.path"));
    }

    $result = $task->detectInteractive()->run();
    if ($result->wasSuccessful()) {
      $this->getConfig()->set('state.drupal.installed', TRUE);
    }

    return $result;
  }

}
