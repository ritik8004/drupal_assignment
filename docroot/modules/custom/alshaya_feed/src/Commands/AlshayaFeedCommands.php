<?php

namespace Drupal\alshaya_feed\Commands;

use Drupal\alshaya_feed\AlshayaFeed;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Class Alshaya Feed Commands.
 *
 * @package Drupal\alshaya_feed\Commands
 */
class AlshayaFeedCommands extends DrushCommands {

  /**
   * Logger Channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Alshaya feed service.
   *
   * @var \Drupal\alshaya_feed\AlshayaFeed
   */
  protected $alshayaFeed;

  /**
   * Dynamic Yield config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $dynamicYieldConfig;

  /**
   * AlshayaFeedCommands constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\alshaya_feed\AlshayaFeed $alshaya_feed
   *   Alshaya feed service.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    AlshayaFeed $alshaya_feed
  ) {
    $this->configFactory = $configFactory->get('alshaya_feed.settings');
    $this->alshayaFeed = $alshaya_feed;
    $this->dynamicYieldConfig = $configFactory->get('dynamic_yield.settings');
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
      'finished' => [__CLASS__, 'batchFinish'],
      'title' => dt('Generating product feed'),
      'init_message' => dt('Starting feed creation...'),
      'progress_message' => dt('Completed @current step of @total.'),
      'error_message' => dt('encountered error while generating feed.'),
    ];

    $query = $this->alshayaFeed->getNodesQuery();
    $nids = $query->execute();

    $batch['operations'][] = [[__CLASS__, 'batchStart'], [count($nids)]];
    foreach (array_chunk($nids, $batch_size) as $chunk) {
      $batch['operations'][] = [
        [__CLASS__, 'batchProcess'],
        [$chunk],
      ];
    }
    $batch['operations'][] = [[__CLASS__, 'batchGenerate'], []];
    batch_set($batch);
    drush_backend_batch_process();
  }

  /**
   * Batch callback; initialize the batch.
   *
   * @param int $total
   *   The total number of nids to process.
   * @param mixed|array $context
   *   The batch current context.
   */
  public static function batchStart($total, &$context) {
    $context['results']['total'] = $total;
    $context['results']['count'] = 0;
    $context['results']['products'] = [];
    $context['results']['files'] = [];
    $context['results']['feed_template'] = drupal_get_path('module', 'alshaya_feed') . '/templates/feed.html.twig';
    $context['results']['timestart'] = microtime(TRUE);
    \Drupal::service('alshaya_feed.generate')->clear();
  }

  /**
   * Batch API callback; collect the products data.
   *
   * @param array $nids
   *   A batch size.
   * @param mixed|array $context
   *   The batch current context.
   */
  public static function batchProcess(array $nids, &$context) {
    \Drupal::service('alshaya_feed.generate')->process($nids, $context);
  }

  /**
   * Batch API callback; Write the xml file.
   *
   * @param mixed|array $context
   *   The batch current context.
   */
  public static function batchGenerate(&$context) {
    $feed_generate = \Drupal::service('alshaya_feed.generate');
    $feed_generate->dumpXml($context);
    $feed_generate->publish();
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
      if ($results['count']) {
        // Display Script End time.
        $time_end = microtime(TRUE);
        $execution_time = ($time_end - $results['timestart']) / 60;

        \Drupal::service('messenger')->addMessage(
          \Drupal::translation()
            ->formatPlural(
              $results['count'],
              'Generated 1 product feed in time: @time.',
              'Generated @count products feed in time: @time.',
              ['@time' => $execution_time]
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
