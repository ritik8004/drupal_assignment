<?php

/**
 * @file
 * Provides drush commands for the products of Acquia Commerce connector.
 */

use Drupal\Core\Url;

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
  $faulty_image = 'c6bcf0e84423a68fa8d32f14bfc2dc80';

  $select = \Drupal::database()->select('acq_sku_field_data');
  $select->fields('acq_sku_field_data', ['id', 'langcode', 'sku', 'attr_assets__value']);
  $select->addField('acq_sku_field_data', 'attr_assets__value', 'assets');
  $select->condition('langcode', 'en');
  $select->condition('attr_assets__value', 'a:0:{}', '<>');
  $select->orderBy('id', 'DESC');
  $select->range(0, 1000);
  $skus = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);

  $alshaya_hm_images_settings = \Drupal::config('alshaya_hm_images.settings');
  $base_url = $alshaya_hm_images_settings->get('base_url');
  $faulty_assets = $checked_assets = [];

  foreach ($skus as $index => $sku) {
    $assets = unserialize($sku['assets']);

    if ($index > 0 && $index%10 == 0) {
      echo date('H:i:s') . "- Processing " . $index . "/" . count($skus) . "\n";
    }

    foreach ($assets as $key => $asset) {
      if ((isset($asset['is_old_format']) && $asset['is_old_format']) || empty($asset['Data']['FilePath'])) {
        continue;
      }

      if (array_key_exists($asset['Data']['AssetId'], $checked_assets)) {
        $checked_assets[$asset['Data']['AssetId']]++;
        continue;
      }

      $checked_assets[$asset['Data']['AssetId']] = 1;

      $set = [
        'source' => "source[/" . $asset['Data']['FilePath'] . "]",
        'origin' => "origin[" . $alshaya_hm_images_settings->get('origin') . "]",
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
        $faulty_assets[] = [
          'id' => $sku['id'],
          'sku' => $sku['sku'],
          'AssetId' => $asset['Data']['AssetId'],
          'file_path' => urldecode($file_path),
        ];
      }
    }
  }

  print_r($faulty_assets);
}
