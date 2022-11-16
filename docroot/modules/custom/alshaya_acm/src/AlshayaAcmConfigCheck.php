<?php

namespace Drupal\alshaya_acm;

use Drupal\alshaya_config\AlshayaConfigManager;
use Drupal\alshaya_custom\AlshayaCountryManager;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\State\StateInterface;
use Drupal\google_tag\Entity\Container;

/**
 * Alshaya Acm Config Check.
 */
class AlshayaAcmConfigCheck {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The date time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $dateTime;

  /**
   * The state factory.
   *
   * @var \Drupal\Core\KeyValueStore\StateInterface
   */
  protected $state;

  /**
   * Alshaya country manager.
   *
   * @var \Drupal\alshaya_custom\AlshayaCountryManager
   */
  protected $alshayaCountryManager;

  /**
   * Config manager service.
   *
   * @var \Drupal\alshaya_config\AlshayaConfigManager
   */
  protected $configManager;

  /**
   * Module Extension List Manager.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  private $moduleExtensionList;

  /**
   * AlshayaAcmConfigCheck constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Component\Datetime\TimeInterface $date_time
   *   The date time service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state factory.
   * @param \Drupal\alshaya_custom\AlshayaCountryManager $alshaya_country_manager
   *   Alshaya country manager.
   * @param \Drupal\alshaya_config\AlshayaConfigManager $config_manager
   *   The config manager service.
   * @param \Drupal\Core\Extension\ModuleExtensionList $module_extension_list
   *   Module Extension List Manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              ModuleInstallerInterface $module_installer,
                              ModuleHandlerInterface $module_handler,
                              LanguageManagerInterface $language_manager,
                              TimeInterface $date_time,
                              StateInterface $state,
                              AlshayaCountryManager $alshaya_country_manager,
                              AlshayaConfigManager $config_manager,
                              ModuleExtensionList $module_extension_list) {
    $this->configFactory = $config_factory;
    $this->moduleInstaller = $module_installer;
    $this->moduleHandler = $module_handler;
    $this->languageManager = $language_manager;
    $this->dateTime = $date_time;
    $this->state = $state;
    $this->alshayaCountryManager = $alshaya_country_manager;
    $this->configManager = $config_manager;
    $this->moduleExtensionList = $module_extension_list;
  }

  /**
   * Helper function to check config and reset if required.
   *
   * @param bool $force
   *   Force reset.
   * @param string $config_reset
   *   Config that needs to reset.
   *
   * @return bool
   *   Command run fully or not.
   */
  public function checkConfig($force = FALSE, string $config_reset = '') {
    // Do this only after installation is done.
    if (empty($this->configFactory->get('alshaya.installed_brand')->get('module'))) {
      return FALSE;
    }

    // Get the current env.
    $env = Settings::get('env') ?: 'local';

    // We don't do anything on update envs like 01uatup.
    if (str_ends_with($env, 'up')) {
      return FALSE;
    }

    // If we want to force reset or config is empty, we don't check for
    // other conditions.
    // We don't do anything on prod.
    if (alshaya_is_env_prod() && (!$force || empty($config_reset))) {
      return FALSE;
    }

    $request_time = $this->dateTime->getRequestTime();

    // Check if reverting of settings is disabled.
    if (!$force && !empty(Settings::get('disable_config_reset'))) {
      return FALSE;
    }

    $flag_var = 'alshaya_acm_config_check.' . $env;

    // We store reset time in state, check if variable is set for our ENV.
    $reset_time = $this->state->get($flag_var);

    if (!$force && !empty($reset_time)) {
      return FALSE;
    }

    // Set the first request time in state.
    $this->state->set($flag_var, $request_time);

    $reset = [
      'acq_commerce.conductor',
      'alshaya_api.settings',
      'recaptcha.settings',
      'geolocation_google_maps.settings',
      'exponea.settings',
      's3fs.settings',
    ];

    $this->moduleHandler->alter('alshaya_reset_config_configs_to_reset', $reset);

    if (!empty($config_reset)) {
      if (!in_array($config_reset, $reset)) {
        return FALSE;
      }
      else {
        $reset = [$config_reset];
      }
    }

    // Reset the settings.
    foreach ($reset as $config_key) {
      $config = $this->configFactory->getEditable($config_key);
      $settings = Settings::get($config_key);

      foreach ($settings ?? [] as $key => $value) {
        if (is_array($value)) {
          $existing = $config->get($key) ?? [];
          $value = array_replace_recursive($existing, $value);
        }

        $config->set($key, $value);
      }

      $config->save();
    }

    // Reset keys for kaleyra.settings.
    $kaleyra_keys = [
      'api_key',
      'whatsapp_api_key',
      'whatsapp_sid',
    ];
    $kaleyra_config = $this->configFactory->getEditable('kaleyra.settings');
    // Checks if config exists
    // Checks if data in config is not an empty array.
    if ($kaleyra_config && $kaleyra_config->get()) {
      foreach ($kaleyra_keys as $kaleyra_key) {
        $kaleyra_config->set($kaleyra_key, '');
      }
      $kaleyra_config->save();
    }

    // Re-configure Simple Oauth.
    $config = $this->configFactory->getEditable('simple_oauth.settings');
    $config->set('public_key', Settings::get('alshaya_acm_soauth_public_key'));
    $config->set('private_key', Settings::get('alshaya_acm_soauth_private_key'));
    $config->save();

    // Reset :to e-mail for contact us page.
    $config = $this->configFactory->getEditable('webform.webform.alshaya_contact');
    $config->set('handlers.email.settings.to_mail', 'no-reply@acquia.com');
    $config->save();

    // Save config again to ensure overrides are taken into consideration.
    alshaya_config_install_configs(['search_api.server.acquia_search_server'], 'alshaya_search', 'optional');

    // We can code here to support more or different languages later when
    // we encounter those scenarios, keeping it simple and static for now.
    // Reset store id - EN.
    $this->configFactory->getEditable('acq_commerce.store')
      ->set('store_id', Settings::get('store_id')['en'])
      ->save();

    // Reset store id - AR.
    $this->languageManager->getLanguageConfigOverride('ar', 'acq_commerce.store')
      ->set('store_id', Settings::get('store_id')['ar'])
      ->save();

    // Reset magento_lang_prefix - EN.
    $this->configFactory->getEditable('alshaya_api.settings')
      ->set('magento_lang_prefix.en', Settings::get('magento_lang_prefix')['en'])
      ->save();

    // Reset magento_lang_prefix - AR.
    $this->configFactory->getEditable('alshaya_api.settings')
      ->set('magento_lang_prefix.ar', Settings::get('magento_lang_prefix')['ar'])
      ->save();

    // Reset log mode when resetting config.
    if (function_exists('alshaya_performance_reset_log_mode')) {
      alshaya_performance_reset_log_mode();
    }

    // Configure GTM Container ID.
    $this->configureGtmContainerId(Settings::get('google_tag.container.primary'));

    return TRUE;
  }

  /**
   * Function to import gtm container id.
   *
   * @param array $gtmContainerId
   *   Settings array with gtm container id.
   */
  private function configureGtmContainerId(array $gtmContainerId) {
    $data = $this->configFactory->getEditable('google_tag.settings')->get();
    $container_data = [
      'status' => TRUE,
      'id' => 'primary',
      'label' => 'primary',
      'weight' => 0,
      'container_id' => $gtmContainerId['container_id'],
    ] + $data;
    $container_config = $this->configFactory->getEditable('google_tag.container.primary');
    if (empty($container_config->get())) {
      // This case is for new site install.
      $entity = Container::create($container_data);
      $entity->save();
    }
    elseif (empty($container_config->get('id'))) {
      // This case is when sync from uat to local and uat has malformed entity.
      $container_config->setData($container_data);
      $container_config->save();
    }
    else {
      // This case is when sync from uat to local with proper entity.
      /** @var \Drupal\google_tag\Entity\Container $entity */
      $entity = Container::load('primary');
      $entity->set('container_id', $gtmContainerId['container_id']);
      $entity->save();
    }
  }

  /**
   * Function to reset country specific settings.
   */
  public function resetCountrySpecificSettings() {
    $this->moduleHandler->loadInclude('alshaya', 'inc', 'utilities/alshaya.utilities.countries');

    // Get the expected country code cloned for.
    $expected_country_code = strtolower(Settings::get('country_code'));

    // If the target country code does not have related country module, that
    // means we are not using a valid country code which may be on purpose so
    // don't do anything.
    $modules = $this->moduleExtensionList->getList();
    if (!isset($modules['alshaya_' . $expected_country_code])) {
      return;
    }

    // Get the actual country code cloned from.
    $actual_country_code = strtolower(_alshaya_custom_get_site_level_country_code());

    // Enable the expected country specific module only.
    if ($expected_country_code != $actual_country_code) {
      if ($this->moduleHandler->moduleExists('alshaya_' . $actual_country_code)) {
        $this->moduleInstaller->uninstall(['alshaya_' . $actual_country_code]);
      }
      if (!$this->moduleHandler->moduleExists('alshaya_' . $expected_country_code)) {
        $this->moduleInstaller->install(['alshaya_' . $expected_country_code]);

        // Update config with installed brand and module names.
        $this->configFactory->getEditable('alshaya.installed_country')
          ->set('module', 'alshaya_' . $expected_country_code)
          ->save();
      }
    }

    // Reset currency code - EN.
    $this->configFactory->getEditable('acq_commerce.currency')
      ->set('currency_code', $this->alshayaCountryManager->getCurrencyCode($expected_country_code, 'en'))
      ->save();

    // Reset currency code - AR.
    $this->languageManager->getLanguageConfigOverride('ar', 'acq_commerce.currency')
      ->set('currency_code', $this->alshayaCountryManager->getCurrencyCode($expected_country_code, 'ar'))
      ->save();
    // Update mapping config.
    $this->configManager->updateConfigs(['alshaya_i18n.locale_mappings'], 'alshaya_i18n');
  }

}
