<?php

namespace Drupal\alshaya_rcs_super_category\Commands;

use Drush\Commands\DrushCommands;
use Drupal\alshaya_rcs_super_category\Service\RcsSuperCategoryHelper;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class Alshaya RCS Super Category Commands.
 *
 * @package Drupal\alshaya_rcs_super_category\Commands
 */
class RcsSuperCategoryCommands extends DrushCommands {

  /**
   * @var \Drupal\alshaya_rcs_super_category\Service\RcsSuperCategoryHelper
   *   Rcs Super Category Helper service.
   */
  protected $rcsSuperCategoryHelper;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private $drupalLogger;

  /**
   * RcsSuperCategoryCommands constructor.
   *
   * @param \Drupal\alshaya_rcs_super_category\Service\RcsSuperCategoryHelper $rcs_super_helper
   *   Rcs Super Category Helper service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Logger factory service.
   */
  public function __construct(RcsSuperCategoryHelper $rcs_super_helper, LoggerChannelFactoryInterface $logger) {
    $this->rcsSuperCategoryHelper = $rcs_super_helper;
    $this->drupalLogger = $logger->get('alshaya_rcs_super_category');;
  }

  /**
   * Syncs Mdc super categories as RCS Category.
   *
   * @command alshaya_rcs_super_category:sync
   *
   * @aliases sync-rcs-super-categories, srsc
   *
   * @usage drush sync-rcs-super-categories
   *   Syncs rcs super categories.
   */
  public function syncSuperCategories() {
    $this->drupalLogger->notice('Synchronizing all super categories...');
    $this->rcsSuperCategoryHelper->syncSuperCategories();
    $this->drupalLogger->notice('Successfully completed syncing super categories.');
  }
}
