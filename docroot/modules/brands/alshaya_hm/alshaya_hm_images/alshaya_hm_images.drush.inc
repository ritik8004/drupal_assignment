<?php

/**
 * @file
 * Provides drush commands for the H&M images module.
 */

use Drupal\Core\Url;

/**
 * Implements hook_drush_command().
 */
function alshaya_hm_images_drush_command() {
  $commands = [];

  $commands['alshaya-hm-faulty-images'] = [
    'description' => 'Command to get faulty images of H&M.',
    'aliases'     => ['hm-faulty-images'],
    'arguments' => [
      'start' => 'Starting limit.',
      'length' => 'total number of records to execute.',
    ],
    'examples'    => [
      'drush hm-faulty-images' => 'Command to get faulty images of H&M.',
      'drush hm-faulty-images 0 100' => 'Print my example command with an argument "myargument".',
    ],
  ];

  return $commands;
}


function drush_alshaya_hm_images_alshaya_hm_faulty_images($start = 0, $length = 1000) {
  $faulty_image = 'c6bcf0e84423a68fa8d32f14bfc2dc80';

  $select = \Drupal::database()->select('acq_sku_field_data');
  $select->fields('acq_sku_field_data', ['id', 'langcode', 'sku']);
  $select->addField('acq_sku_field_data', 'attr_assets__value', 'assets');
  $select->condition('langcode', 'en');
  $select->condition('attr_assets__value', 'a:0:{}', '<>');
  $select->range($start, $length);
  $select->orderBy('id', 'DESC');
  $skus = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);

  $alshaya_hm_images_settings = \Drupal::config('alshaya_hm_images.settings');
  $base_url = $alshaya_hm_images_settings->get('base_url');
  $line = 0;

  $checked_assets = [];
  foreach ($skus as $index => $sku) {
    $assets = unserialize($sku['assets']);

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
          'call' => 'url[file:/product/miniature]',
        ],
      ];

      $file_path = Url::fromUri($base_url, $options)->toString();
      $output = md5_file($file_path);
      if ($output == $faulty_image) {
        echo $line++ . " sku {$sku['id']} / {$sku['sku']} has invalid image for {$asset['Data']['AssetId']} ({$file_path}) \n";
      }
    }
  }

 // print_r($faulty_assets);
}
