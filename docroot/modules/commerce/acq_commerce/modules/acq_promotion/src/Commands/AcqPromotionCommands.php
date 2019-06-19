<?php
namespace Drupal\acq_promotion\Commands;

use Drupal\acq_promotion\AcqPromotionsManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;

class AcqPromotionCommands extends DrushCommands {

  /**
   * Acq Promotions Manager.
   *
   * @var \Drupal\acq_promotion\AcqPromotionsManager
   */
  private $acqPromotionsManager;

  /**
   * AcqPromotionCommands constructor.
   * @param \Drupal\acq_promotion\AcqPromotionsManager $acqPromotionsManager
   *   Acq Promotion Manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger Factory.
   */
  public function __construct(AcqPromotionsManager $acqPromotionsManager,
                              LoggerChannelFactoryInterface $loggerChannelFactory) {
    $this->acqPromotionsManager = $acqPromotionsManager;
    $this->logger = $loggerChannelFactory->get('acq_promotion');
  }

  /**
   * Run a full synchronization of all commerce promotion records.
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
   * @param array $options
   */
  public function syncPromotions($options = ['types' => NULL]) {
    if ($types = $options['types']) {
      $this->logger->notice(dt('Synchronizing all @types commerce promotions, this usually takes some time...', ['@types' => $types]));
      $types = explode(',', $types);
      $types = array_map('trim', $types);
      $this->acqPromotionsManager->syncPromotions($types);
    }
    else {
      $this->logger->notice(dt('Synchronizing all commerce promotions, this usually takes some time...'));
      $this->acqPromotionsManager->syncPromotions();
    }

    $this->logger->notice(dt('Promotion sync completed.'));
  }

}
