<?php

namespace Drupal\alshaya_online_returns\Commands;

use Drupal\alshaya_online_returns\Helper\OnlineReturnsApiHelper;
use Drush\Commands\DrushCommands;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Provides commands to sync Online Returns related config from API.
 *
 * @package Drupal\alshaya_online_returns\Commands
 */
class OnlineReturnCommands extends DrushCommands {

  /**
   * The api helper object.
   *
   * @var Drupal\alshaya_online_returns\Helper\OnlineReturnsApiHelper
   */
  protected $apiHelper;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Logger Channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

  /**
   * OnlineReturnCommands constructor.
   *
   * @param Drupal\alshaya_online_returns\Helper\OnlineReturnsApiHelper $api_helper
   *   Api helper object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $drupal_logger
   *   Looger Factory.
   */
  public function __construct(
    OnlineReturnsApiHelper $api_helper,
    LanguageManagerInterface $language_manager,
    LoggerChannelFactoryInterface $drupal_logger
  ) {
    $this->apiHelper = $api_helper;
    $this->languageManager = $language_manager;
    $this->drupalLogger = $drupal_logger->get('alshaya_online_returns');
  }

  /**
   * Syncs online returns api config cache.
   *
   * @command alshaya_online_returns:sync-returns-api-config
   *
   * @aliases sync-returns-api-config
   * @usage drush sync-returns-api-config
   */
  public function syncReturnsConfig() {
    foreach (array_keys($this->languageManager->getLanguages()) as $langcode) {
      $this->drupalLogger->notice('Online Returns config sync started for language @langcode.', [
        '@langcode' => $langcode,
      ]);

      $this->apiHelper->getReturnsApiConfig($langcode, TRUE);
    }

    $this->drupalLogger->notice('Online Returns API config synced.');
  }

}
