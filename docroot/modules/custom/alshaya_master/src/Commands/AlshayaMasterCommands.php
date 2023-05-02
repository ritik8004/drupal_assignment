<?php

namespace Drupal\alshaya_master\Commands;

use Drupal\Core\Config\CachedStorage;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Site\Settings;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Update\UpdateHookRegistry;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\lightning_core\ConfigHelper;
use Drupal\locale\LocaleConfigManager;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;
use Symfony\Component\Console\Input\InputInterface;
use Consolidation\AnnotatedCommand\AnnotationData;
use Consolidation\SiteAlias\SiteAliasManagerAwareInterface;
use Consolidation\SiteAlias\SiteAliasManagerAwareTrait;
use Drupal\Component\Serialization\Yaml;

/**
 * Alshaya Master Commands class.
 */
class AlshayaMasterCommands extends DrushCommands implements SiteAliasManagerAwareInterface {

  use SiteAliasManagerAwareTrait;

  /**
   * State Manager.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  private $state;

  /**
   * Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * Module Installer service.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  private $moduleInstaller;

  /**
   * Module Handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * Cached Storage service.
   *
   * @var \Drupal\Core\Config\CachedStorage
   */
  private $cachedStorage;

  /**
   * Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Module Extension List Manager.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  private $moduleExtensionList;

  /**
   * Date Formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  private $dateFormatter;

  /**
   * The install profile.
   *
   * @var string
   */
  protected $installProfile;

  /**
   * The locale configuration manager.
   *
   * @var \Drupal\locale\LocaleConfigManager
   */
  protected $localeConfigManager;

  /**
   * Drupal Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannel|\Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

  /**
   * Update Hook Registry.
   *
   * @var \Drupal\Core\Update\UpdateHookRegistry
   */
  protected $updateHookRegistery;

  /**
   * AlshayaMasterCommands constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   State Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config Factory service.
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $moduleInstaller
   *   Module Installer service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module Handler service.
   * @param \Drupal\Core\Config\CachedStorage $configStorage
   *   Cache Storage service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language Manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Extension\ModuleExtensionList $module_extension_list
   *   Module Extension List Manager.
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   Date Formatter.
   * @param string $install_profile
   *   The install profile.
   * @param \Drupal\locale\LocaleConfigManager $locale_config_manager
   *   The locale configuration manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   Logger Channel Factory.
   * @param \Drupal\Core\Update\UpdateHookRegistry $update_hook_registry
   *   Update Hook Registery.
   */
  public function __construct(StateInterface $state,
                              ConfigFactoryInterface $configFactory,
                              ModuleInstallerInterface $moduleInstaller,
                              ModuleHandlerInterface $moduleHandler,
                              CachedStorage $configStorage,
                              LanguageManagerInterface $languageManager,
                              EntityTypeManagerInterface $entityTypeManager,
                              ModuleExtensionList $module_extension_list,
                              DateFormatter $date_formatter,
                              $install_profile,
                              LocaleConfigManager $locale_config_manager,
                              LoggerChannelFactory $logger_factory,
                              UpdateHookRegistry $update_hook_registry) {
    $this->state = $state;
    $this->configFactory = $configFactory;
    $this->moduleInstaller = $moduleInstaller;
    $this->moduleHandler = $moduleHandler;
    $this->cachedStorage = $configStorage;
    $this->languageManager = $languageManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->moduleExtensionList = $module_extension_list;
    $this->dateFormatter = $date_formatter;
    $this->installProfile = $install_profile;
    $this->localeConfigManager = $locale_config_manager;
    $this->drupalLogger = $logger_factory->get('AlshayaMasterCommands');
    $this->updateHookRegistery = $update_hook_registry;
  }

  /**
   * Code to be executed only once post install.
   *
   * @command alshaya_master:post-drupal-install
   *
   * @option brand_module The brand module to install.
   * @option country_code The country code the module.
   *
   * @validate-module-enabled alshaya_master
   *
   * @aliases apdi,alshaya-post-drupal-install
   */
  public function postDrupalinstall($options = [
    'brand_module' => self::REQ,
    'country_code' => self::REQ,
  ]) {
    // Increase memory limit to make sure install finishes fine.
    ini_set('memory_limit', '2G');

    $post_install_status = $this->state->get('alshaya_master_post_drupal_install', 'not done');
    $modules = $this->moduleExtensionList->getList();

    // Determine which country module to install.
    $country_code = $options['country_code'];
    $country_code = empty($country_code) ? Settings::get('country_code') : $country_code;
    $country_module = 'alshaya_' . strtolower($country_code);

    if (isset($modules[$country_module])) {
      if (empty($this->configFactory->get('alshaya.installed_country')->get('module'))) {
        $this->output()->writeln(dt('Enabling the @country_module country module.', ['@country_module' => $country_module]));

        // Install the module.
        $this->moduleInstaller->install([$country_module]);

        // Update config with installed brand and module names.
        $this->configFactory->getEditable('alshaya.installed_country')
          ->set('module', $country_module)
          ->save();
      }
      else {
        $this->output()->writeln(dt("Country module @country_module can't be enabled because the site is already configured with @existing_country_module.",
          [
            '@country_module' => $country_module,
            '@existing_country_module' => $this->configFactory->get('alshaya.installed_country')->get('module'),
          ]));
      }
    }
    else {
      $this->output()->writeln(dt('Country module @country_module does not exists.', ['@country_module' => $country_module]));
    }

    // Determine which brand to install.
    $brand_module = $options['brand_module'];
    $sites = Yaml::decode(file_get_contents('../blt/alshaya_sites.yml'))['sites'];
    // @codingStandardsIgnoreLine
    global $_acsf_site_name;

    // Get the current installed profile.
    $profile = str_replace('alshaya_', '_', $this->cachedStorage->read('core.extension')['profile']);

    // Try to get the brand module from settings file if available.
    if (!empty($sites[$_acsf_site_name]) && !empty($sites[$_acsf_site_name]['module'])) {
      $brand_module = $sites[$_acsf_site_name]['module'];
    }
    // Try to look for transac and non transac specific module for brand before
    // brand installing brand module. i.e. alshaya_vs_transac.
    elseif (isset($modules[$brand_module . $profile])) {
      $brand_module = $brand_module . $profile;
    }

    if (!empty($brand_module)
      && isset($modules[$brand_module])) {
      if (empty($this->configFactory->get('alshaya.installed_brand')->get('module'))) {
        $this->output()->writeln(dt('Enabling the @brand_module brand module.', ['@brand_module' => $brand_module]));

        // Install the module.
        $this->moduleInstaller->install([$brand_module]);

        // Update config with installed brand and module names.
        $this->configFactory->getEditable('alshaya.installed_brand')
          ->set('module', $brand_module)
          ->save();

        // Get langcodes for all languages.
        $langcodes = array_keys($this->languageManager->getLanguages());

        // Get all config names available in current module.
        $names = ConfigHelper::forModule($brand_module)->optional()->listAll();

        // Update config translations from string translations.
        $this->localeConfigManager->updateConfigTranslations($names, $langcodes);

        // Get all config names available in current profile.
        $names = ConfigHelper::forModule($this->installProfile)->optional()->listAll();

        // Update config translations from string translations.
        $this->localeConfigManager->updateConfigTranslations($names, $langcodes);
      }
      else {
        $this->output()->writeln(dt("Brand module @brand_module can't be enabled because the site is already configured with @existing_brand_module.",
          [
            '@brand_module' => $brand_module,
            '@existing_brand_module' => $this->configFactory->get('alshaya.installed_brand')->get('module'),
          ]));
      }
    }
    else {
      $this->output()->writeln(dt('Brand module @brand_module does not exists.', ['@brand_module' => $brand_module]));
    }

    if ($post_install_status == 'not done') {
      $this->output()->writeln(dt('Executing post install code, please wait...'));

      // Invoke a hook to allow all modules to run code once post install.
      $this->moduleHandler->invokeAll('alshaya_master_post_drupal_install');

      // Set the state variable to done to avoid redoing it.
      $this->state->set('alshaya_master_post_drupal_install', 'done');

      $this->output()->writeln(dt('Done.'));
    }
    else {
      $this->output()->writeln(dt('Post install code executed already, not doing again.'));
    }
  }

  /**
   * Delete all users that have only autheticated user role.
   *
   * @throws \Drush\Exceptions\UserAbortException
   *
   * @command alshaya_master:scrub-users
   *
   * @aliases alshaya-scrub-users
   */
  public function scrubUsers() {
    $user_entity = $this->entityTypeManager->getStorage('user');
    $ids = $user_entity->getQuery()->execute();

    $ids_to_delete = [];

    foreach ($ids as $id) {
      $user = $this->entityTypeManager->getStorage('user')->load($id);
      $roles = $user->getRoles();

      $num_roles = is_countable($roles) ? count($roles) : 0;

      // Only if a user has just a single role of authenticated user,
      // we will delete them.
      if (($num_roles == 1) && ($roles[0] == 'authenticated')) {
        $ids_to_delete[] = $id;
      }
    }

    if ($ids_to_delete) {
      $this->output()->writeln(dt('Following user ids are about to be deleted: @ids', [
        '@ids' => implode(', ', $ids_to_delete),
      ]));

      if ($this->io()->confirm(dt('Are you sure you want to delete @num non-privileged users?', ['@num' => count($ids_to_delete)]))) {
        foreach ($ids_to_delete as $id_to_delete) {
          $this->output()->writeln(dt('Deleting user: @id', ['@id' => $id_to_delete]));
          $this->entityTypeManager->getStorage('user')->load($id_to_delete)->delete();
        }

        $this->output()->writeln(dt('Done.'));
      }
      else {
        throw new UserAbortException();
      }
    }
    else {
      $this->output()->writeln(dt('No matching users found to be deleted.'));
    }
  }

  /**
   * Alter the uri to use https.
   *
   * @hook pre-init *
   */
  public function alter(InputInterface $input, AnnotationData $annotationData) {
    $self = $this->siteAliasManager()->getSelf();
    $uri = $self->get('uri');
    $url = parse_url($uri);

    // If the uri does not have a scheme add https.
    if (!isset($url['scheme'])) {
      $self->set('uri', "https://$uri");
    }
    elseif ($url['scheme'] == 'http') {
      $uri = substr($uri, 4);
      $self->set('uri', "https$uri");
    }
  }

  /**
   * Ensure https is used for queues.
   *
   * @hook pre-init queue-run
   *
   * @throws Exception
   */
  public function preInitQueueRun(InputInterface $input, AnnotationData $annotationData) {
    // Do the checks and throw error if not https.
    $this->checkScheme();
  }

  /**
   * Ensure https is used for search-api-index.
   *
   * @hook pre-init search-api-index
   *
   * @throws Exception
   */
  public function preInitSearchApiIndex(InputInterface $input, AnnotationData $annotationData) {
    // Do the checks and throw error if not https.
    $this->checkScheme();
  }

  /**
   * Ensure https is used for create-products-feed.
   *
   * @hook pre-init create-products-feed
   *
   * @throws Exception
   */
  public function preInitCreateProductsFeed(InputInterface $input, AnnotationData $annotationData) {
    // Do the checks and throw error if not https.
    $this->checkScheme();
  }

  /**
   * Ensure https is used for simple-sitemap-generate.
   *
   * @hook pre-init simple-sitemap-generate
   *
   * @throws Exception
   */
  public function preInitSimpleSitemapGenerate(InputInterface $input, AnnotationData $annotationData) {
    // Do the checks and throw error if not https.
    $this->checkScheme();
  }

  /**
   * Enable maintenance mode and memorise it is done via script.
   *
   * @command alshaya_master:enable-maintenance
   *
   * @aliases alshaya-enable-maintenance
   */
  public function enableMaintenance() {
    if (!($this->state->get('system.maintenance_mode'))) {
      $this->state->set('alshaya.maintenance_mode', TRUE);
      $this->state->set('system.maintenance_mode', TRUE);

      $this->logger()->warning('Enabled maintenance mode via alshaya-enable-maintenance command');
    }
  }

  /**
   * Disable maintenance mode only if it was done via script.
   *
   * @command alshaya_master:disable-maintenance
   *
   * @aliases alshaya-disable-maintenance
   */
  public function disableMaintenance() {
    if ($this->state->get('alshaya.maintenance_mode')) {
      $this->state->set('alshaya.maintenance_mode', FALSE);
      $this->state->set('system.maintenance_mode', FALSE);

      $this->logger()->warning('Disabled maintenance mode via alshaya-disable-maintenance command');
    }
  }

  /**
   * Create QA accounts.
   *
   * @command alshaya_master:create-qa-accounts
   *
   * @aliases alshaya-create-qa-accounts
   */
  public function createQaAccounts() {
    if (alshaya_is_env_prod()) {
      return;
    }

    $date = $this->dateFormatter->format(
      time(),
      'custom',
      DateTimeItemInterface::DATETIME_STORAGE_FORMAT,
      DateTimeItemInterface::STORAGE_TIMEZONE
    );

    $qa_users = @file_get_contents(Settings::get('server_home_dir') . '/qa_accounts.txt');
    foreach (explode(PHP_EOL, $qa_users ?? '') as $qa_user) {
      if (empty($qa_user)) {
        continue;
      }

      $admin_user = user_load_by_mail($qa_user);
      if (!($admin_user instanceof UserInterface)) {
        // Create default administrator user.
        $admin_user = User::create();
        $admin_user->enforceIsNew();
        $admin_user->setUsername(explode('@', $qa_user)[0]);
        $admin_user->setEmail($qa_user);
        $admin_user->addRole('administrator');
      }

      // Set expiration to not-expired.
      $admin_user->set('field_last_password_reset', $date);
      $admin_user->set('field_password_expiration', '0');
      $admin_user->setPassword(user_password());
      $admin_user->activate();
      $admin_user->save();
    }
  }

  /**
   * Check if drush command should be with https scheme.
   */
  public function checkScheme() {
    $self = $this->siteAliasManager()->getSelf();
    $uri = $self->get('uri');
    $url = parse_url($uri);

    if (!isset($url['scheme']) || $url['scheme'] == 'http') {
      throw new \Exception('Please use https URI.');
    }
  }

  /**
   * Utility command to fix module update version.
   *
   * @command alshaya_master:fix-update-version
   *
   * @aliases fix-update-version
   */
  public function fixUpdateVersion() {
    foreach ($this->moduleExtensionList->getList() as $module) {
      $module_name = $module->getName();

      $version = (int) $this->updateHookRegistery->getInstalledVersion($module->getName());
      if (empty($version)) {
        continue;
      }
      $installed_version = $version;

      $this->moduleHandler->loadInclude($module_name, 'install');

      while (TRUE) {
        if (function_exists("{$module_name}_update_{$version}")) {
          if ($installed_version !== $version) {
            $this->updateHookRegistery->setInstalledVersion($module_name, $version);
            $this->drupalLogger->warning('Installed Update Version updated for module: @module, to version: @version_to, from version: @version_from.', [
              '@module' => $module_name,
              '@version_from' => $installed_version,
              '@version_to' => $version,
            ]);
          }
          break;
        }

        $version--;

        if ($version < 9000) {
          break;
        }
      }
    }
  }

}
