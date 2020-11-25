<?php

namespace Drupal\alshaya_aura_react\Commands;

use Drupal\alshaya_aura_react\Helper\AuraApiHelper;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
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
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * AlshayaAuraCommands constructor.
   *
   * @param Drupal\alshaya_aura_react\Helper\AuraApiHelper $api_helper
   *   Api helper object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory.
   */
  public function __construct(
    AuraApiHelper $api_helper,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->apiHelper = $api_helper;
    $this->logger = $logger_factory->get('alshaya_aura_react');
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
   * @usage drush sync-aura-api-config --api_keys="APC_CASHBACK_ACCRUAL_RATIO,EXT_PHONE_PREFIX"
   * @usage drush sync-aura-api-config --api_keys="APC_CASHBACK_REDEMPTION_RATIO" --reset
   */
  public function syncAuraConfig(array $options = [
    'api_keys' => '',
    'reset' => FALSE,
  ]) {
    $this->apiHelper->getAuraApiConfig($options);

    $this->logger->notice('Aura API config synced. API Keys: @api_keys.', [
      '@api_keys' => $options['api_keys'],
    ]);
  }

}
