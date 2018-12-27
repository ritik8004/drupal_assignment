<?php

namespace Drupal\alshaya_hm_temp_skus_filter\Commands;

use Drupal\acq_sku\Entity\SKU;
use Drupal\node\NodeInterface;
use Drush\Commands\DrushCommands;
use SplFixedArray;

/**
 * Class AlshayaHmSkusCleanupCommand.
 *
 * @package Drupal\alshaya_hm_temp_skus_filter\Commands
 */
class AlshayaHmSkusCleanupCommand extends DrushCommands {

  /**
   * Cleanup all SKUs missing a descriptivestilllife image.
   *
   * @param array $options
   *   Options for drush command.
   *
   * @command alshaya_hm_temp_skus_filter:cleanup
   *
   * @option size Batch size for cleanup execution.
   *
   * @validate-module-enabled alshaya_hm_temp_skus_filter
   *
   * @aliases alshaya_hm_skus_cleanup
   */
  public function cleanSkusWithoutImages(array $options = ['size' => 100, 'process_multipack' => FALSE]) {
    $batch_size = $options['size'];
    $count_descriptive = 0;

    // @codingStandardsIgnoreStart
    $connection = \Drupal::database();
    // @codingStandardsIgnoreEnd

    // Fetch list of all season5+ SKUs which don't have any DescriptiveStillLife
    // images.
    $query = $connection->select('acq_sku_field_data', 'asfd');
    $query->fields('asfd', ['sku']);
    $query->condition('type', 'configurable', "!=");
    $query->condition('attr_assets__value', '%is_old_format%', 'NOT LIKE');
    $query->condition('attr_assets__value', '%DescriptiveStillLife%', 'NOT LIKE');
    $query->distinct();
    $res = $query->execute();
    $entity_list = $res->fetchAll();

    // Process multipack case if passed via options.
    if ($options['process_multipack']) {
      // Get total count of SKUs with DescriptiveStillLife that needs to be
      // processed. This will be needed to populate the batch.
      $query = $connection->select('acq_sku_field_data', 'asfd');
      $query->addExpression('COUNT(DISTINCT(asfd.sku))', 'count');
      $query->condition('asfd.type', 'configurable', "!=");
      $query->condition('asfd.attr_assets__value', '%is_old_format%', 'NOT LIKE');
      $query->condition('asfd.attr_assets__value', '%DescriptiveStillLife%', 'LIKE');
      $count_descriptive = $query->execute()->fetchField();
    }

    $total = count($entity_list) + $count_descriptive;

    $chunks = array_chunk($entity_list, $batch_size);
    $progress = 0;

    // Calculate size of Array & allocate space for it.
    $array_size = ceil(count($entity_list) / $batch_size) + ceil($count_descriptive / $batch_size);
    $operations = new SplFixedArray($array_size);
    $counter = 0;

    // Process SKUs missing DescriptiveStillLife imgages.
    foreach ($chunks as $chunk) {
      $progress += count($chunk);
      $operations[$counter] = [
        ['\Drupal\alshaya_hm_temp_skus_filter\Commands\AlshayaHmSkusCleanupCommand', 'processBatch'],
        [
          $chunk,
          dt('@percent% (Processing @progress of @total)', [
            '@percent' => round(100 * $progress / $total),
            '@progress' => $progress,
            '@total' => $total,
          ]),
        ],
      ];
      $counter++;
    }

    // Process multipack case if passed via options.
    if ($options['process_multipack']) {
      for ($i = 0; $i < $count_descriptive; $i += $batch_size) {
        $progress += count($chunk);

        // Keeping this light since the loop might run for a huge number of
        // SKUs. Passing only start & end range to the chunks & load items to be
        // processed while processing the batch itself.
        $operations[$counter] = [
          ['\Drupal\alshaya_hm_temp_skus_filter\Commands\AlshayaHmSkusCleanupCommand', 'processBatch'],
          [
            [['start_range' => $i, 'end_range' => $i + $batch_size]],
            dt('@percent% (Processing @progress of @total)', [
              '@percent' => round(100 * $progress / $total),
              '@progress' => $progress,
              '@total' => $total,
            ]),
          ],
        ];
        $counter++;
      }
    }

    $batch = [
      'operations' => $operations,
      'title' => dt('Entity process callback batch'),
      'finished' => ['\Drupal\alshaya_hm_temp_skus_filter\Commands\AlshayaHmSkusCleanupCommand', 'postCleanupCallback'],
      'progress_message' => dt('@current entities of @total were processed.'),
    ];

    // Get the batch process all ready!
    batch_set($batch);

    // Start processing the batch operations.
    drush_backend_batch_process();
  }

  /**
   * Helper function to cleanup SKUs.
   *
   * @param array[] $chunk
   *   The array of objects containing sku value.
   * @param string $details
   *   A feedback message to be sent to the user.
   * @param mixed $context
   *   It is used to interact with the process executing the batches.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processBatch(array $chunk, $details, &$context) {
    $context['message'] = $details;

    // Make sure to only initialize the results the first time.
    if (!isset($context['results']['success'])) {
      $context['results']['success'] = $context['results']['error'] = 0;
    }

    // @codingStandardsIgnoreStart
    global $acsf_site_code;
    global $country_code;

    $connection = \Drupal::database();
    // @codingStandardsIgnoreEnd

    $fp = fopen('/tmp/skus_' . $acsf_site_code . $country_code . '.log', 'a');

    foreach ($chunk as $item) {
      // For multipack case, do the query while processing the batch & fetch
      // items we interested in processing.
      if (is_array($item) && isset($item['start_range']) && isset($item['end_range'])) {
        // Set memory limit to -1 while processing the multipack case.
        ini_set('memory_limit', -1);
        $query = $connection->select('acq_sku_field_data', 'asfd');
        $query->fields('asfd', ['sku', 'attr_assets__value']);
        $query->condition('asfd.type', 'configurable', "!=");
        $query->condition('asfd.attr_assets__value', '%is_old_format%', 'NOT LIKE');
        $query->condition('asfd.attr_assets__value', '%DescriptiveStillLife%', 'LIKE');
        $query->range($item['start_range'], $item['end_range']);
        $query->distinct();
        $res = $query->execute();
        $items = $res->fetchAll();

        foreach ($items as $item) {
          if (self::processSku($item, $context)) {
            fwrite($fp, $item->sku . PHP_EOL);
            $context['results']['success']++;
          }
          else {
            $context['results']['error']++;
          }
        }
      }
      else {
        if (self::processSku($item, $context)) {
          fwrite($fp, $item->sku . PHP_EOL);
          $context['results']['success']++;
        }
        else {
          $context['results']['error']++;
        }
      }
    }
    fclose($fp);
  }

  /**
   * Helper function to delete the SKUs.
   *
   * @param mixed $item
   *   SKU code for the product that needs to be deleted.
   * @param mixed $context
   *   Used to interact with the process executing the batches & passing data.
   *
   * @return bool
   *   TRUE if deletion was successful.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function processSku($item, $context) {
    // SKUs with DescriptiveStillLife Images. Process only if its multipack
    // attribute is set to FALSE for all images.
    if ((!empty($attr_assets__value = $item->attr_assets__value)) &&
      (!empty($assets_data  = unserialize($attr_assets__value)))) {
      // Set default to delete all SKUs with DescriptiveStillLife image.
      $skip_sku = FALSE;

      foreach ($assets_data as $assets_datum) {
        // Skip deleting the SKU if we find a DescriptiveStillLife image with
        // multipack set to TRUE in the list of assets.
        if (($assets_datum['sortAssetType'] == 'DescriptiveStillLife') &&
          ($assets_datum['Data']['IsMultiPack'] == 'true')) {
          $skip_sku = TRUE;
          break;
        }
      }

      // Return if no DescriptiveStillLife image found with multipack set to
      // TRUE.
      if ($skip_sku) {
        return FALSE;
      }
    }

    $sku = SKU::loadFromSku($item->sku);

    if (!$sku instanceof SKU) {
      return FALSE;
    }

    // @codingStandardsIgnoreStart
    /** @var \Drupal\alshaya_acm_product\SkuManager $skumanager */
    $skumanager = \Drupal::service('alshaya_acm_product.skumanager');
    // @codingStandardsIgnoreEnd

    $parent_sku = $skumanager->getParentSkuBySku($sku);

    // Delete the SKU.
    $sku->delete();

    // Delete parent SKUs & their corresponding node if the parent SKU has no
    // children.
    if (($parent_sku instanceof SKU) &&
      (empty($skumanager->getChildSkus($parent_sku))) &&
      ($parent_node = $skumanager->getDisplayNode($sku)) &&
      ($parent_node instanceof NodeInterface)) {
      $parent_sku->delete();
      $parent_node->delete();
      $context['results']['parent_sku_processed'][] = $parent_sku->getSku();
    }

    $context['results']['skus_processed'][] = $item->sku;
    return TRUE;
  }

  /**
   * This callback is called when the batch process finishes.
   */
  public function postCleanupCallback($success, $results, $operations) {
    // Log data to watchdog around SKUs that were deleted & configurable SKUs
    // that were cleaned up as a result of deleting simple SKUs.
    // @codingStandardsIgnoreStart
    if (!empty($results['skus_processed'])) {
      \Drupal::logger('acq_sku')->info(dt('Cleaned up following SKUs without any DescriptiveStillLife image. List of SKUs deleted: @skus', ['@skus' => implode(',', $results['skus_processed'])]));
    }
    if (!empty($results['parent_sku_processed'])) {
      \Drupal::logger('acq_sku')->info(dt('Cleaned up configurable SKUs without any children items: @skus', ['@skus' => implode(',', $results['parent_sku_processed'])]));
    }
    // @codingStandardsIgnoreEnd
  }

}
