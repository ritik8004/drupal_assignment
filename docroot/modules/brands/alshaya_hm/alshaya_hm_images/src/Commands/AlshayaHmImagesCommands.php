<?php

namespace Drupal\alshaya_hm_images\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Drush\Commands\DrushCommands;

/**
 * Class AlshayaHmImagesCommands.
 *
 * @package Drupal\alshaya_hm_images\Commands
 */
class AlshayaHmImagesCommands extends DrushCommands {

  /**
   *
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * AlshayaHmImagesCommands constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config Factory.
   */
  public function __construct(Connection $connection,
                              ConfigFactoryInterface $configFactory) {
    $this->connection = $connection;
    $this->configFactory = $configFactory;
  }

  /**
   * Command to get faulty images of H&M.
   *
   * @param array $options
   *
   * @command alshaya_hm_images:generate-image-report
   *
   * @option check_faults List only gray images
   *
   * @aliases hmir,alshaya-hm-images-report
   */
  public function generateImageReport(array $options = ['check_faults' => FALSE]) {
    // Set memory limit to -1 while processing faulty images.
    ini_set('memory_limit', -1);

    // product/listing without dam
    //  $faulty_image = 'eefda5660843594e82bf807be8edbe69';
    // product/miniature without dam
    //  $faulty_image = 'da83c2c002729efa800f77b2f22bf20a';
    // product/miniature with dam.
    $faulty_image = 'c6bcf0e84423a68fa8d32f14bfc2dc80';
    $faulty_size = 351;

    $checked_assets = [];

    $select = $this->connection->select('acq_sku_field_data');
    $select->fields('acq_sku_field_data', ['id', 'langcode', 'sku']);
    $select->addField('acq_sku_field_data', 'attr_assets__value', 'assets');
    $select->condition('langcode', 'en');
    $select->condition('attr_assets__value', 'a:0:{}', '<>');
    $select->orderBy('attr_assets__value', 'DESC');
    $skus = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);

    $alshaya_hm_images_settings = $this->configFactory->get('alshaya_hm_images.settings');
    $base_url = $alshaya_hm_images_settings->get('base_url');
    $line = 0;

    $check_faults = $options['check_faults'];

    $filename = '/tmp/alshaya-hm-images_';
    if ($check_faults) {
      $filename .= 'faults_';
    }
    $filename .= $GLOBALS['site_name'] . '.log';

    $fp = fopen($filename, 'w');

    foreach ($skus as $index => $sku) {
      if ($index > 0 && $index % 10 == 0) {
        echo date('H:i:s') . "- Processing " . $index . "/" . count($skus) . "\n";
      }

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

        if ($check_faults) {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $file_path);
          curl_setopt($ch, CURLOPT_NOBODY, TRUE);
          curl_exec($ch);
          $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
          curl_close($ch);

          // $output = md5_file($file_path);
          //        if ($output == $faulty_image) {.
          if ($size == $faulty_size) {
            fwrite($fp, $line++ . ' - Sku ' . $sku['id'] . ' / ' . $sku['sku'] . ' has invalid asset ' . $asset['Data']['AssetId'] . ' (' . urldecode($file_path) . ")\n");
          }
        }
        else {
          fwrite($fp, $sku['sku'] . ';' . $asset['Data']['AssetId'] . ';' . urldecode($file_path) . "\n");
        }
      }
    }

    fclose($fp);
  }

}
