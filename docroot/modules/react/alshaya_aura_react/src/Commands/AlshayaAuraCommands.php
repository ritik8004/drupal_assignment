<?php

namespace Drupal\alshaya_aura_react\Commands;

use Drupal\alshaya_aura_react\Helper\AuraApiHelper;
use Drush\Commands\DrushCommands;

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
   * AlshayaAuraCommands constructor.
   *
   * @param Drupal\alshaya_aura_react\Helper\AuraApiHelper $api_helper
   *   Api helper object.
   */
  public function __construct(
    AuraApiHelper $api_helper
  ) {
    $this->apiHelper = $api_helper;
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
   */
  public function syncAuraConfig(array $options = [
    'configs' => '',
    'reset' => FALSE,
  ]) {
    $configs = !empty($options['configs'])
      ? explode(',', $options['configs'])
      : [];
    $this->apiHelper->getAuraApiConfig($configs, $options['reset']);

    $this->logger->notice('Aura API config synced. Configs: @configs.', [
      '@configs' => json_encode($options['configs']),
    ]);
  }

}
