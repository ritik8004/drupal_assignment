<?php

namespace Drupal\alshaya_acm_product\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Command for enabling matchabck add to bag.
 */
class MatchbackAddToBagCommands extends DrushCommands {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Logger Channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

  /**
   * Constructor for the class.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   Module installer.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   Logger channel.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    ModuleInstallerInterface $module_installer,
    ModuleHandlerInterface $module_handler,
    LoggerChannelFactoryInterface $logger_channel_factory
  ) {
    $this->configFactory = $config_factory;
    $this->moduleInstaller = $module_installer;
    $this->moduleHandler = $module_handler;
    $this->drupalLogger = $logger_channel_factory->get('alshaya_acm_product');
  }

  /**
   * Disables matchback feature.
   *
   * @param array $options
   *   Options supported with drush command.
   *
   * @command alshaya_acm_product:disable-matchback-add-to-bag
   *
   * @option show_wishlist Whether to show wishlist button on matchback items.
   * @option change_matchback_color Whether to change matchback item color when PDP product color changes.
   * @option show_view_options Whether to show view options button for matchback items.
   * @option use_matchback_cart_notification Whether to use matchback cart notification or default one
   *
   * @aliases mvoe, matchback-view-options-enable
   *
   * @usage drush matchback-view-options --show_wishlist=true --change_matchback_color=false --show_view_options=true --use_matchback_cart_notification=false
   *   Enable matchback add to bag with above options.
   * @usage drush mvoe
   *   Enable matchback add to bag with default options.
   */
  public function enableMatchbackAddToBag(
    array $options = [
      'show_wishlist' => 'false',
      'change_matchback_color' => 'false',
      'show_view_options' => 'true',
      'use_matchback_cart_notification' => 'false',
    ]
  ) {
    if (!$this->moduleHandler->moduleExists('alshaya_matchback')) {
      $this->moduleInstaller->install(['alshaya_matchback']);
      $this->drupalLogger->notice(dt('Installed alshaya_matchback_module.'));
    }

    $this->configFactory->getEditable('alshaya_acm.settings')
      ->set('display_crosssell', TRUE)
      ->set('show_crosssell_as_matchback', TRUE)
      ->save();

    if ($this->moduleHandler->moduleExists('alshaya_wishlist')) {
      $this->configFactory->getEditable('alshaya_wishlist.settings')
        ->set('show_wishlist_on_matchback', $options['show_wishlist'] === 'true')
        ->save();
    }

    $this->configFactory->getEditable('alshaya_acm_product.display_settings')
      ->set('change_matchback_color', $options['change_matchback_color'] === '')
      ->set('use_matchback_cart_notification', (bool) $options['use_matchback_cart_notification'])
      ->set('display_mobile_matchback_add_to_bag_button', (bool) $options['show_view_options'])
      ->save();
  }

  /**
   * Disables matchback feature.
   *
   * @command alshaya_acm_product:disable-matchback-add-to-bag
   *
   * @aliases mvod, matchback-view-options-disable
   *
   * @usage drush matchback-view-options-disable
   *   Disable matchback add to bag.
   * @usage drush mvod
   *   Disable matchback add to bag.
   */
  public function disableMatchbackAddToBag() {
    if (!$this->moduleHandler->moduleExists('alshaya_matchback')) {
      $this->moduleInstaller->uninstall(['alshaya_matchback']);
      $this->drupalLogger->notice(dt('Un-installed alshaya_matchback_module.'));
    }

    $this->configFactory->getEditable('alshaya_acm.settings')
      ->set('display_crosssell', FALSE)
      ->set('show_crosssell_as_matchback', FALSE)
      ->save();

    if ($this->moduleHandler->moduleExists('alshaya_wishlist')) {
      $this->configFactory->getEditable('alshaya_wishlist.settings')
        ->set('show_wishlist_on_matchback', TRUE)
        ->save();
    }

    $this->configFactory->getEditable('alshaya_acm_product.display_settings')
      ->set('change_matchback_color', TRUE)
      ->set('use_matchback_cart_notification', TRUE)
      ->set('display_mobile_matchback_add_to_bag_button', FALSE)
      ->save();
  }

}
