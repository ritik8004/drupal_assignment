<?php

namespace Drupal\alshaya_shopby_filter_attribute\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;

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
   * Cache Tags Invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * AlshayaShopByFilterAttributeCommands constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cacheTagsInvalidator
   *   Cache Tags invalidator.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    CacheTagsInvalidatorInterface $cacheTagsInvalidator
  ) {
    parent::__construct();
    $this->configFactory = $configFactory;
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
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
   * @option status
   *  Pass the 'enable' or 'disable' to switch the feature.
   * @option attributeName
   *  Pass the attribute name for which you want to show the shop by links.
   *
   * @aliases alshaya-shopby-filter-attribute
   *
   * @usage drush alshaya-shopby-filter-attribute --status=enable --attributeName='size_shoe_eu'
   *   Enable shop by size_shoe_eu filter attribute.
   * @usage drush alshaya-shopby-filter-attribute --status=disable
   *   Disable the shop by filter attribute feature.
   */
  public function enableDisableShopByFilterAttribute(
    array $options = [
      'status' => NULL,
      'attributeName' => NULL,
    ]
  ) {
    // Check if status option is set with either enable or disable else exit.
    // If we don't find any option set with command, we will abort requesting an
    // operation flag.
    $action = trim($options['status']);
    if (empty($action)
      || !in_array($action, ['enable', 'disable'])) {
      $this->io()->error(dt("Please provide the action to be performed with argument --status='enable' or --status='disable'"));
      return;
    }

    // Identify the status to be set for the feature.
    $status = ($action === 'enable');

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
      $this->io()->error(dt('Aborting the operation as feature is already !action.', ['!action' => $action]));
      return;
    }

    // Get the attribute name supplied with the command and it's found empty, we
    // stop the further operation. We need this validation only when enabling
    // this feature i.e. if $status is TRUE.
    $attributeName = $status ? $options['attributeName'] : '';
    if ($status && empty($attributeName)) {
      $this->io()->error(dt('Please provide filter attribute name with argument --attributeName.'));
      return;
    }

    // For enable/disable this feature we update the following settings -
    // - alshaya_main_menu.settings
    // -- show_l2_in_separate_column (TRUE in case of enabled)
    // -- show_highlight (FALSE in case of enabled)
    // -- show_menu_full_width (TRUE in case of enabled)
    // - alshaya_shopby_filter_attribute.settings
    // -- enabled (TRUE in case of enabled)
    // -- attributes (Empty in case of disabled)
    $this->configFactory->getEditable('alshaya_main_menu.settings')
      ->set('show_l2_in_separate_column', $status)
      ->set('show_highlight', !$status)
      ->set('show_menu_full_width', $status)
      ->save();

    $configShopByFilter->set('enabled', $status);
    $configShopByFilter->set('attributes', $attributeName);
    $configShopByFilter->save();

    // Invalidate cache for adding new menu item attribute.
    $this->cacheTagsInvalidator->invalidateTags(['taxonomy_term:acq_product_category']);

    // Inform that the feature is successfully enabled.
    $this->io()->success(dt('Successfully !action shop by filter attribute feature.', ['!action' => $action]));
  }

}
