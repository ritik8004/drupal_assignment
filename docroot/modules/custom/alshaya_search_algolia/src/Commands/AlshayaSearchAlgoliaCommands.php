<?php

namespace Drupal\alshaya_search_algolia\Commands;

use Algolia\AlgoliaSearch\SearchClient;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_master\Service\AlshayaEntityHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\node\NodeInterface;
use Drush\Commands\DrushCommands;

/**
 * Class Alshaya Search Algolia Commands.
 *
 * @package Drupal\alshaya_search_algolia\Commands
 */
class AlshayaSearchAlgoliaCommands extends DrushCommands {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Alshaya entity helper.
   *
   * @var \Drupal\alshaya_master\Service\AlshayaEntityHelper
   */
  protected $alshayaEntityHelper;

  /**
   * AlshayaSearchAlgoliaCommands constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\alshaya_master\Service\AlshayaEntityHelper $alshaya_entity_helper
   *   Alshaya entity helper.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    AlshayaEntityHelper $alshaya_entity_helper
  ) {
    $this->configFactory = $configFactory;
    $this->alshayaEntityHelper = $alshaya_entity_helper;
  }

  /**
   * Check product price diff.
   *
   * @param array $options
   *   (optional) An array of options.
   *
   * @command alshaya_search_algolia:verify_price
   *
   * @aliases alshaya-algolia-verify-price
   *
   * @option batch-size
   *   The number of items to check per batch run.
   *
   * @usage drush alshaya-algolia-verify-price
   *   check products price.
   * @usage drush alshaya-algolia-verify-price --batch-size=200
   *   check products price with batch of 200.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function verifyPrices(array $options = ['batch-size' => NULL]) {
    $batch_size = $options['batch-size'] ?? 50;
    $batch = [
      'finished' => [self::class, 'batchFinish'],
      'title' => dt('Comparing products price with algolia'),
      'init_message' => dt('Starting price comparison...'),
      'progress_message' => dt('Completed @current products of @total.'),
      'error_message' => dt('encountered error while verifying prices.'),
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
    // Prepare the output of faulty results and show.
    $batch['operations'][] = [[self::class, 'batchGenerate'], []];
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
    $context['results']['faulty'] = 0;
    $context['results']['files'] = [];
    $context['results']['timestart'] = microtime(TRUE);
  }

  /**
   * Batch API callback; verify the products price diff.
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
    $alshaya_algolia_react_setting_values = \Drupal::config('alshaya_algolia_react.settings');
    $app_id = $alshaya_algolia_react_setting_values->get('application_id');
    $app_secret_admin = $alshaya_algolia_react_setting_values->get('search_api_key');
    $client = SearchClient::create($app_id, $app_secret_admin);

    $index_name = \Drupal::configFactory()->get('search_api.index.alshaya_algolia_index')->get('options.algolia_index_name');
    $languages = \Drupal::languageManager()->getLanguages();

    $skuManager = \Drupal::service('alshaya_acm_product.skumanager');

    $logger = \Drupal::logger('alshaya_search_algolia');

    SkuManager::$colorSplitMergeChildren = FALSE;
    foreach ($languages as $language) {
      $name = $index_name . '_' . $language->getId();
      $index = $client->initIndex($name);

      // Create object ids from node id and language to fetch results from
      // algolia.
      $objectIDs = array_map(fn($nid) => "entity:node/{$nid}:{$language->getId()}", $nids);

      try {
        $objects = $index->getObjects($objectIDs, [
          'attributesToRetrieve' => [
            'nid',
            'final_price',
            'original_price',
            'sku',
          ],
        ]);

      }
      catch (\Exception) {
        continue;
      }

      // Loop through only the available results to ignore not indexed data.
      foreach (array_filter($objects['results']) as $object) {
        if (empty($object)) {
          continue;
        }

        $node = \Drupal::entityTypeManager()->getStorage('node')->load($object['nid']);
        if (!$node instanceof NodeInterface) {
          continue;
        }

        $sku = SKU::loadFromSku($object['sku'], $language->getId());
        if (!$sku instanceof SKU) {
          $logger->error('Not able to load sku: @sku', [
            '@sku' => $object['sku'],
          ]);
          continue;
        }

        $prices = $skuManager->getMinPrices($sku);
        if (((float) $object['original_price'] !== (float) $prices['price'])
            || ((float) $object['final_price'] !== (float) $prices['final_price'])
        ) {
          $context['results']['products'][$language->getId()][$object['nid']] = [
            'nid' => $object['nid'],
            'sku' => $sku->getSku(),
            'lang' => $language->getId(),
            'price_diff' => [$object, $prices],
          ];
        }
      }
    }

    $context['message'] = dt('Verified price for @count out of @total.', [
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
    if (empty($context['results']['products'])) {
      return;
    }

    foreach ($context['results']['products'] as $products) {
      if (empty($products)) {
        continue;
      }

      $context['results']['faulty'] += count(array_values($products));

      // Encode each result to dump.
      $records = array_map(fn($item) => json_encode($item), array_values($products));

      $context['message'] = implode(PHP_EOL, $records);
    }
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
    $logger = \Drupal::logger('alshaya_search_algolia');
    if ($success) {
      if ($results['count']) {
        // Display Script End time.
        $time_end = microtime(TRUE);
        $execution_time = ($time_end - $results['timestart']) / 60;

        $logger->notice('Verified @count products and found @faulty results in time: @time.', [
          '@count' => $results['count'],
          '@time' => $execution_time,
          '@faulty' => $results['faulty'],
        ]);
      }
      else {
        $logger->notice(t('No new products to verify.'));
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
