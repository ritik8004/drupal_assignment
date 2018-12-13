<?php

namespace Drupal\alshaya_hm_temp_skus_filter\Commands;

use Drupal\acq_sku\Entity\SKU;
use Drupal\node\NodeInterface;
use Drush\Commands\DrushCommands;

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
    $entity_list_multipack = [];

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
      // Fetch list of all season5+ SKUs which have DescriptiveStillLife image.
      // Filtering on this data will be done as a part of the batch process itself
      $query = $connection->select('acq_sku_field_data', 'asfd');
      $query->fields('asfd', ['sku', 'attr_assets__value']);
      $query->condition('asfd.type', 'configurable', "!=");
      $query->condition('asfd.attr_assets__value', '%is_old_format%', 'NOT LIKE');
      $query->condition('asfd.attr_assets__value', '%DescriptiveStillLife%', 'LIKE');
      $query->distinct();
      $res = $query->execute();

      $entity_list_multipack = $res->fetchAll();
    }

    $entity_list = array_merge($entity_list, $entity_list_multipack);
    $chunks = array_chunk($entity_list, $batch_size);

    $total = count($entity_list);
    $progress = 0;
    $operations = [];
    foreach ($chunks as $chunk) {
      $progress += count($chunk);
      $operations[] = [
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
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processBatch(array $chunk, $details, &$context) {
    $context['message'] = $details;

    // Make sure to only initialize the results the first time.
    if (!isset($context['results']['success'])) {
      $context['results']['success'] = $context['results']['error'] = 0;
    }

    global $acsf_site_code;

    $fp = fopen('/tmp/skus_'  . $acsf_site_code . '.log','a');

    foreach ($chunk as $item) {
      if ($success = self::processSku($item, $context)) {
        fwrite($fp, $item->sku . PHP_EOL);
        $context['results']['success']++;
      }
      else {
        $context['results']['error']++;
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

    // Delete the SKU.
    $sku->delete();

    // Get parent SKU for the deleted SKU.
    $context['results']['skus_processed'][] = $sku->getSku();
    return TRUE;
  }

  /**
   * This callback is called when the batch process finishes.
   */
  public function postCleanupCallback($success, $results, $operations) {
    /** @var \Drupal\alshaya_acm_product\SkuManager $skumanager */
    // @codingStandardsIgnoreStart
    $skumanager = \Drupal::service('alshaya_acm_product.skumanager');
    // @codingStandardsIgnoreEnd
    if (!empty($results['skus_processed'])) {
      $parent_skus = array_unique($skumanager->getParentSkus($results['skus_processed']));

      $parent_sku_processed = [];
      // Post-process parent SKUs for the simple SKUs deleted.
      foreach ($parent_skus as $sku) {
        $parent_sku = SKU::loadFromSku($sku);

        // Delete parent SKUs & their corresponding node if the parent SKU has no
        // children.
        if (($parent_sku instanceof SKU) &&
          (empty($skumanager->getChildSkus($parent_sku))) &&
          ($parent_node = $skumanager->getDisplayNode($sku)) &&
          ($parent_node instanceof NodeInterface)) {
          $parent_sku->delete();
          $parent_node->delete();
          $parent_sku_processed[] = $sku;
        }
      }

      // Log data to watchdog around SKUs that were deleted & configurable SKUs
      // that were cleaned up as a result of deleting simple SKUs.
      // @codingStandardsIgnoreStart
      \Drupal::logger('acq_sku')->info(dt('Cleaned up following SKUs without any DescriptiveStillLife image. List of SKUs deleted: @skus', ['@skus' => implode(',', $results['skus_processed'])]));
      \Drupal::logger('acq_sku')->info(dt('Cleaned up configurable SKUs without any children items: @skus', ['@skus' => implode(',', $parent_sku_processed)]));
      // @codingStandardsIgnoreEnd
    }
  }

}
