<?php

namespace Drupal\alshaya_aura_react\Commands;

use Drupal\alshaya_aura_react\Helper\AuraApiHelper;
use Drush\Commands\DrushCommands;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Provides commands to sync Aura related config from API.
 *
 * @package Drupal\alshaya_aura_react\Commands
 */
class AlshayaAuraCommands extends DrushCommands {

  /**
   * The api helper object.
   *
   * @var Drupal\alshaya_aura_react\Helper\AuraApiHelper
   */
  protected $apiHelper;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * AlshayaAuraCommands constructor.
   *
   * @param Drupal\alshaya_aura_react\Helper\AuraApiHelper $api_helper
   *   Api helper object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(
    AuraApiHelper $api_helper,
    LanguageManagerInterface $language_manager
  ) {
    $this->apiHelper = $api_helper;
    $this->languageManager = $language_manager;
  }

  /**
   * Syncs aura api config cache.
   *
   * @param array $options
   *   Command options.
   *
   * @command alshaya_aura_react:sync-aura-api-config
   *
   * @aliases sync-aura-api-config
   * @usage drush sync-aura-api-config
   * @usage drush sync-aura-api-config --configs="APC_CASHBACK_ACCRUAL_RATIO,EXT_PHONE_PREFIX"
   * @usage drush sync-aura-api-config --configs="APC_CASHBACK_REDEMPTION_RATIO" --reset
   * @usage drush sync-aura-api-config --configs="APC_CASHBACK_REDEMPTION_RATIO" --langcode='en,ar' --reset
   */
  public function syncAuraConfig(array $options = [
    'configs' => '',
    'reset' => FALSE,
    'langcode' => '',
  ]) {
    $configs = !empty($options['configs'])
      ? explode(',', $options['configs'])
      : [];
    $langcode_list = !empty($options['langcode'])
      ? explode(',', $options['langcode'])
      : array_keys($this->languageManager->getLanguages());

    foreach ($langcode_list as $langcode) {
      $this->apiHelper->getAuraApiConfig($configs, $options['reset'], $langcode);
    }

    $this->logger->notice('Aura API config synced. Configs: @configs.', [
      '@configs' => json_encode($options['configs']),
    ]);
  }

}
