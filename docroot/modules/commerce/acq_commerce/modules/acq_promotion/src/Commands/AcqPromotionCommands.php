<?php

namespace Drupal\acq_promotion\Commands;

use Consolidation\SiteAlias\SiteAliasManagerAwareInterface;
use Consolidation\SiteAlias\SiteAliasManagerAwareTrait;
use Drupal\acq_promotion\AcqPromotionsManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Class Acq Promotion Commands.
 *
 * @package Drupal\acq_promotion\Commands
 */
class AcqPromotionCommands extends DrushCommands implements SiteAliasManagerAwareInterface {

  /**
   * Logger Channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

  use SiteAliasManagerAwareTrait;

  /**
   * Acq Promotions Manager.
   *
   * @var \Drupal\acq_promotion\AcqPromotionsManager
   */
  private $acqPromotionsManager;

  /**
   * AcqPromotionCommands constructor.
   *
   * @param \Drupal\acq_promotion\AcqPromotionsManager $acqPromotionsManager
   *   Acq Promotion Manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger Factory.
   */
  public function __construct(AcqPromotionsManager $acqPromotionsManager,
                              LoggerChannelFactoryInterface $loggerChannelFactory) {
    $this->acqPromotionsManager = $acqPromotionsManager;
    $this->drupalLogger = $loggerChannelFactory->get('acq_promotion');
  }

  /**
   * Run a full synchronization of all commerce promotion records.
   *
   * @param array $options
   *   Command options.
   *
   * @command acq_promotion:sync-promotions
   *
   * @option types Type of promotions that need to be synced.
   *
   * @validate-module-enabled acq_promotion
   *
   * @aliases acspm,sync-commerce-promotions
   *
   * @usage drush acspm
   *   Run a full synchronization of all available promotions.
   * @usage drush acspm --types=cart
   *   Run a full synchronization of all available cart promotions.
   */
  public function syncPromotions(array $options = ['types' => NULL]) {
    if ($types = $options['types']) {
      $this->drupalLogger->notice(dt('Synchronizing all @types commerce promotions, this usually takes some time...', ['@types' => $types]));
      $types = explode(',', $types);
      $types = array_map('trim', $types);
      $this->acqPromotionsManager->syncPromotions($types);
    }
    else {
      $this->drupalLogger->notice(dt('Synchronizing all commerce promotions, this usually takes some time...'));
      $this->acqPromotionsManager->syncPromotions();
    }

    $this->drupalLogger->notice(dt('Promotion sync completed.'));
  }

  /**
   * Run a full sync of all commerce promotion records and process queues.
   *
   * @param array $options
   *   Command options.
   *
   * @command acq_promotion:sync-and-process-promotions
   *
   * @option types Type of promotions that need to be synced.
   *
   * @validate-module-enabled acq_promotion
   *
   * @aliases sync-and-process-promotions
   *
   * @usage drush sync-and-process-promotions
   *   Run a full synchronization of all available promotions.
   * @usage drush sync-and-process-promotions --types=cart
   *   Run a full synchronization of all available cart promotions.
   */
  public function syncPromotionsAndRunQueues(array $options = ['types' => NULL]) {
    $selfRecord = $this->siteAliasManager()->getSelf();
    $options = array_filter($options);
    /** @var \Consolidation\SiteProcess\SiteProcess $acspm */
    $acspm = $this->processManager()->drush($selfRecord, 'acspm', [], $options);
    $acspm->run($acspm->showRealtime());

    $command = sprintf('screen -dm bash -c "cd %s; drush --uri=%s queue-run acq_promotion_attach_queue"', $selfRecord->get('root'), $selfRecord->get('uri'));
    /** @var \Consolidation\SiteProcess\SiteProcess $attach */
    $attach = $this->processManager()->process($command);
    $attach->run($attach->showRealtime());

    $this->drupalLogger->notice(dt('Promotions synced and queue-run started in screens.'));
  }

}
