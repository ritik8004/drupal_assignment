<?php

namespace Drupal\alshaya_shopby_filter_attribute\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;

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
  protected $configFactory;

  /**
   * AlshayaShopByFilterAttributeCommands constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory
  ) {
    parent::__construct();
    $this->configFactory = $configFactory;
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
   * @option enable
   *  Pass the 'enable' to enable the feature.
   * @option disable
   *  Pass the 'disable' to disable the feature.
   * @option attributeName
   *  Pass the attribute name for which you want to show the shop by links.
   *
   * @aliases alshaya-shopby-filter-attribute
   *
   * @usage drush alshaya-shopby-filter-attribute --enable --attributeName='size_shoe_eu'
   *   Enable shop by size_shoe_eu filter attribute.
   * @usage drush alshaya-shopby-filter-attribute --disable
   *   Disable the shop by filter attribute feature.
   */
  public function enableDisableShopByFilterAttribute(
    array $options = [
      'enable' => NULL,
      'disable' => NULL,
      'attributeName' => NULL,
    ]
  ) {
    // Check if either enable or disable option is set and proceed further only.
    // If we don't find any option set with command, we will abort requesting an
    // operation flag. We do the same if both are provided together.
    if ((empty($options['enable']) && empty($options['disable']))
      || (!empty($options['enable']) && !empty($options['disable']))) {
      $this->output->writeln(dt('Please provide the action to be performed with argument either --enable or --disable.'));
      return;
    }

    // Identify the status to be set for the feature.
    $status = (bool) $options['enable'] ?? $options['disable'];

    // We will check if the feature is already enabled we will disable it and
    // vice versa.
    $action = $status ? 'enable' : 'disable';

    // Confirm if the user wants to enable/disable the feature or not. If not,
    // abort the operation throwing a UserAbortException.
    $askToConfirm = dt('Are you sure you want to !action shop by filter attribute feature?', ['!action' => $action]);
    if (!$this->io()->confirm($askToConfirm)) {
      throw new UserAbortException();
    }

    // Get the alshaya_shopby_filter_attribute configs.
    $configShopByFilter = $this->configFactory->getEditable('alshaya_shopby_filter_attribute.settings');

    // Get the current status of the feature.
    $current_status = (bool) $configShopByFilter->get('enabled');

    // If the current status is similar to what is requested, we will abort the
    // executation to repeat the same operation again.
    if ($status === $current_status) {
      $this->output->writeln(dt('Aborting the operation as feature is already !action.', ['!action' => $action]));
      return;
    }

    // Get the attribute name supplied with the command and it's found empty, we
    // stop the further operation. We need this validation only when enabling
    // this feature i.e. if $status is TRUE.
    $attributeName = $options['attributeName'];
    if ($status && empty($attributeName)) {
      $this->output->writeln(dt('Please provide filter attribute name with argument --attributeName.'));
      return;
    }

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
