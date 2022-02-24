<?php

namespace Drupal\alshaya_bazaar_voice\Commands;

use Drush\Commands\DrushCommands;
use Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice;

/**
 * Class Alshaya BazaarVoice remove photos commands.
 *
 * @package Drupal\alshaya_bazaar_voice\Commands
 */
class AlshayaBazaarVoiceRemovePhotosCommands extends DrushCommands {

  /**
   * Alshaya BazaarVoice.
   *
   * @var \Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice
   */
  protected $alshayaBazaarVoice;

  /**
   * AlshayaBazaarVoiceRemovePhotosCommands constructor.
   *
   * @param \Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice $alshaya_bazaar_voice
   *   Alshaya BazaarVoice.
   */
  public function __construct(AlshayaBazaarVoice $alshaya_bazaar_voice) {
    $this->alshayaBazaarVoice = $alshaya_bazaar_voice;
  }

  /**
   * Delete photos stored for temporarily purpose for bv photo upload field.
   *
   * @param array $options
   *   (optional) An array of options.
   *
   * @command alshaya_bazaar_voice:rm-bv-photos
   *
   * @aliases rmbvp
   *
   * @option batch-size
   *   The number of items to check per batch run.
   *
   * @usage drush rm-bv-photos
   *   Fetch and delete photos with default batch size.
   * @usage drush rm-bv-photos --batch-size=100
   *   Fetch and delete photos with batch of 100.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function removeUploadedPhotos(array $options = ['batch-size' => NULL]) {
    $batch_size = $options['batch-size'] ?? 50;
    $batch = [
      'finished' => [__CLASS__, 'batchFinish'],
      'title' => dt('Fetching list of photos'),
      'init_message' => dt('Starting deleting photos...'),
      'progress_message' => dt('Completed @current step of @total.'),
      'error_message' => dt('encountered error while deleting photos.'),
    ];

    $photos = $this->alshayaBazaarVoice->getUploadedPhotos();

    $batch['operations'][] = [[__CLASS__, 'batchStart'], [count($photos)]];
    foreach (array_chunk($photos, $batch_size) as $chunk) {
      $batch['operations'][] = [
        [__CLASS__, 'batchProcess'],
        [$chunk],
      ];
    }
    // Prepare the output of processed items and show.
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
    $context['results']['timestart'] = microtime(TRUE);
  }

  /**
   * Batch API callback; delete photos stored temporarilty for bv photo upload.
   *
   * @param array $photos
   *   A batch size.
   * @param mixed|array $context
   *   The batch current context.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function batchProcess(array $photos, &$context) {
    $context['results']['count'] += count($photos);

    if (empty($photos)) {
      return;
    }
    /** @var \Drupal\bazaar_voice\Service\AlshayaBazaarVoice $alshayaBazaarVoice */
    $alshaya_bazaar_voice = \Drupal::service('alshaya_bazaar_voice.service');
    $alshaya_bazaar_voice->deletePhotos($photos);

    $context['message'] = dt('Deleted items for @count out of @total.', [
      '@count' => $context['results']['count'],
      '@total' => $context['results']['total'],
    ]);
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
    $logger = \Drupal::logger('alshaya_bazaar_voice');
    if ($success) {
      if ($results['count']) {
        // Display Script End time.
        $time_end = microtime(TRUE);
        $execution_time = ($time_end - $results['timestart']) / 60;

        $logger->notice('Total @count items processed in time: @time.', [
          '@count' => $results['count'],
          '@time' => $execution_time,
        ]);
      }
      else {
        $logger->notice(t('No item to process.'));
      }
    }
    else {
      $error_operation = reset($operations);
      $logger->error('An error occurred while processing @operation with arguments : @args', [
        '@operation' => $error_operation[0],
        '@args' => print_r($error_operation[0], TRUE),
      ]);
    }
  }

}
