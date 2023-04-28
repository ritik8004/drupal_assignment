<?php

namespace Alshaya\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

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
   * Create default settings files.
   */
  public function createDefaultSettingsFiles() {
    // Default site directory.
    $default_multisite_dir = $this->getConfigValue('docroot') . "/sites/default";

    // Generate local.settings.php from file provided by blt.
    $default_local_settings_file = $default_multisite_dir . '/settings/default.local.settings.php';
    $local_settings_file = "$default_multisite_dir/settings/local.settings.php";

    // Generate local.drush.yml.
    $default_local_drush_file = "$default_multisite_dir/default.local.drush.yml";
    $local_drush_file = "$default_multisite_dir/local.drush.yml";

    // Array of from and destination file paths.
    $copy_map = [
      $default_local_settings_file => $local_settings_file,
      $default_local_drush_file => $local_drush_file,
    ];

    $taskFilesystemStack = $this->taskFilesystemStack();
    $taskFilesystemStack->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);

    // Copy files without overwriting.
    foreach ($copy_map as $from => $to) {
      if (!file_exists($to)) {
        $this->say("Generating file $from ==> $to");
        $taskFilesystemStack->copy($from, $to);
      }
    }

    $result = $taskFilesystemStack->run();

    if (!$result->wasSuccessful()) {
      $this->say($result->getMessage());
      return $result;
    }

    return $result;
  }

  /**
   * Allow editing settings.php file.
   *
   * @command local:reset-local-settings
   * @description Resets local settings file.
   */
  public function localResetLocalSettings() {
    $this->say('Resetting local.settings.php file');
    $multisites = $this->getConfigValue('multisite.name');
    $docroot = $this->getConfigValue('docroot');

    // Proceed if settings and drush.yml files exists/created in default site
    // directory.
    if ($this->createDefaultSettingsFiles()) {
      // Location of Default site directory.
      $default_multisite_dir = $this->getConfigValue('docroot') . "/sites/default";
      // Location of default.local.settings.php.
      $default_local_settings_file = "$default_multisite_dir/settings/default.local.settings.php";
      // Location of default.local.drush.yml.
      $default_local_drush_file = "$default_multisite_dir/default.local.drush.yml";

      foreach ($multisites as $multisite) {
        $multisite_dir = $docroot . '/sites/' . $multisite;
        $settings_file = $multisite_dir . '/settings.php';

        // Local settings file for multisite.
        $project_local_settings_file = "$multisite_dir/settings/local.settings.php";
        // Local drush.yml file for multisite.
        $project_local_drush_file = "$multisite_dir/local.drush.yml";

        // Array of from and destination file paths.
        $copy_map = [
          $default_local_settings_file => $project_local_settings_file,
          $default_local_drush_file => $project_local_drush_file,
        ];

        // Adding copy command to loop to generate files for each multisite.
        $taskFilesystemStack = $this->taskFilesystemStack();
        $taskFilesystemStack->stopOnFail()
          ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
          ->chmod($multisite_dir, 0755)
          ->chmod($settings_file, 0644);

        // Copy files without overwriting.
        foreach ($copy_map as $from => $to) {
          if (!file_exists($to)) {
            $this->say("Generating file $from ==> $to");
            $taskFilesystemStack->copy($from, $to);
          }
        }

        $taskFilesystemStack->run();
      }
    }

  }

  /**
   * Allow all modules to run code once post drupal install.
   *
   * @command local:post-install
   * @description Allow all modules to run code once post drupal install.
   */
  public function localPostInstall($uri, $brand, $country_code) {
    $this->say('Allow all modules to run code once post drupal install.');
    $drush_alias = $this->getConfigValue('drush.alias');
    $this->taskDrush()
      ->stopOnFail()
      ->drush('alshaya-post-drupal-install')
      ->alias($drush_alias)
      ->option('brand_module', $brand)
      ->option('country_code', $country_code)
      ->uri($uri)
      ->run();
  }

  /**
   * Sync the Categories, Product Options and Products.
   *
   * @command sync:products
   * @description Sync the Categories, Product Options and Products.
   */
  public function syncProducts($uri, $limit = 200) {
    $this->say('Sync the categories, product option and products');
    $drush_alias = $this->getConfigValue('drush.alias');
    $this->taskDrush()
      ->stopOnFail()
      ->alias($drush_alias)
      ->drush('sync-commerce-cats')
      ->drush('sync-options')
      ->drush('sync-commerce-products-test')
      ->arg($limit)
      ->uri($uri)
      ->run();
  }

  /**
   * Sync the Stores.
   *
   * @command sync:stores
   * @description Sync the Stores.
   */
  public function syncStores($uri) {
    $this->say('Sync the stores');
    $drush_alias = $this->getConfigValue('drush.alias');
    $this->taskDrush()
      ->stopOnFail()
      ->alias($drush_alias)
      ->drush('sync-stores')
      ->uri($uri)
      ->run();
  }

  /**
   * Sync Areas.
   *
   * @command sync:areas
   * @description Sync the areas.
   */
  public function syncAreas($uri) {
    $this->say('Sync the areas');
    $drush_alias = $this->getConfigValue('drush.alias');
    $this->taskDrush()
      ->stopOnFail()
      ->alias($drush_alias)
      ->drush('sync-areas')
      ->uri($uri)
      ->run();
  }

  /**
   * Sync the Promotions.
   *
   * @command sync:promotions
   * @description Sync the Promotions.
   */
  public function syncPromotions($uri) {
    $this->say('Sync the promotions');
    $drush_alias = $this->getConfigValue('drush.alias');
    $this->taskDrush()
      ->stopOnFail()
      ->alias($drush_alias)
      ->drush('sync-commerce-promotions')
      ->uri($uri)
      ->run();
  }

  /**
   * Setup local dev environment.
   *
   * @command refresh:local
   * @description Setup local dev environment.
   */
  public function refreshLocal($site = NULL) {
    $data = Yaml::parse(file_get_contents($this->getConfigValue('repo.root') . '/blt/alshaya_sites.yml'));
    $sites = $data['sites'];
    $list = implode(', ', array_keys($sites));
    if ($site == NULL) {
      $site = $this->ask("Enter site code to reinstall ($list):");
    }
    while (!array_key_exists($site, $sites)) {
      $this->yell('Invalid site code');
      $site = $this->ask("Enter site code to reinstall ($list):");
    }
    if (array_key_exists('country', $sites[$site])) {
      $country_code = $sites[$site]['country'];
    }
    else {
      $country_code = $this->ask("Enter country code for the site ($list):");
    }

    $uri = self::getSiteUri($site);
    $profile_name = $sites[$site]['type'];
    $brand = $sites[$site]['module'];

    $this->invokeCommand('local:drupal:install', [
      'uri' => $uri,
      'profile' => $profile_name,
    ]);

    $this->invokeCommand('local:post-install', [
      'uri' => $uri,
      'brand' => $brand,
      'country_code' => $country_code,
    ]);

    // Following commands are required only for transac.
    if ($profile_name == 'alshaya_transac') {
      $this->invokeCommand('sync:products', ['uri' => $uri]);
      $this->invokeCommand('sync:promotions', ['uri' => $uri]);
      $this->invokeCommand('sync:areas', ['uri' => $uri]);
      $this->invokeCommand('sync:stores', ['uri' => $uri]);
    }
  }

  /**
   * Reinstall local dev environment.
   *
   * @command refresh:local:drupal
   * @description Reinstall local dev environment.
   */
  public function refreshLocalDrupal($site = NULL) {
    $data = Yaml::parse(file_get_contents($this->getConfigValue('repo.root') . '/blt/alshaya_sites.yml'));
    $sites = $data['sites'];
    $list = implode(', ', array_keys($sites));
    if ($site == NULL) {
      $site = $this->ask("Enter site code to reinstall, ($list):");
    }
    while (!array_key_exists($site, $sites)) {
      $this->yell('Invalid site code');
      $site = $this->ask("Enter site code to reinstall, ($list):");
    }
    if (array_key_exists('country', $sites[$site])) {
      $country_code = $sites[$site]['country'];
    }
    else {
      $country_code = $this->ask("Enter country code for the site:, ($list):");
    }

    $uri = self::getSiteUri($site);
    $profile_name = $sites[$site]['type'];
    $brand = $sites[$site]['module'];

    $this->invokeCommand('setup:composer:install');
    $this->invokeCommand('frontend:build');

    $this->invokeCommand('local:drupal:install', [
      'uri' => $uri,
      'profile' => $profile_name,
    ]);

    $this->invokeCommand('local:post-install', [
      'uri' => $uri,
      'brand' => $brand,
      'country_code' => $country_code,
    ]);

    // Following commands are required only for transac.
    if ($profile_name == 'alshaya_transac') {
      $this->invokeCommand('sync:products', ['uri' => $uri]);
      $this->invokeCommand('sync:promotions', ['uri' => $uri]);
      $this->invokeCommand('sync:areas', ['uri' => $uri]);
      $this->invokeCommand('sync:stores', ['uri' => $uri]);
    }
  }

  /**
   * Installs Drupal and imports configuration.
   *
   * @command local:drupal:install
   *
   * @return \Robo\Result
   *   The `drush site-install` command result.
   */
  public function localDrupalInstall($uri, $profile) {
    // Drop existing DB before installing again, any failed install
    // may have left some tables.
    $drush_alias = $this->getConfigValue('drush.alias');
    $this->taskDrush()
      ->stopOnFail()
      ->alias($drush_alias)
      ->drush('sql-drop')
      ->verbose(TRUE)
      ->uri($uri)
      ->run();

    $this->invokeCommand('local:reset-local-settings');

    $app_root = $this->getConfigValue('repo.root');
    $sudo_prefix = '';

    if (getenv('LANDO')) {
      // Flush memcache.
      $this->_exec('echo "flush_all" | nc -q 2 memcache 11211');
    }
    elseif (getenv('IS_DDEV_PROJECT')) {
      // Flush memcache.
      $this->_exec('echo "flush_all" | nc -q 2 memcached 11211');
    }
    elseif (getenv('AH_SITE_ENVIRONMENT')) {
      $this->taskDrush()
        ->drush('cr')
        ->uri($uri)
        ->run();
    }
    else {
      $sudo_prefix = 'sudo ';
      // Restart memcache to avoid issues because of old configs.
      $this->_exec($sudo_prefix . 'service memcached restart');
    }

    /** @var \Acquia\Blt\Robo\Tasks\DrushTask $task */
    $task = $this->taskDrush()
      ->drush("site-install")
      ->arg($profile)
      ->rawArg("install_configure_form.update_status_module='array(FALSE,FALSE)'")
      ->rawArg("install_configure_form.enable_update_status_module=NULL")
      ->uri($uri)
      ->option('site-name', $this->getConfigValue('project.human_name'))
      ->option('site-mail', $this->getConfigValue('drupal.account.mail'))
      ->option('account-name', 'admin', '=')
      ->option('account-pass', 'admin', '=')
      ->option('account-mail', $this->getConfigValue('drupal.account.mail'))
      ->option('locale', $this->getConfigValue('drupal.locale'))
      ->printOutput(TRUE);

    $config_strategy = $this->getConfigValue('cm.strategy');

    if (!$config_strategy != 'none') {
      $task->option('config-dir', $this->getConfigValue("cm.core.dirs.sync.path"));
    }

    $result = $task->detectInteractive()->run();
    if ($result->wasSuccessful()) {
      $this->getConfig()->set('state.drupal.installed', TRUE);
    }

    $this->invokeCommand('local:reset-settings-file');

    // Avoid file not writable issues after Drupal install.
    $this->_exec($sudo_prefix . 'chmod -R 777 ' . $app_root . '/docroot/sites/g/files');
    $this->_exec($sudo_prefix . 'chmod -R 777 ' . $app_root . '/files-private');

    return $result;
  }

  /**
   * Executes YAML validator against paragraph files.
   *
   * Paragraph field config yml files should have 'translatable=false' and
   * 'skip_translation_check=true'. If these values not exist in yml, then
   * invalidate/inform user/developer to add them.
   *
   * @param string $file_list
   *   A list of files to scan, separated by \n.
   *
   * @command tests:yaml:lint:files:paragraph
   * @aliases tylfp
   */
  public function lintFileList($file_list) {
    $this->say("Linting Paragraph Field YAML files...");
    $files = explode("\n", $file_list);
    $paragraph_ymls = array_filter($files, function ($paragraph_yml) {
      // Only fot the field config ymls.
      if (strpos($paragraph_yml, 'field.field.')) {
        $yaml_parsed = Yaml::parse(file_get_contents($paragraph_yml));
        return ($yaml_parsed['field_type'] == 'entity_reference_revisions'
          && (empty($yaml_parsed['skip_translation_check']) || !empty($yaml_parsed['translatable'])));
      }
    });

    // If there are any field config ymls not have proper translation config.
    if (!empty($paragraph_ymls)) {
      $this->say('Paragraph field yml file ' . $paragraph_ymls[0] . ' should have translatable=false and skip_translation_check=true');
      // Exit with a status of 1.
      return 1;
    }
  }

  /**
   * Executes Rector suggestions for newer PHP features.
   *
   * This command will trigger Rector processing to check if any
   * new PHP features can be used in provided file list. This Rector
   * processing takes all the configuration provided in rector.php file
   * available in repository root. For more information please check:
   * https://github.com/rectorphp/rector.
   *
   * @param string $file_list
   *   A list of files to scan, separated by \n.
   *
   * @command tests:rector:validate
   *
   * @throws \Exception
   */
  public function sniffFileList($file_list) {
    $failed = FALSE;
    $this->say("Sniffing changed PHP files for upgraded feature usage...");
    $files = explode("\n", $file_list);
    // Filtering PHP files.
    $files = array_filter($files, fn($value) =>
      (
        // Only track required files inside docroot.
        // Validate modules, profiles, proxy and themes folder only.
        str_starts_with($value, 'docroot/modules')
        || str_starts_with($value, 'docroot/profiles')
        || str_starts_with($value, 'docroot/proxy')
        || str_starts_with($value, 'docroot/themes')
      ) && (
        // Only take php files for validation.
        str_ends_with($value, '.php')
        || str_ends_with($value, '.module')
        || str_ends_with($value, '.install')
        || str_ends_with($value, '.inc')
        || str_ends_with($value, '.theme')
      )
    );
    if ($files) {
      $output = $this->_exec('cd ' . $this->getConfigValue('repo.root') . '; vendor/bin/rector process ' . implode(' ', $files) . ' --dry-run');
      if ($output->getExitCode() !== 0) {
        $failed = TRUE;
      }
    }

    // Throw exception if any upgradable PHP feature found.
    if ($failed) {
      throw new \Exception("Please use new PHP features as suggested by Rector.\n CAUTION!!! Rector suggestions do not provide proper code formatting as of now. E.g. Indentation, Bracket positions, Comment formats etc. Please use your best judgement while adding them. You can also validate using PHPCS.");
    }
  }

  /**
   * Build react dist files.
   *
   * @command local:react:build
   * @aliases react-build
   */
  public function reactBuildDist() {
    $result = $this->taskExec('blt/scripts/react-build.sh ' . $this->getConfigValue('repo.root') . '/docroot')
      ->dir($this->getConfigValue('repo.root'))
      ->interactive($this->input()->isInteractive())
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    return $result;
  }

  /**
   * Build react files for use in local.
   *
   * @command local:react:build:dev
   * @aliases react-build-dev
   */
  public function reactBuildDev() {
    foreach ($this->getFoldersWithReact() as $dir) {
      // Print the directory path to for user.
      $this->say($dir);

      // Build the files.
      $this->taskExec('npm run build:dev')
        ->dir($dir)
        ->run();
    }
  }

  /**
   * Wrapper to get React folders which require compiling.
   *
   * @return array
   *   Folders containing webpack.config.js file for React.
   */
  private function getFoldersWithReact() {
    $folders = [];

    $finder = new Finder();

    // Find all the folders containing the file used to specify entry points.
    $finder->name('webpack.config.js');

    // We need to find inside custom code only.
    $files = $finder->in($this->getConfigValue('docroot') . '/modules/react');

    foreach ($files as $file) {
      $dir = str_replace('webpack.config.js', '', $file->getRealPath());

      // Ignore webpack.config.js found inside node_modules.
      if (strpos($dir, 'node_modules') > -1 || strpos($dir, 'alshaya_react_test') > -1) {
        continue;
      }

      $folders[] = $dir;
    }

    return $folders;
  }

  /**
   * Wrapper to get folders which require compiling.
   *
   * @return array
   *   Folders containing webpack.config.js file.
   */
  private function getFoldersWithJs() {
    $folders = [];

    $finder = new Finder();

    // Find all the folders containing the file used to specify entry points.
    $finder->name('webpack.config.js');

    // We need to find inside custom code only.
    $files = $finder->in($this->getConfigValue('repo.root') . '/docroot/modules/custom');

    foreach ($files as $file) {
      $dir = str_replace('webpack.config.js', '', $file->getRealPath());

      // Ignore webpack.config.js found inside node_modules.
      if (strpos($dir, 'node_modules') > -1) {
        continue;
      }

      $folders[] = $dir;
    }

    return $folders;
  }

  /**
   * Compile all the JS for development use.
   *
   * @command source:build:js-assets-dev
   * @aliases js:build:dev
   */
  public function assetsBuildDev() {
    foreach ($this->getFoldersWithJs() as $dir) {
      // Print the directory path to for user.
      $this->say($dir);

      // Build the files.
      $this->taskExec('npm run build:dev')
        ->dir($dir)
        ->run();
    }
  }

  /**
   * Compile all the JS for production use.
   *
   * @command source:build:js-assets
   * @aliases js:build
   */
  public function assetsBuild() {
    foreach ($this->getFoldersWithJs() as $dir) {
      // Print the directory path to for user.
      $this->say($dir);

      // Build the files.
      $this->taskExec('npm run build')
        ->dir($dir)
        ->run();
    }
  }

  /**
   * Install the npm packages.
   *
   * @command source:setup:js-assets
   * @aliases js:setup
   */
  public function assetsSetup() {
    $this->taskExec('npm install')
      ->dir($this->getConfigValue('repo.root') . '/docroot/modules/custom')
      ->run();
  }

  /**
   * Get the site url for local.
   *
   * @param string $site_code
   *   Site Code.
   *
   * @return string
   *   URL.
   */
  public static function getSiteUri($site_code) {
    if (getenv('LANDO') || getenv('IS_DDEV_PROJECT')) {
      return $site_code . '.alshaya.lndo.site';
    }

    if (getenv('AH_SITE_ENVIRONMENT') === 'ide') {
      // Store the site code in file.
      file_put_contents('/home/ide/project/site.txt', $site_code);

      if (getenv('SERVER_NAME')) {
        return getenv('SERVER_NAME');
      }

      // Cloud IDE doesn't support multiple sites.
      return getenv('ACQUIA_APPLICATION_UUID') . '.web.ahdev.cloud';
    }

    return 'local.alshaya-' . $site_code . '.com';
  }

}
