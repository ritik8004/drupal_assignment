<?php

namespace Drupal\alshaya_acm\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\alshaya_acm\AlshayaAcmConfigCheck;
use Drupal\alshaya_acm_dashboard\AlshayaAcmDashboardManager;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Site\Settings;
use Drush\Commands\DrushCommands;
use Drush\Drush;
use Drush\Exceptions\UserAbortException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Class AlshayaAcmCommands.
 *
 * @package Drupal\alshaya_acm\Commands
 */
class AlshayaAcmCommands extends DrushCommands {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * Langauge Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  private $entityManager;

  /**
   * Product category tree manager.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  private $productCategoryTree;

  /**
   * Alshaya config check service.
   *
   * @var \Drupal\alshaya_acm\AlshayaAcmConfigCheck
   */
  private $alshayaAcmConfigCheck;

  /**
   * Database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * Conductor API wrapper service.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  private $apiWrapper;

  /**
   * Alshaya acm dashboard manager.
   *
   * @var \Drupal\alshaya_acm_dashboard\AlshayaAcmDashboardManager
   */
  private $acmDashboardManager;

  /**
   * AlshayaAcmCommands constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   Entity manager.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $productCategoryTree
   *   Product category tree manager.
   * @param \Drupal\alshaya_acm\AlshayaAcmConfigCheck $alshayaAcmConfigCheck
   *   Alshaya config check service.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection service.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   Commerce API wrapper.
   * @param \Drupal\alshaya_acm_dashboard\AlshayaAcmDashboardManager $acm_dashboard_manager
   *   Alshaya Dashboard manager.
   */
  public function __construct(ConfigFactoryInterface $configFactory,
                              LanguageManagerInterface $languageManager,
                              EntityTypeManagerInterface $entityTypeManager,
                              EntityManagerInterface $entityManager,
                              ProductCategoryTree $productCategoryTree,
                              AlshayaAcmConfigCheck $alshayaAcmConfigCheck,
                              Connection $connection,
                              APIWrapper $api_wrapper,
                              AlshayaAcmDashboardManager $acm_dashboard_manager) {
    $this->configFactory = $configFactory;
    $this->languageManager = $languageManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityManager = $entityManager;
    $this->productCategoryTree = $productCategoryTree;
    $this->alshayaAcmConfigCheck = $alshayaAcmConfigCheck;
    $this->connection = $connection;
    $this->apiWrapper = $api_wrapper;
    $this->acmDashboardManager = $acm_dashboard_manager;
  }

  /**
   * Switch some config to connect Drupal to specific ACM and MDC.
   *
   * @param array $options
   *   Options supported with drush command.
   *
   * @command alshaya_acm:switch-config
   *
   * @option acm The ACM key to connect Drupal to (ex: mcsa_dev, hmkw_qa, hmsa_uat, ...).
   * @option mdc The MDC key to connect Drupal to (ex: mc_dev, hm_qa, vs_uat, ...).
   * @option country_code The country code to be used with MDC communication (store_ids, ...) - Default is current site country code.
   *
   * @aliases asc,alshaya-switch-config
   *
   * @usage drush asc --acm=mcsa_dev --mdc=mc_dev --country_code=sa
   *   Connect Drupal to mcsa_dev ACM and mc_dev MDC using the SA store ids.
   * @usage drush asc --acm=hmkw_qa
   *   Connect Drupal to hmkw_qa ACM.
   */
  public function switchConfig(
    array $options = ['acm' => '', 'mdc' => '', 'country_code' => '']
  ) {
    $acm = $options['acm'];
    $mdc = $options['mdc'];

    if (empty($acm) && empty($mdc)) {
      $this->output->writeln(dt('Please provide an ACM or a MDC key to switch the config.'));
      return;
    }

    // Update the conductor config.
    if (!empty($acm)) {
      include_once DRUPAL_ROOT . '/../factory-hooks/environments/conductor.php';
      // @codingStandardsIgnoreLine
      global $conductors;

      if (isset($conductors[$acm])) {
        $config = $this->configFactory->getEditable('acq_commerce.conductor');

        foreach ($conductors[$acm] as $key => $value) {
          $config->set($key, $value);

          $this->output->writeln(dt('Configuring acq_commerce.conductor.@key to @value.', [
            '@key' => $key,
            '@value' => $value,
          ]));
        }

        $config->save();
      }
      else {
        $this->output->writeln(dt('Unknown ACM "@acm".', ['@acm' => $acm]));
      }
    }

    // Update the magento config.
    if (!empty($mdc)) {
      include_once DRUPAL_ROOT . '/../factory-hooks/environments/magento.php';
      // @codingStandardsIgnoreLine
      global $magentos;

      if (isset($magentos[$mdc])) {
        // Update the magento host.
        $config = $this->configFactory->getEditable('alshaya_api.settings');
        $config->set('magento_host', $magentos[$mdc]['url']);
        foreach ($magentos[$mdc]['magento_secrets'] ?? [] as $key => $value) {
          $config->set($key, $value);
        }

        $config->save();

        $this->output->writeln(dt('Configuring alshaya_api.settings.magento_host to @value.', [
          '@value' => $magentos[$mdc]['url'],
        ]));

        // Determine the langcode to use.
        $country_code = !empty($options['country_code']) ?: Unicode::strtolower(Settings::get('country_code'));

        if (!isset($magentos[$mdc][$country_code])) {
          $this->output->writeln(dt('Unknown "@country_code" country code for "@mdc" MDC. Using the current site\'s country code "@current_country_code".', [
            '@country_code' => $country_code,
            '@mdc' => $mdc,
            '@current_country_code' => $country_code = Unicode::strtolower(Settings::get('country_code')),
          ]));
        }

        $configs = [
          'acq_commerce.store' => 'store_id',
          'alshaya_api.settings' => 'magento_lang_prefix',
        ];

        // Update the magento store ids and lang_prefix.
        foreach ($configs as $name => $key) {
          foreach ($this->languageManager->getLanguages() as $lang => $language) {
            if ($lang == $this->languageManager->getDefaultLanguage()->getId()) {
              $config = $this->configFactory->getEditable($name);
            }
            else {
              $config = $this->languageManager->getLanguageConfigOverride($lang, $name);
            }

            // Use specific config if it exists, use default one otherwise.
            $value = isset($magentos[$mdc][$country_code][$key][$lang]) ? $magentos[$mdc][$country_code][$key][$lang] : $magentos['default'][$country_code][$key][$lang];
            $config->set($key, $value)->save();

            $this->output->writeln(dt('Configuring @name.@key to @value.', [
              '@name' => $name,
              '@key' => $key . ' ' . $lang,
              '@value' => $value,
            ]));
          }
        }
      }
      else {
        $this->output->writeln(dt('Unknown MDC "@mdc".', ['@mdc' => $mdc]));
      }
    }
  }

  /**
   * Imports commerce products from the local files.
   *
   * @param array $options
   *   List of options supported with the command.
   *
   * @command alshaya_acm:offline-product-sync
   *
   * @option limit Number of products to sync.
   * @option skus SKUs to import (like query).
   *
   * @aliases aaops,alshaya-acm-offline-products-sync
   */
  public function syncProductsOffline(array $options = ['limit' => 0, 'skus' => '']) {
    $brand_module = $this->configFactory->get('alshaya.installed_brand')->get('module');

    // Get country code.
    $country_code = Unicode::strtolower(Settings::get('country_code'));

    // Check if the site is configured for a specific brand.
    if (empty($brand_module)) {
      $this->output->writeln(dt('The site is not configured for a specific brand yet.'));
      return;
    }

    $path = drupal_get_path('module', $brand_module);

    // Check if the directory containing the data files exists.
    if (!file_exists($path . '/data/')) {
      $this->output->writeln(dt('The directory @directory does not exist. Please create and upload appropriate data files.', ['@directory' => $path . '/data/']));
      return;
    }

    // Get the REST endpoint information to POST products.
    $resource_storage = $this->entityTypeManager->getStorage('rest_resource_config');
    $resource_config = $resource_storage->load('acq_productsync');
    $resource = $resource_config->getResourcePlugin();

    // Get the options from the drush command.
    $query = $options['skus'];
    $limit = (int) $options['limit'];

    global $_alshaya_acm_products;
    foreach ($this->languageManager->getLanguages() as $langcode => $language) {
      // We check if the data file exists for this language.
      if (!file_exists($path . '/data/products_' . $country_code . '_' . $langcode . '.data')) {
        continue;
      }

      module_load_include('data', $brand_module, 'data/products_' . $country_code . '_' . $langcode);

      // Check if we need to import only specific SKUs.
      if ($query) {
        $_alshaya_acm_products = array_filter($_alshaya_acm_products, function ($sku) use ($query) {
          return strpos($sku['sku'], $query) !== FALSE;
        });
      }

      // Check if we need to import a limited set of products.
      if ($limit) {
        $_alshaya_acm_products = array_slice($_alshaya_acm_products, 0, $limit);
      }

      if (empty($_alshaya_acm_products)) {
        $this->output->writeln(dt('No @language products in @filename', ['@language' => strtolower($language->getName()), '@filename' => $path . '/data/products_' . $country_code . '_' . $langcode . '.data']));
        continue;
      }

      // Prepare chunks of products to avoid memory issue.
      $product_chunks = array_chunk($_alshaya_acm_products, 250, TRUE);

      // Save memory by unsetting global var.
      $_alshaya_acm_products = [];

      foreach ($product_chunks as $i => $products) {
        $this->output->writeln(dt('Synchronizing chunk @i of @total of @language products from @filename',
          [
            '@i' => $i + 1,
            '@total' => count($product_chunks),
            '@language' => strtolower($language->getName()),
            '@filename' => $path . '/data/products_' . $country_code . '_' . $langcode . '.data',
          ]
        ));

        // Reset static caches to release memory.
        drupal_static_reset();

        // Entity storage can blow up with caches so clear them out.
        foreach ($this->entityManager->getDefinitions() as $id => $definition) {
          $this->entityManager->getStorage($id)->resetCache();
        }

        // Process chunk.
        $resource->post($products);
      }
    }

    $this->output->writeln(dt('Done.'));
  }

  /**
   * Print the category menu true for specified language.
   *
   * @param string $langcode
   *   Language code for which we want to get category tree.
   *
   * @command alshaya_acm:print-category-menu-tree
   *
   * @aliases aapcmt,alshaya-acm-print-category-menu-tree
   *
   * @usage drush aapcmt en
   *   Print the category menu true for en language.
   */
  public function printCategoryTreeMenu($langcode) {
    $tree = $this->productCategoryTree->getCategoryTree($langcode);

    foreach ($tree as $level1) {
      $this->output->writeln($level1['label']);

      foreach ($level1['child'] as $level2) {
        $this->output->writeln('--' . $level2['label']);

        foreach ($level2['child'] as $level3) {
          $this->output->writeln('----' . $level3['label']);
        }
      }
    }
  }

  /**
   * Reset all config from settings.
   *
   * @command alshaya_acm:reset-config
   *
   * @aliases arc,alshaya-reset-config
   */
  public function resetConfig() {
    // Force reset all the settings.
    $this->alshayaAcmConfigCheck->checkConfig(TRUE);

    // Reset country specific settings.
    $this->alshayaAcmConfigCheck->resetCountrySpecificSettings();
  }

  /**
   * Print skus for which media is attached to SKU but not available in drupal.
   *
   * @param string $langcode
   *   Language code for which we want to get skus.
   *
   * @command alshaya_acm:media-missing
   *
   * @aliases alshaya-sku-media-missing
   *
   * @usage drush alshaya-sku-media-missing en
   *   Print the skus for en language.
   *
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   *   List of Skus missing media.
   */
  public function printMissingMediaSkus($langcode = 'en') {
    $this->output->writeln(dt('Checking skus for the langcode @langcode', ['@langcode' => $langcode]));

    $query = $this->connection->select('acq_sku_field_data', 'acq');
    $query->fields('acq', ['sku', 'media__value']);
    $query->condition('acq.media__value', 'a:0:{}', '<>');
    $query->condition('acq.langcode', $langcode);
    $result = $query->execute()->fetchAll();

    $fids = [];
    $missing_fids = [];

    // If there are any results.
    if ($result) {

      // Prepare fid array.
      foreach ($result as $key => $rs) {
        $media_data = unserialize($rs->media__value);
        foreach ($media_data as $data) {
          if (isset($data['fid'])) {
            $fids[$data['fid']] = $rs->sku;
          }
        }
      }

      // If there are ids.
      if (!empty($fids)) {
        $query = $this->connection->select('file_managed', 'fm');
        $query->fields('fm', ['fid']);
        $query->condition('fm.fid', array_keys($fids), 'IN');
        $existing_fids = $query->execute()->fetchCol();
        $missing_fids = array_diff(array_keys($fids), $existing_fids);
      }
    }

    // If there are no skus.
    if (empty($missing_fids)) {
      $this->output->writeln(dt('There are no skus.'));
    }
    else {
      $rows = [];
      // Prepare the row.
      foreach ($missing_fids as $key => $value) {
        $rows[$key] = [
          'sku' => $fids[$value],
          'fid' => $value,
        ];
      }

      array_unshift($rows, ['SKU', 'File ID']);
      return new RowsOfFields($rows);
    }
  }

  /**
   * Product sync if confirmed based on queue status.
   *
   * @throws \Drush\Exceptions\UserAbortException
   *
   * @command alshaya_acm:sync-products
   *
   * @param string $langcode
   *   Sync products available in this langcode.
   * @param string $page_size
   *   Number of items to be synced in one batch.
   *
   * @param array $options
   *
   * @option skus SKUs to import (like query).
   * @option category_id Magento category id to sync the products for.
   *
   * @validate-module-enabled acq_sku
   *
   * @aliases aasp,alshaya-sync-commerce-products
   *
   * @usage drush acsp en 50
   *   Run a full product synchronization of all available products in store linked to en and page size 50.
   * @usage drush acsp en 50 --skus=\'M-H3495 130 2  FW\',\'M-H3496 130 004FW\',\'M-H3496 130 005FW\''
   *   Synchronize sku data for the skus M-H3495 130 2  FW, M-H3496 130 004FW & M-H3496 130 005FW only in store linked to en and page size 50.
   * @usage drush acsp en 50 --category_id=1234
   *   Synchronize sku data for the skus in category with id 1234 only in store linked to en and page size 50.
   */
  public function syncProducts($langcode, $page_size, $options = ['skus' => NULL, 'category_id' => NULL, 'csv_path' => NULL, 'batch_size' => 500]) {
    $acm_queue_count = $this->apiWrapper->getQueueStatus();
    $mdc_queue_stats = json_decode($this->acmDashboardManager->getMdcQueueStats('connectorProductPushQueue'));
    $mdc_queue_count = $mdc_queue_stats->messages;

    if (($acm_queue_count > 0) || ($mdc_queue_count > 0)) {
      drush_print('Items in MDC Queue: ' . $mdc_queue_count);
      drush_print('Items in ACM Queue: ' . $acm_queue_count);
      if (!$this->io()->confirm('There are items in MDC/ ACM queues awaiting sync. Do you still want to continue with sync operation?')) {
        throw new UserAbortException();
      }
    }

    $command_options = Drush::redispatchOptions();

    if (!empty($options['csv_path'])) {
      if (($handle = fopen($options['csv_path'], 'r')) === FALSE) {
        print "File not readable or not available at the specified path.";
        throw new FileNotFoundException();
      }

      $i = 0;
      $j = 0;
      $csv_skus = [];

      while (($data = fgetcsv($handle, $options['batch_size'], ",")) !== FALSE) {
        $csv_skus[$j][] = $data[0];
        if ($i++ % $options['batch_size'] == 0) {
          $j++;
        }
      }

      // Remove additinoal options which are not understood by the actual import
      // command.
      unset($command_options['csv_path']);
      unset($command_options['batch_size']);
    }

    if (!empty($csv_skus)) {
      // Override skus option with the list retrieved from csv file. The command
      // would give preference to list of SKUs supplied by csv file.
      unset($command_options['skus']);
      foreach ($csv_skus as $csv_skus_chunk) {
        $command_options['skus'] = implode(',', $csv_skus_chunk);
        drush_invoke_process('@self', 'acsp', ['langcode' => $langcode, 'page_size' => $page_size], $command_options);
      }
    }
    else {
      drush_invoke_process('@self', 'acsp', ['langcode' => $langcode, 'page_size' => $page_size], $command_options);
    }
  }

}
