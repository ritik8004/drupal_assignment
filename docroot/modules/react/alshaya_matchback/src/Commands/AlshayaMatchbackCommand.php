<?php

namespace Drupal\alshaya_matchback\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Command for enabling matchabck add to bag.
 */
class AlshayaMatchbackCommand extends DrushCommands {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   Logger channel.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    ModuleHandlerInterface $module_handler,
    LoggerChannelFactoryInterface $logger_channel_factory
  ) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->drupalLogger = $logger_channel_factory->get('alshaya_matchback');
  }

  /**
   * Enables matchback feature.
   *
   * @param array $options
   *   Options supported with drush command.
   *
   * @command alshaya_matchback:enable-matchback-add-to-bag
   *
   * @option show_wishlist Whether to show wishlist button on matchback items.
   * @option change_matchback_color Whether to change matchback item color when PDP product color changes.
   * @option show_view_options Whether to show view options button for matchback items.
   * @option use_matchback_cart_notification Whether to use matchback cart notification or default one
   *
   * @aliases matbe, matchback-add-to-bag-enable
   *
   * @usage drush matchback-add-to-bag-enable --show_wishlist=true --change_matchback_color=false --show_view_options=true --use_matchback_cart_notification=false
   *   Enable matchback add to bag with above options.
   * @usage drush matbe
   *   Enable matchback add to bag with default options.
   */
  public function enableMatchbackAddToBag(
    array $options = [
      'show_wishlist' => 0,
      'change_matchback_color' => 0,
      'show_view_options' => 1,
      'use_matchback_cart_notification' => 0,
    ]
  ) {
    // Numbers are passed as default arguments for the options since on passing
    // FALSE, we get error on running the command that the argument is not
    // accepted.
    // So now we convert it from number to boolean before saving it to config.
    $options = array_map(fn($option) => (bool) $option, $options);
    $this->configFactory->getEditable('alshaya_acm.settings')
      ->set('display_crosssell', TRUE)
      ->set('show_crosssell_as_matchback', TRUE)
      ->save();

    if ($this->moduleHandler->moduleExists('alshaya_wishlist')) {
      $this->configFactory->getEditable('alshaya_wishlist.settings')
        ->set('show_wishlist_on_matchback', $options['show_wishlist'] === TRUE)
        ->save();
    }

    $this->configFactory->getEditable('alshaya_acm_product.display_settings')
      ->set('change_matchback_color', $options['change_matchback_color'] === TRUE)
      ->set('use_matchback_cart_notification', (bool) $options['use_matchback_cart_notification'])
      ->set('display_mobile_matchback_add_to_bag_button', (bool) $options['show_view_options'])
      ->save();

    $this->drupalLogger->notice(dt('Enabled matchback add to bag feature.'));
  }

  /**
   * Disables matchback feature.
   *
   * @command alshaya_matchback:disable-matchback-add-to-bag
   *
   * @aliases matbd, matchback-add-to-bag-disable
   *
   * @usage drush matchback-add-to-bag-disable
   *   Disable matchback add to bag.
   * @usage drush matbd
   *   Disable matchback add to bag.
   */
  public function disableMatchbackAddToBag() {
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

    $this->drupalLogger->notice(dt('Disabled matchback add to bag feature.'));
  }

}
