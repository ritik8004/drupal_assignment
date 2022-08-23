<?php

namespace Drupal\alshaya_shopby_filter_attribute\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class Alshaya Shop By Filter Attribute Commands.
 *
 * @package Drupal\alshaya_shopby_filter_attribute\Commands
 */
class AlshayaShopByFilterAttributeCommands extends DrushCommands {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

  /**
   * AlshayaShopByFilterAttributeCommands constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->configFactory = $configFactory;
    $this->drupalLogger = $logger_factory->get('alshaya_shopby_filter_attribute');
  }

  /**
   * Enable or disable shop by filter attribute feature.
   *
   * @param array $options
   *   List of options supported by drush command.
   *
   * @throws \Drush\Exceptions\UserAbortException
   *
   * @command alshaya_shopby_filter_attribute:switch
   *
   * @option attributeName
   *  Pass the attribute name for which you want to show the shop by links.
   *
   * @aliases alshaya-shopby-filter-attribute
   *
   * @usage drush alshaya-shopby-filter-attribute --attributeName='size_shoe_eu'
   *   Enable shop by size_shoe_eu filter attribute.
   */
  public function enableDisableShopByFilterAttribute(
    array $options = [
      'attributeName' => NULL,
    ]
  ) {
    // Get the alshaya_shopby_filter_attribute configs.
    $configShopByFilter = $this->configFactory->getEditable('alshaya_shopby_filter_attribute.settings');

    // We will check if the feature is already enabled we will disable it and
    // vice versa.
    $status = (bool) !$configShopByFilter->get('enabled');
    $action = $status ? 'enable' : 'disable';

    // Confirm if the user wants to enable/disable the feature or not. If not,
    // abort the operation throwing a UserAbortException.
    $askToConfirm = dt('Are you sure you want to !action shop by filter attribute feature?', ['!action' => $action]);
    if (!$this->io()->confirm($askToConfirm)) {
      throw new UserAbortException();
    }

    // Get the attribute name supplied with the command and it's found empty, we
    // stop the further operation.
    $attributeName = $options['attributeName'];
    if (empty($attributeName)) {
      $this->drupalLogger->warning('Please provide filter attribute name with argument --attributeName.');
      return;
    }

    // Add informative message to drush command about which all settings we will
    // update during the command run.
    $configs_update_message = "For enable/disable this feature we will update the following settings.\n- alshaya_main_menu.settings\n-- show_l2_in_separate_column\n-- show_highlight\n-- show_menu_full_width\nand\n- alshaya_shopby_filter_attribute.settings\n-- enabled\n-- attributes\nWe can individually update the settings as well to on/off specific feature.\n\n";
    $this->drupalLogger->notice($configs_update_message);

    // For enable this feature we will update the following settings with the
    // relevant values as mentioned.
    // - alshaya_main_menu.settings
    // -- show_l2_in_separate_column: TRUE (Show each L2 in separate column)
    // -- show_highlight: FALSE (Hide highlights CTA in menu)
    // -- show_menu_full_width: TRUE (Show menu in full width)
    // and
    // - alshaya_shopby_filter_attribute.settings
    // -- enabled: TRUE (Enable the feature)
    // -- attributes: {--attributeName} (Like 'size_shoe_eu' etc).
    if ($status) {
      $this->configFactory->getEditable('alshaya_main_menu.settings')
        ->set('show_l2_in_separate_column', TRUE)
        ->set('show_highlight', FALSE)
        ->set('show_menu_full_width', TRUE)
        ->save();

      $configShopByFilter->set('enabled', $status);
      $configShopByFilter->set('attributes', $attributeName);
      $configShopByFilter->save();
    }
    else {
      // For disable this feature we will update the following settings with the
      // relevant values as mentioned.
      // - alshaya_main_menu.settings
      // -- show_l2_in_separate_column: FALSE (Show L2 as per the column algo)
      // -- show_highlight: TRUE (Show highlights CTA in menu)
      // -- show_menu_full_width: FALSE (Show menu in container width)
      // and
      // - alshaya_shopby_filter_attribute.settings
      // -- enabled: FALSE (Disable the feature)
      // -- attributes: '' (Set this to empty).
      $this->configFactory->getEditable('alshaya_main_menu.settings')
        ->set('show_l2_in_separate_column', FALSE)
        ->set('show_highlight', TRUE)
        ->set('show_menu_full_width', FALSE)
        ->save();

      $configShopByFilter->set('enabled', $status);
      $configShopByFilter->set('attributes', '');
      $configShopByFilter->save();
    }

    // Inform that the feature is successfully enabled.
    $this->output->writeln(dt('Successfully !action shop by filter attribute feature.', ['!action' => $action . 'd']));
  }

}
