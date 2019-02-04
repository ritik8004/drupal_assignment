<?php

namespace Drupal\alshaya_acm\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm\AlshayaAcmConfigCheck;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Site\Settings;
use Drupal\node\NodeInterface;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;
use Consolidation\AnnotatedCommand\CommandData;

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
   * Sku manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

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
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku manager service.
   */
  public function __construct(ConfigFactoryInterface $configFactory,
                              LanguageManagerInterface $languageManager,
                              EntityTypeManagerInterface $entityTypeManager,
                              EntityManagerInterface $entityManager,
                              ProductCategoryTree $productCategoryTree,
                              AlshayaAcmConfigCheck $alshayaAcmConfigCheck,
                              Connection $connection,
                              SkuManager $skuManager) {
    $this->configFactory = $configFactory;
    $this->languageManager = $languageManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityManager = $entityManager;
    $this->productCategoryTree = $productCategoryTree;
    $this->alshayaAcmConfigCheck = $alshayaAcmConfigCheck;
    $this->connection = $connection;
    $this->skuManager = $skuManager;
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
   * Reset the specified config from settings files.
   *
   * @param string $config
   *   Config that needs to reset.
   *
   * @command alshaya_acm:reset-config
   *
   * @aliases arc,alshaya-reset-config
   *
   * @usage drush alshaya-reset-config acq_commerce.conductor
   *   Resets the conductor config.
   */
  public function resetConfig(string $config = '') {
    // Force reset all the settings.
    if (!$this->alshayaAcmConfigCheck->checkConfig(TRUE, $config)) {
      $this->io()->error(dt('Config reset is not done.'));
    }
    else {
      $this->io()->success(dt('Config reset done successfully.'));
    }

    // Reset country specific settings.
    $this->alshayaAcmConfigCheck->resetCountrySpecificSettings();
  }

  /**
   * Check config state as a part of post-command to reset.
   *
   * Added (*) to execute after each drush command.
   *
   * @hook post-command *
   */
  public function resetConfigPostCommand($result, CommandData $commandData) {
    $this->alshayaAcmConfigCheck->checkConfig();
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
   * Cleanup Configurable SKUs without any child SKU.
   *
   * @command alshaya_acm:cleanup-orphan-skus
   *
   * @aliases alshaya-cleanup-orphan-skus, acos
   *
   * @usage drush alshaya-cleanup-orphan-skus
   *   Cleanup Configurable SKUs without any child SKU.
   */
  public function cleanupOrphanSkus() {
    $subquery = $this->connection->select('acq_sku__field_configured_skus', 'c')
      ->fields('c', ['entity_id']);
    $subquery->join('acq_sku_field_data', 'd', 'c.field_configured_skus_value = d.sku');
    $subquery->condition('c.bundle', "configurable");

    $query = $query = $this->connection->select('acq_sku__field_configured_skus', 's')
      ->fields('b', ['sku']);
    $query->leftJoin('acq_sku_field_data', 'b', 's.entity_id = b.id');
    $query->condition('s.entity_id', $subquery, 'NOT IN');
    $query->join('node__field_skus', 'nfs', 'nfs.field_skus_value = b.sku');
    $query->join('node_field_data', 'nfd', 'nfd.nid = nfs.entity_id');
    $query->condition('nfd.status', 1);
    $query->distinct();

    $results = $query->execute()->fetchAllKeyed(0, 0);

    if (empty($results)) {
      $this->output()->writeln('SKUs are already in a clean state. No configurable SKUs found without children.');
      return;
    }

    $confirm_message = dt('You are going to disable the following SKUs from Drupal: !skus', ['!skus' => implode(',', $results)]);
    if (!$this->io()->confirm($confirm_message)) {
      throw new UserAbortException();
    }

    foreach ($results as $result) {
      if (($sku = SKU::loadFromSku($result)) && $sku instanceof SKU) {
        // Get parent node for SKU.
        $parent_node = $this->skuManager->getDisplayNode($sku, FALSE);

        // Unpublish the node rather than deleting it. We may run into orphan
        // simple SKUs, if a simple SKU connected with a config is enabled on
        // MDC (which has been cleaned from Drupal), unless we save the config
        // SKU again on MDC.
        if ($parent_node instanceof NodeInterface) {
          $parent_node->setPublished(FALSE);
          $parent_node->save();

          // Get translation languages for the parent node.
          $node_trans_languages = $parent_node->getTranslationLanguages();
          $current_langauge = $parent_node->language();

          // Disable translations as well.
          foreach ($node_trans_languages as $language) {
            if ($current_langauge->getId() !== $language->getId() &&
              $parent_node->hasTranslation($language->getId()) &&
              $translated_node = $parent_node->getTranslation($language->getId())) {
              $translated_node->setPublished(FALSE);
              $translated_node->save();
            }
          }

          $deleted_skus[] = $result;
        }
      }
    }

    if (!empty($deleted_skus)) {
      $this->logger->info('Cleaned up following SKUs which had no children attached: @cleaned_skus', ['@clenaed_skus' => implode(',', $deleted_skus)]);
      $this->io()->success('Cleaned up following SKUs which had no children attached: ' . implode(',', $deleted_skus));
    }
  }

}
