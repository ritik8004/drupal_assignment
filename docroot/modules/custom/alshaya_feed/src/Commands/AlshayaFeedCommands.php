<?php

namespace Drupal\alshaya_feed\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Class AlshayaFeedCommands.
 *
 * @package Drupal\alshaya_feed\Commands
 */
class AlshayaFeedCommands extends DrushCommands {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * AlshayaFeedCommands constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory
  ) {
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
    $context['results']['markups'] = [];
    $context['results']['timestart'] = microtime(TRUE);
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
      if ($results['updates']) {
        // Display Script End time.
        $time_end = microtime(TRUE);
        $execution_time = ($time_end - $results['timestart']) / 60;

        \Drupal::service('messenger')->addMessage(
          \Drupal::translation()
            ->formatPlural(
              $results['updates'],
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
