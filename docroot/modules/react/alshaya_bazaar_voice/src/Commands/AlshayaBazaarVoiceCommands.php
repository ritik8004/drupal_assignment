<?php

namespace Drupal\alshaya_bazaar_voice\Commands;

use Algolia\AlgoliaSearch\SearchClient;
use Drupal\alshaya_master\Service\AlshayaEntityHelper;
use Drush\Commands\DrushCommands;
use Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice;

/**
 * Class Alshaya BazaarVoice commands.
 *
 * @package Drupal\alshaya_bazaar_voice\Commands
 */
class AlshayaBazaarVoiceCommands extends DrushCommands {

  /**
   * Alshaya entity helper.
   *
   * @var \Drupal\alshaya_master\Service\AlshayaEntityHelper
   */
  protected $alshayaEntityHelper;

  /**
   * Alshaya BazaarVoice.
   *
   * @var \Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice
   */
  protected $alshayaBazaarVoice;

  /**
   * AlshayaBazaarVoiceCommands constructor.
   *
   * @param \Drupal\alshaya_master\Service\AlshayaEntityHelper $alshaya_entity_helper
   *   Alshaya entity helper.
   * @param \Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice $alshaya_bazaar_voice
   *   Alshaya BazaarVoice.
   */
  public function __construct(
                              AlshayaEntityHelper $alshaya_entity_helper,
                              AlshayaBazaarVoice $alshaya_bazaar_voice) {
    $this->alshayaEntityHelper = $alshaya_entity_helper;
    $this->alshayaBazaarVoice = $alshaya_bazaar_voice;
  }

  /**
   * Update/index BazaarVoice attributes values in algolia.
   *
   * @param array $options
   *   (optional) An array of options.
   *
   * @command bv_attr_val_algolia:index
   *
   * @aliases bvava-i
   *
   * @option batch-size
   *   The number of items to check per batch run.
   *
   * @usage drush index-bv-attr-val-algolia
   *   Fetch and index BazaarVoice attributes values in algolia.
   * @usage drush index-bv-attr-val-algolia --batch-size=100
   *   Fetch and index BazaarVoice attribute value in algolia with batch of 100.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function indexBvAttrValuesInAlgolia(array $options = ['batch-size' => NULL]) {
    $batch_size = $options['batch-size'] ?? 50;
    $batch = [
      'finished' => [__CLASS__, 'batchFinish'],
      'title' => dt('Indexing BV att value in algolia'),
      'init_message' => dt('Starting attrbute indexing...'),
      'progress_message' => dt('Completed @current step of @total.'),
      'error_message' => dt('encountered error while indexing attributes.'),
    ];

    $query = $this->alshayaEntityHelper->getNodesQuery();
    $nids = $query->execute();

    $batch['operations'][] = [[__CLASS__, 'batchStart'], [count($nids)]];
    foreach (array_chunk($nids, $batch_size) as $chunk) {
      $batch['operations'][] = [
        [__CLASS__, 'batchProcess'],
        [$chunk],
      ];
    }
    // Prepare the output of processed items and show.
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
    $context['results']['items'] = [];
    $context['results']['timestart'] = microtime(TRUE);
  }

  /**
   * Batch API callback; update BazaarVoice attributes in algolia.
   *
   * @param array $nids
   *   A batch size.
   * @param mixed|array $context
   *   The batch current context.
   *
   * @throws \AlgoliaSearch\AlgoliaException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function batchProcess(array $nids, &$context) {
    $context['results']['count'] += count($nids);

    $alshaya_bazaar_voice = \Drupal::service('alshaya_bazaar_voice.service');
    $skus = $alshaya_bazaar_voice->getSkusByNodeIds($nids);
    $data = $alshaya_bazaar_voice->getDataFromBvReviewFeeds($skus);

    if (empty($data)) {
      return;
    }

    // Algolia config details.
    $alshaya_algolia_react_setting_values = \Drupal::config('search_api.server.algolia');
    $backend_config = $alshaya_algolia_react_setting_values->get('backend_config');
    $app_id = $backend_config['application_id'];
    $app_secret_admin = $backend_config['api_key'];

    $client = SearchClient::create($app_id, $app_secret_admin);
    // Get Multiple algolia Index names.
    $algolia_index = \Drupal::service('alshaya_search_algolia.index_helper');
    $index_names = $algolia_index->getAlgoliaIndexNames();
    foreach ($index_names as $indexName) {
      $search_api_index = 'search_api.index.' . $indexName;
      $index_name = \Drupal::configFactory()->get($search_api_index)->get('options.algolia_index_name');
      // Get value for algolia_index_apply_suffix in search Api backend.
      $algolia_index_apply_suffix = \Drupal::configFactory()->get($search_api_index)->get('options.algolia_index_apply_suffix');
      $languages = \Drupal::languageManager()->getLanguages();
      if ($algolia_index_apply_suffix == 1) {
        // If algolia_index_apply_suffix enabled append language to index name.
        foreach ($languages as $language) {
          $bv_objects = [];
          $name = $index_name . '_' . $language->getId();
          $index = $client->initIndex($name);

          // Create object ids from node id and language to fetch results from
          // algolia.
          $objectIDs = array_map(function ($nid) use ($language) {
            return "entity:node/{$nid}:{$language->getId()}";
          }, $nids);

          try {
            $objects = $index->getObjects($objectIDs);
            foreach ($objects['results'] as $object) {
              if (empty($data['ReviewStatistics'][$object['sku']])) {
                continue;
              }
              $object['attr_bv_average_overall_rating'] = $data['ReviewStatistics'][$object['sku']]['AverageOverallRating'];
              $object['attr_bv_total_review_count'] = $data['ReviewStatistics'][$object['sku']]['TotalReviewCount'];
              $object['attr_bv_rating_distribution'] = $data['ReviewStatistics'][$object['sku']]['RatingDistribution'];
              $object['attr_bv_rating'] = $data['ReviewStatistics'][$object['sku']]['RatingStars'];
              $bv_objects['results'][] = $object;
            }

            // Save and update objects with bBazaarVoicev attributes in algolia.
            $result = $index->saveObjects($bv_objects['results']);
            $context['results']['items'][] = $result;
          }
          catch (\Exception $e) {
            continue;
          }
        }
      }
      else {
        $bv_objects = [];
        $name = $index_name;
        $index = $client->initIndex($name);

        // Create object ids from node id and language to fetch results from
        // algolia.
        $objectIDs = $skus;

        try {
          $objects = $index->getObjects($objectIDs);
          foreach ($objects['results'] as $object) {
            if (empty($data['ReviewStatistics'][$object['sku']])) {
              continue;
            }
            $object['attr_bv_average_overall_rating'] = $data['ReviewStatistics'][$object['sku']]['AverageOverallRating'];
            $object['attr_bv_total_review_count'] = $data['ReviewStatistics'][$object['sku']]['TotalReviewCount'];
            $object['attr_bv_rating_distribution'] = $data['ReviewStatistics'][$object['sku']]['RatingDistribution'];
            $object['attr_bv_rating'] = $data['ReviewStatistics'][$object['sku']]['RatingStars'];
            $bv_objects['results'][] = $object;
          }

          // Save and update objects with bBazaarVoicev attributes in algolia.
          $result = $index->saveObjects($bv_objects['results']);
          $context['results']['items'][] = $result;
        }
        catch (\Exception $e) {
          continue;
        }
      }
    }

    $context['message'] = dt('Updated items for @count out of @total.', [
      '@count' => $context['results']['count'],
      '@total' => $context['results']['total'],
    ]);
  }

  /**
   * Batch API callback; Write output in message.
   *
   * @param mixed|array $context
   *   The batch current context.
   */
  public static function batchGenerate(&$context) {
    if (empty($context['results']['items'])) {
      return;
    }

    $context['message'] = json_encode($context['results']['items']);
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

  /**
   * Sync fields in webform getting from bazaarvoice config hub.
   *
   * @param string $product_id
   *   Product id is required to get fields from BazaarVoice.
   *
   * @command alshaya_bazaar_voice:alshaya-sync-bv-fields
   *
   * @option $product_id
   *   Product id required by BazaarVoice submission form api.
   *
   * @aliases asbvf,alshaya-sync-bv-fields
   *
   * @usage drush alshaya-sync-bv-fields Y5BOY5PMDM05
   *   Sync fields in webform getting from bazaarvoice config hub.
   */
  public function syncFieldsFromBvPortal($product_id = '') {
    $sync_field = $this->alshayaBazaarVoice->syncFieldsFromBvSubmissionForm($product_id);
    if ($sync_field) {
      $this->io()->success(dt('Sync fields done successfully.'));
    }
    elseif (!$sync_field) {
      $this->io()->warning(dt('No new field found for sync.'));
    }
    else {
      $this->io()->error(dt('Error while syncing form fields.'));
    }
  }

}
