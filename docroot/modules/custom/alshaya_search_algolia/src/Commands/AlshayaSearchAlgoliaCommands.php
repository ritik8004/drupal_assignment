<?php

namespace Drupal\alshaya_search_algolia\Commands;

use AlgoliaSearch\Client;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_master\Service\AlshayaEntityHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\node\NodeInterface;
use Drush\Commands\DrushCommands;

/**
 * Class AlshayaSearchAlgoliaCommands.
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
    $this->configFactory = $configFactory->get('alshaya_feed.settings');
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
      'finished' => [__CLASS__, 'batchFinish'],
      'title' => dt('Comparing products price with algolia'),
      'init_message' => dt('Starting price comparison...'),
      'progress_message' => dt('Completed @current products of @total.'),
      'error_message' => dt('encountered error while verifying prices.'),
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
    $languages = \Drupal::languageManager()->getLanguages();
    $file_system = \Drupal::service('file_system');
    // Delete any existing file to dump new results.
    foreach ($languages as $language) {
      $wip_file = $file_system->realpath(file_default_scheme() . "://price_diff_{$language->getId()}.txt");
      if (file_exists($wip_file)) {
        $file_system->delete($wip_file);
      }
    }

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
    $app_id = Settings::get('block.block.alshayaalgoliareactautocomplete')['settings']['application_id'];
    $app_secret_admin = Settings::get('block.block.alshayaalgoliareactautocomplete')['settings']['search_api_key'];
    $client = new Client($app_id, $app_secret_admin);

    $index_name = \Drupal::configFactory()->get('search_api.index.alshaya_algolia_index')->get('options.algolia_index_name');
    $languages = \Drupal::languageManager()->getLanguages();

    $skuManager = \Drupal::service('alshaya_acm_product.skumanager');
    foreach ($languages as $language) {
      $name = $index_name . '_' . $language->getId();
      $index = $client->initIndex($name);

      // Create object ids from node id and language to fetch results from
      // algolia.
      $objetIDs = array_map(function ($nid) use ($language) {
          return "entity:node/{$nid}:{$language->getId()}";
      }, $nids);

      try {
        $objects = $index->getObjects($objetIDs, 'final_price,original_price,price,sku,nid');
      }
      catch (\Exception $e) {
        continue;
      }

      // Loop through only the available results to ignore not indexed data.
      foreach (array_filter($objects['results']) as $object) {
        $node = \Drupal::entityTypeManager()->getStorage('node')->load($object['nid']);
        if (!$node instanceof NodeInterface) {
          continue;
        }

        // Get SKU attached with node.
        $sku = $skuManager->getSkuForNode($node);
        if ($sku !== $object['sku']) {
          $context['results']['products'][$language->getId()][$object['nid']] = [
            'title' => 'sku mismatch',
            'nid' => $object['nid'],
            'sku' => $sku,
            'sku_diff' => $object,
          ];
          continue;
        }

        $sku = SKU::loadFromSku($sku, $language->getId());
        if (!$sku instanceof SKU) {
          continue;
        }

        if (empty($object)) {
          continue;
        }

        $product_color = '';
        if ($skuManager->isListingModeNonAggregated()) {
          $product_color = $node->get('field_product_color')->getString();
        }

        $prices = $skuManager->getMinPrices($sku, $product_color);
        $prices['final_price'] = 20;
        if (($object['original_price'] !== (float) $prices['price'])
            || ($object['price'] !== (float) $prices['price'])
            || ($object['final_price'] !== (float) $prices['final_price'])
        ) {
          $context['results']['products'][$language->getId()][$object['nid']] = [
            'nid' => $object['nid'],
            'sku' => $sku->getSku(),
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
   * Batch API callback; Write the txt file.
   *
   * @param mixed|array $context
   *   The batch current context.
   */
  public static function batchGenerate(&$context) {
    if (empty($context['results']['products'])) {
      return;
    }

    foreach ($context['results']['products'] as $lang => $products) {
      if (empty($products)) {
        continue;
      }

      $path = file_create_url(\Drupal::service('file_system')->realpath(file_default_scheme() . "://price_diff_{$lang}.txt"));
      if ($fp = fopen($path, 'a')) {
        $context['results']['files'][] = $path;
        $context['results']['faulty'] += count(array_values($products));
        // Encode each result to dump into text file.
        $records = array_map(function ($item) {
          return json_encode($item);
        }, array_values($products));
        fwrite($fp, implode(PHP_EOL, $records));
        fclose($fp);
      }
      else {
        \Drupal::logger('alshaya_search_algolia')->error('could not create a file: @file', ['@file' => $path]);
      }
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
    if ($success) {
      if ($results['count']) {
        // Display Script End time.
        $time_end = microtime(TRUE);
        $execution_time = ($time_end - $results['timestart']) / 60;

        \Drupal::service('messenger')->addMessage(
          \Drupal::translation()
            ->formatPlural(
              $results['count'],
              'Verified 1 product and found @faulty result in time: @time. check file at @files.',
              'Verified @count products and found @faulty results in time: @time. check files at @files',
              [
                '@time' => $execution_time,
                '@faulty' => $results['faulty'],
                '@files' => implode(',', $results['files']),
              ]
            )
        );
      }
      else {
        \Drupal::service('messenger')->addMessage(t('No new products to verify.'));
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
