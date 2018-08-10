<?php

/**
 * @file
 * Provides drush commands for the products of Acquia Commerce connector.
 */

use Drupal\Core\Cache\Cache;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\rest\resource\ProductSyncResource;
use Drupal\Core\Url;

const DELETE_BATCH_COUNT = 200;

/**
 * Implements hook_drush_command().
 */
function alshaya_hm_images_drush_command() {
  $commands = [];

  $commands['alshaya-hm-faulty-images'] = [
    'description' => 'Run a full synchronization of all commerce product options.',
    'aliases'     => ['hmfaulty'],
    'examples'    => [
      'drush acsp' => 'Run a full product synchronization of all available product options.',
    ],
  ];

  return $commands;
}


function drush_alshaya_hm_images_alshaya_hm_faulty_images() {
  $faulty_image = 'eefda5660843594e82bf807be8edbe69';


  $select = \Drupal::database()->select('acq_sku_field_data');
  $select->fields('acq_sku_field_data', ['id', 'langcode', 'sku', 'attr_assets__value']);
  $select->addField('acq_sku_field_data', 'attr_assets__value', 'assets');
  $select->condition('langcode', 'en');
  $select->condition('attr_assets__value', 'a:0:{}', '<>');
  $select->orderBy('attr_assets__value', 'DESC');
  $skus = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);

  $base_url = \Drupal::config('alshaya_hm_images.settings')->get('base_url');
  $fault_asset = [];
  foreach($skus as $sku) {
    $assets = unserialize($sku['assets']);
    foreach ($assets as $key => $asset) {
      if ((isset($asset['is_old_format']) && $asset['is_old_format']) || empty($asset['Data']['FilePath'])) {
        continue;
      }

      $set = [
        'source' => "source[/" . $asset['Data']['FilePath'] . "]",
      ];

      $options = [
        'query' => [
          'set' => implode(',', $set),
          'call' => 'url[file:/product/listing]',
        ],
      ];

      $file_path = Url::fromUri($base_url, $options)->toString();
      $output = md5_file($file_path);
      if ($output == $faulty_image) {
        $fault_asset[] = [
          'id' => $sku['id'],
          'sku' => $sku['sku'],
          'AssetId' => $asset['Data']['AssetId'],
          'file_path' => urldecode($file_path),
        ];
      }
    }
  }

  print_r($fault_asset);
}
