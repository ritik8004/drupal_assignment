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
      'finished' => [self::class, 'batchFinish'],
      'title' => dt('Indexing BV att value in algolia'),
      'init_message' => dt('Starting attrbute indexing...'),
      'progress_message' => dt('Completed @current step of @total.'),
      'error_message' => dt('encountered error while indexing attributes.'),
    ];

    $query = $this->alshayaEntityHelper->getNodesQuery();
    $nids = $query->execute();

    $batch['operations'][] = [
      [self::class, 'batchStart'],
      [is_countable($nids) ? count($nids) : 0],
    ];
    foreach (array_chunk($nids, $batch_size) as $chunk) {
      $batch['operations'][] = [
        [self::class, 'batchProcess'],
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
    $data = $alshaya_bazaar_voice->getDataFromBvReviewFeeds($skus, count($nids));

    if (empty($data)) {
      return;
    }
    // Use to bv data into DY.
    $dyProductDeltaFeedApiWrapper = \Drupal::service('dynamic_yield.product_feed_api_wrapper');
    $dy_config = \Drupal::config('dynamic_yield.settings');
    $feeds = $dy_config->get('feeds');

    /** @var \Drupal\alshaya_acm_product\SkuManager $skuManager */
    $skuManager = \Drupal::service('alshaya_acm_product.skumanager');

    // Algolia config details.
    $alshaya_algolia_react_setting_values = \Drupal::config('search_api.server.algolia');
    $backend_config = $alshaya_algolia_react_setting_values->get('backend_config');
    $app_id = $backend_config['application_id'];
    $app_secret_admin = $backend_config['api_key'];

    $client = SearchClient::create($app_id, $app_secret_admin);
    // Get Multiple algolia Index names.
    $algolia_index = \Drupal::service('alshaya_search_algolia.index_helper');
    $index_names = $algolia_index->getAlgoliaIndexNames();
    $languages = \Drupal::languageManager()->getLanguages();
    // Check if we are using SKU as ObjectID on search page.
    $index_sku_as_objectid = \Drupal::config('alshaya_search_algolia.settings')->get('index_sku_as_object_id');
    $node_manager = \Drupal::entityTypeManager()->getStorage('node');
    foreach ($index_names as $index) {
      $search_api_index = 'search_api.index.' . $index;
      $index_name = \Drupal::configFactory()->get($search_api_index)->get('options.algolia_index_name');
      // Get value for algolia_index_apply_suffix in search Api backend.
      $algolia_index_apply_suffix = \Drupal::configFactory()->get($search_api_index)->get('options.algolia_index_apply_suffix');
      if ($algolia_index_apply_suffix == 1) {
        // If algolia_index_apply_suffix enabled append language to index name.
        foreach ($languages as $language) {
          $bv_objects = [];
          $name = $index_name . '_' . $language->getId();
          $index = $client->initIndex($name);
          // Create object ids from node id and language to fetch results from
          // algolia.
          // Use the entity:node/{nid}:{lang} pattern by default.
          $objectIDs = array_map(fn($nid) => "entity:node/{$nid}:{$language->getId()}", $nids);
          if ($index_sku_as_objectid) {
            $objectIDs = [];
            foreach ($nids as $nid) {
              $node = $node_manager->load($nid);
              if ($node) {
                $objectIDs[] = $node->get('field_skus')->getString();
              }
            }
          }
          try {
            $objects = $index->getObjects($objectIDs);
            foreach ($objects['results'] as $object) {
              if (empty($object['sku'])) {
                continue;
              }
              $sanitized_sku = $skuManager->getSanitizedSku($object['sku']);
              if (empty($data['ReviewStatistics'][$sanitized_sku])) {
                continue;
              }
              $object['attr_bv_average_overall_rating'] = $data['ReviewStatistics'][$sanitized_sku]['AverageOverallRating'];
              $object['attr_bv_total_review_count'] = $data['ReviewStatistics'][$sanitized_sku]['TotalReviewCount'];
              $object['attr_bv_rating_distribution'] = $data['ReviewStatistics'][$sanitized_sku]['RatingDistribution'];
              $object['attr_bv_rating'] = $data['ReviewStatistics'][$sanitized_sku]['RatingStars'];
              $bv_objects['results'][] = $object;
            }

            // Save and update objects with BazaarVoice attributes in algolia.
            $index->saveObjects($bv_objects['results']);
          }
          catch (\Exception) {
            continue;
          }
        }
      }
      else {
        $bv_objects = [];
        $fields = [];
        $index = $client->initIndex($index_name);
        // Skus will be the object ids in case of product list algolia index.
        try {
          $objects = $index->getObjects($skus);
          foreach ($objects['results'] as $object) {
            if (empty($object['sku'])) {
              continue;
            }
            $sanitized_sku = $skuManager->getSanitizedSku($object['sku']);
            if (empty($data['ReviewStatistics'][$sanitized_sku])) {
              continue;
            }
            $object['attr_bv_total_review_count'] = $data['ReviewStatistics'][$sanitized_sku]['TotalReviewCount'];
            $object['attr_bv_rating_distribution'] = $data['ReviewStatistics'][$sanitized_sku]['RatingDistribution'];
            foreach ($languages as $language) {
              $object['attr_bv_average_overall_rating'][$language->getId()] = $data['ReviewStatistics'][$sanitized_sku]['AverageOverallRating'];
              $object['attr_bv_rating'][$language->getId()] = $data['ReviewStatistics'][$sanitized_sku]['RatingStars'];
            }
            $bv_objects['results'][] = $object;

            // Sync BV reviews info into DY.
            foreach ($feeds as $feed) {
              if ($feed['context'] === 'web') {
                $fields['sku'] = $object['sku'];
                $fields['bv_overall_rating_percentage'] = $data['ReviewStatistics'][$sanitized_sku]['OverallRatingPercentage'];
                $fields['bv_average_overall_rating'] = $data['ReviewStatistics'][$sanitized_sku]['AverageOverallRating'];
                $fields['bv_total_review_count'] = $data['ReviewStatistics'][$sanitized_sku]['TotalReviewCount'];
                $fields['bv_rating_distribution'] = json_encode($data['ReviewStatistics'][$sanitized_sku]['RatingDistribution']);
                $fields['bv_rating_distribution_average'] = json_encode($data['ReviewStatistics'][$sanitized_sku]['RatingDistributionAverage']);
                $fields['bv_recommended_average'] = $data['ReviewStatistics'][$sanitized_sku]['ProductRecommendedAverage'];
                $featured_reviews = $data['ReviewStatistics'][$sanitized_sku]['FeaturedReviews'];
                $locations = $data['ReviewStatistics'][$sanitized_sku]['locations'];

                // Prepare bv featured reviews.
                if (!empty($featured_reviews)) {
                  $fields['bv_featured_reviews'] = '';
                  foreach ($featured_reviews as $key => $review) {
                    if ($key === 'en') {
                      $fields['bv_featured_reviews'] = $review;
                    }
                    foreach ($locations as $code => $location) {
                      $fields['lng:' . $key . '_' . $code . ':bv_featured_reviews'] = $review;
                    }
                  }
                }

                $dy_data['data'] = $fields;
                $dyProductDeltaFeedApiWrapper->productFeedPartialUpdate($feed['api_key'], $feed['id'], $object['sku'], $dy_data);
              }
            }
          }
          // Save and update objects with BazaarVoice attributes in algolia.
          $index->saveObjects($bv_objects['results']);
        }
        catch (\Exception) {
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
