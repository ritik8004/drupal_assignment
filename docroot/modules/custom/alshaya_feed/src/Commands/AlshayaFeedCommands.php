<?php

namespace Drupal\alshaya_feed\Commands;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_feed\AlshayaFeed;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drush\Commands\DrushCommands;

/**
 * Class AlshayaFeedCommands.
 *
 * @package Drupal\alshaya_feed\Commands
 */
class AlshayaFeedCommands extends DrushCommands {

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * The date time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  private $dateTime;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Alshaya feed service object.
   *
   * @var \Drupal\alshaya_feed\AlshayaFeed
   */
  protected $alshayaFeed;

  /**
   * Static reference to logger object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected static $loggerStatic;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * AlshayaFeedCommands constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   * @param \Drupal\Component\Datetime\TimeInterface $date_time
   *   The Date Time service.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\alshaya_feed\AlshayaFeed $alshaya_feed
   *   Alshaya feed service object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   */
  public function __construct(
    Connection $connection,
    TimeInterface $date_time,
    LoggerChannelInterface $logger,
    SkuManager $sku_manager,
    EntityTypeManagerInterface $entity_type_manager,
    AlshayaFeed $alshaya_feed,
    ConfigFactoryInterface $configFactory
  ) {
    $this->connection = $connection;
    $this->dateTime = $date_time;
    $this->setLogger($logger);
    self::$loggerStatic = $logger;
    $this->skuManager = $sku_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->alshayaFeed = $alshaya_feed;
    $this->configFactory = $configFactory->get('alshaya_feed.settings');
  }

  /**
   * Create product feed.
   *
   * @param array $options
   *   (optional) An array of options.
   *
   * @command alshaya_feed:generate
   *
   * @aliases create-products-feed
   *
   * @option batch-size
   *   The number of items to generate/process per batch run. If batch size is
   *   not provided, then default `feed_batch_chunk_size` from
   *   `alshaya_feed.settings` config will be used.
   *
   * @usage drush create-products-feed
   *   Create products feed.
   * @usage drush create-products-feed --batch-size=200
   *   Generate products feed with batch of 200.
   */
  public function generateFeed(array $options = ['batch-size' => NULL]) {
    $batch_size = $options['batch-size'] ?? $this->configFactory->get('batch_size');
    $batch = [
      'operations' => [
        [[__CLASS__, 'batchStart'], []],
        [[__CLASS__, 'batchProcess'], [$batch_size]],
        [[__CLASS__, 'batchGenerate'], []],
      ],
      'finished' => [__CLASS__, 'batchFinish'],
      'title' => $this->output->writeln(dt('Generating product feed')),
      'init_message' => $this->output->writeln(dt('Starting feed creation...')),
      'progress_message' => '',
      'error_message' => $this->output->writeln(dt('encountered error while generating feed.')),
    ];

    batch_set($batch);
    drush_backend_batch_process();
  }

  /**
   * Batch callback; initialize the batch.
   *
   * @param mixed|array $context
   *   The batch current context.
   */
  public static function batchStart(&$context) {
    $context['results']['updates'] = 0;
    $context['results']['products'] = [];
    \Drupal::service('alshaya_feed.generate')->clear();
  }

  /**
   * Batch API callback; collect the products data.
   *
   * @param int $batch_size
   *   A batch size.
   * @param mixed|array $context
   *   The batch current context.
   */
  public static function batchProcess($batch_size, &$context) {
    \Drupal::service('alshaya_feed.generate')->batchProcess($batch_size, $context);
  }

  /**
   * Batch API callback; Write the xml file.
   *
   * @param mixed|array $context
   *   The batch current context.
   */
  public static function batchGenerate(&$context) {
    \Drupal::service('alshaya_feed.generate')->dumpXml($context);
  }

  /**
   * Finishes the update process and stores the results.
   *
   * @param bool $success
   *   Indicate that the batch API tasks were all completed successfully.
   * @param array $results
   *   An array of all the results that were updated in update_do_one().
   * @param array $operations
   *   A list of all the operations that had not been completed by batch API.
   */
  public static function batchFinish($success, array $results, array $operations) {
    if ($success) {
      if ($results['updates']) {
        \Drupal::service('messenger')->addMessage(
          \Drupal::translation()
            ->formatPlural(
              $results['updates'],
              'Generated 1 product feed.',
              'Generated @count products feed.'
            )
        );
      }
      else {
        \Drupal::service('messenger')->addMessage(t('No new products to generate.'));
      }
    }
    else {
      $error_operation = reset($operations);
      \Drupal::service('messenger')
        ->addMessage(t('An error occurred while processing @operation with arguments : @args'), [
          '@operation' => $error_operation[0],
          '@args' => print_r($error_operation[0]),
        ]);
    }
  }

}
