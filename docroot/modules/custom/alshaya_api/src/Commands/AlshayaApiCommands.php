<?php

namespace Drupal\alshaya_api\Commands;

use Drupal\acq_commerce\Conductor\IngestAPIWrapper;
use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Class AlshayaApiCommands.
 *
 * @package Drupal\alshaya_api\Commands
 */
class AlshayaApiCommands extends DrushCommands {

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * Alshaya Api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  private $alshayaApiWrapper;

  /**
   * Sku manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * I18n Helper service.
   *
   * @var \Drupal\acq_commerce\InHelper
   */
  private $i18nHelper;

  /**
   * Conductor Api wrapper.
   *
   * @var \Drupal\acq_commerce\Conductor\IngestAPIWrapper
   */
  private $ingestApiWrapper;

  /**
   * Database connection object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Database lock service.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  private $lock;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * AlshayaApiCommands constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language Manager.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshayaApiWrapper
   *   Alshaya Api Wrapper.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku Manager service.
   * @param \Drupal\acq_commerce\I18nHelper $i18nHelper
   *   i18n Helper service.
   * @param \Drupal\acq_commerce\Conductor\IngestAPIWrapper $ingestAPIWrapper
   *   Conductor API wrapper.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger Factory.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   Database lock service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   */
  public function __construct(LanguageManagerInterface $languageManager,
                              AlshayaApiWrapper $alshayaApiWrapper,
                              SkuManager $skuManager,
                              I18nHelper $i18nHelper,
                              IngestAPIWrapper $ingestAPIWrapper,
                              LoggerChannelFactoryInterface $loggerChannelFactory,
                              Connection $connection,
                              EntityTypeManagerInterface $entityTypeManager,
                              LockBackendInterface $lock,
                              ConfigFactoryInterface $config_factory) {
    $this->languageManager = $languageManager;
    $this->alshayaApiWrapper = $alshayaApiWrapper;
    $this->skuManager = $skuManager;
    $this->i18nHelper = $i18nHelper;
    $this->ingestApiWrapper = $ingestAPIWrapper;
    $this->logger = $loggerChannelFactory->get('alshaya_api');
    $this->connection = $connection;
    $this->entityTypeManager = $entityTypeManager;
    $this->lock = $lock;
    $this->configFactory = $config_factory;
    parent::__construct();
  }

  /**
   * Run sanity check to get diff between Magento and Drupal SKUs data.
   *
   * @param array $options
   *   List of options supported by drush command.
   *
   * @command alshaya_api:sanity-check
   *
   * @option types
   *   The comma-separated list of SKUs types to check (simple, configurable).
   *
   * @aliases aasc,alshaya-api-sanity-check
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function sanityCheck(array $options = [
    'types' => 'configurable,simple',
  ]) {
    $verbose = $options['verbose'];

    $types = array_map('trim', explode(',', $options['types']));

    $languages = $this->languageManager->getLanguages();

    $this->output->writeln(dt("\n#### FETCHING DATA ####"));

    // Retrieve all enabled SKUs from Magento.
    $this->output->writeln(dt('Getting SKUs from Magento, please wait...'));
    $mskus = $this->alshayaApiWrapper->getSkusData();

    foreach ($types as $type) {
      $this->logger()->debug(dt("@type SKUs (@count) from Magento:\n@skus", [
        '@type' => $type,
        '@count' => count($mskus[$type]),
        '@skus' => "'" . implode("','", array_keys($mskus[$type])) . "'",
      ]));
    }

    // Retrieve all enabled SKUs from Drupal indexed by type and langcode.
    $this->output->writeln(dt("\nGetting @types SKUs from Drupal, please wait...", [
      '@types' => implode(dt(' and '), $types),
    ]));

    foreach ($types as $type) {
      foreach ($languages as $language) {
        $dskus[$type][$language->getId()] = $this->skuManager->getSkus($language->getId(), $type);

        $this->logger()->debug(dt("@type @language SKUs (@count) from Drupal:\n@skus", [
          '@type' => $type,
          '@language' => $language->getName(),
          '@count' => count($dskus[$type][$language->getId()]),
          '@skus' => "'" . implode("','", array_keys($dskus[$type][$language->getId()])) . "'",
        ]));
      }
    }

    $this->output->writeln(dt("\n#### REPORT SUMMARY ####"));

    $missing = [];
    $to_be_deleted = [];
    $stock_mismatch_sync = [];
    $price_mismatch_sync = [];
    $cat_mismatch_sync = [];

    $skip_cats = [];
    // MDC categories those needs to be ignored while checking for the category
    // diff between drupal and mdc.
    if (!empty($cats_to_ignore = $this->configFactory->get('alshaya_api.settings')->get('ignored_mdc_cats_on_sanity_check'))) {
      $skip_cats = explode(',', $cats_to_ignore);
    }

    // Informing user in debug mode to setup ignored categories.
    if (empty($skip_cats)) {
      $this->logger()->debug(dt('You can set the category ids which can be ignored in key @key of @config config.', [
        '@key' => 'ignored_mdc_cats_on_sanity_check',
        '@config' => 'alshaya_api.settings',
      ]));
    }
    else {
      $this->logger()->debug(dt('Category IDs:@cat_ids are being ignored while computing MDC and Drupal category diff.', [
        '@cat_ids' => implode(',', $skip_cats),
      ]));
    }

    foreach ($types as $type) {
      $missing[$type]['all'] = [];
      $to_be_deleted[$type]['all'] = [];
      $stock_mismatch[$type] = [];
      $price_mismatch[$type] = [];
      $cat_mismatch[$type] = [];

      foreach ($languages as $language) {
        // The ones which are missing in Drupal.
        $missing[$type][$language->getId()] = array_diff(array_keys($mskus[$type]), array_keys($dskus[$type][$language->getId()]));
        $missing[$type]['all'] = array_merge($missing[$type]['all'], $missing[$type][$language->getId()]);

        // The ones which are only in Drupal and should be removed.
        $to_be_deleted[$type][$language->getId()] = array_diff(array_keys($dskus[$type][$language->getId()]), array_keys($mskus[$type]));
        $to_be_deleted[$type]['all'] = array_merge($to_be_deleted[$type]['all'], $to_be_deleted[$type][$language->getId()]);

        if (!empty($missing[$type][$language->getId()])) {
          $this->output->writeln(dt("\n@count @language @type's SKUs are missing in Drupal and must be synced!skus", [
            '@count' => count($missing[$type][$language->getId()]),
            '@language' => strtolower($language->getName()),
            '@type' => $type,
            '!skus' => $verbose ? ":\n'" . implode("','", $missing[$type][$language->getId()]) . "'" : '.',
          ]));
        }
        else {
          $this->output->writeln(dt("\nNo missing SKUs match for @language @type in Drupal.", [
            '@language' => strtolower($language->getName()),
            '@type' => $type,
          ]));
        }

        if (!empty($to_be_deleted[$type][$language->getId()])) {
          $this->output->writeln(dt("\n@count @language @type's SKUs are only in Drupal and must be removed!skus", [
            '@count' => count($to_be_deleted[$type][$language->getId()]),
            '@language' => strtolower($language->getName()),
            '@type' => $type,
            '!skus' => $verbose ? ":\n'" . implode("','", $to_be_deleted[$type][$language->getId()]) . "'" : '.',
          ]));
        }
        else {
          $this->output->writeln(dt("\nNo additional SKUs for @language @type found in Drupal. Nothing to delete.", [
            '@language' => strtolower($language->getName()),
            '@type' => $type,
          ]));
        }

        // Get the commerce category ids for the skus from drupal.
        $dsku_cats = $this->skuManager->getCategoriesOfSkus($language->getId(), array_keys($dskus[$type][$language->getId()]));
        $dsku_cats_data = [];
        foreach ($dsku_cats as $dsku_cat) {
          $dsku_cats_data[$dsku_cat['field_skus_value']][] = $dsku_cat['field_commerce_id_value'];
        }
        foreach ($dsku_cats_data as $sku => $dsku_cat_data) {
          if (!empty($mskus[$type][$sku])) {
            $cat_diff_output = '';
            // Remove categories from MDC response for which we don't need the
            // diff. Example is like `2` which is `Default` category id of MDC
            // and we don't use/attach `Default` category to the product nodes.
            $mdc_cats = array_diff(explode(',', $mskus[$type][$sku]['category_ids']), $skip_cats);
            if (!empty(array_diff($mdc_cats, $dsku_cat_data))) {
              $cat_diff_output .= 'Drupal Cat:' . implode(',', array_unique($dsku_cat_data)) . ' | ';
              $cat_diff_output .= 'MDC Cat:' . implode(',', $mdc_cats);
              $cat_mismatch_sync[$type][] = $sku;
            }

            // If there any sku having category mismatch.
            if (!empty($cat_diff_output)) {
              $cat_mismatch[$type][$language->getId()][] = "SKU:" . $sku . " | " . $cat_diff_output;
            }
          }
        }

        // Output the details of category mismatch.
        if (!empty($cat_mismatch[$type][$language->getId()])) {
          $this->output->writeln(dt("\n@count @type type SKUs of @langcode in Drupal have different category than Magento!output", [
            '@count' => count($cat_mismatch[$type][$language->getId()]),
            '@type' => $type,
            '@langcode' => $language->getId(),
            '!output' => $verbose ? ":\n" . implode("\n", $cat_mismatch[$type][$language->getId()]) : '.',
          ]));
        }
        else {
          $this->output->writeln(dt("\nNo category mismatch for language @langcode of type @type in Drupal.", [
            '@langcode' => $language->getId(),
            '@type' => $type,
          ]));
        }

      }

      // We check stock and price only for simple SKUs.
      if ($type == 'simple') {
        $checked_skus = [];

        foreach ($languages as $language) {
          foreach ($dskus[$type][$language->getId()] as $sku => $data) {
            // We will check the stock and price for this SKU only if it exists
            // in Magento (otherwise it must be deleted) and if it has not been
            // checked yet in another language.
            if (!empty($mskus[$type][$sku]) && !in_array($sku, $checked_skus)) {
              $checked_skus[] = $sku;

              $stock_output = '';
              $price_output = '';

              // If stock in Drupal does not match with stock in Magento.
              if ($data['quantity'] != (int) $mskus[$type][$sku]['qty']
                || $data['status'] != $mskus[$type][$sku]['stock_status']) {
                $stock_output .= 'Drupal stock:' . $data['quantity'] . ' | ';
                $stock_output .= 'Drupal stock status:' . $data['status'] . ' | ';
                $stock_output .= 'MDC stock:' . (int) $mskus[$type][$sku]['qty'] . ' | ';
                $stock_output .= 'MDC stock status:' . $mskus[$type][$sku]['stock_status'];
                $stock_mismatch_sync[$type][] = $sku;
              }

              // If price in Drupal not matches with price in Magento.
              if ($data['price'] != $mskus[$type][$sku]['price']) {
                $price_output .= 'Drupal price:' . $data['price'] . ' | ';
                $price_output .= 'MDC price:' . $mskus[$type][$sku]['price'];
                $price_mismatch_sync[$type][] = $sku;
              }

              // If special price in Drupal not matches with final price
              // in Magento.
              if ($data['special_price'] != $mskus[$type][$sku]['final_price']) {
                $price_output .= 'Drupal spl price:' . $data['special_price'] . ' | ';
                $price_output .= 'MDC spl price:' . $mskus[$type][$sku]['final_price'];
                $price_mismatch_sync[$type][] = $sku;
              }

              // If there any sku having stock mismatch.
              if (!empty($stock_output)) {
                $stock_mismatch[$type][$sku] = "SKU:" . $sku . " | " . $stock_output;
              }

              // If there any sku having price mismatch.
              if (!empty($price_output)) {
                $price_mismatch[$type][$sku] = "SKU:" . $sku . " | " . $price_output;
              }
            }
          }
        }

        // Output the details of stock mismatch.
        if (!empty($stock_mismatch[$type])) {
          $this->output->writeln(dt("\n@count @type's SKUs in Drupal have different stock than Magento!output", [
            '@count' => count($stock_mismatch[$type]),
            '@type' => $type,
            '!output' => $verbose ? ":\n" . implode("\n", $stock_mismatch[$type]) : '.',
          ]));
        }
        else {
          $this->output->writeln(dt("\nNo stock mismatch for @type's in Drupal.", [
            '@type' => $type,
          ]));
        }

        // Output the details of price mismatch.
        if (!empty($price_mismatch[$type])) {
          $this->output->writeln(dt("\n@count @type's SKUs in Drupal have different price than Magento!output", [
            '@count' => count($price_mismatch[$type]),
            '@type' => $type,
            '!output' => $verbose ? ":\n" . implode("\n", $price_mismatch[$type]) : '.',
          ]));
        }
        else {
          $this->output->writeln(dt("\nNo price mismatch for @type's in Drupal.", [
            '@type' => $type,
          ]));
        }
      }

      $missing[$type]['all'] = array_unique($missing[$type]['all']);
      $to_be_deleted[$type]['all'] = array_unique($to_be_deleted[$type]['all']);
      $stock_mismatch_sync[$type] = !empty($stock_mismatch_sync[$type]) ? array_unique($stock_mismatch_sync[$type]) : [];
      $price_mismatch_sync[$type] = !empty($price_mismatch_sync[$type]) ? array_unique($price_mismatch_sync[$type]) : [];
      $cat_mismatch_sync[$type] = !empty($cat_mismatch_sync[$type]) ? array_unique($cat_mismatch_sync[$type]) : [];
    }

    $this->output->writeln(dt("\n#### SYNCHRONIZATION ####"));

    $chunk_size = 100;
    $page_size = 10;

    foreach ($types as $type) {
      // Missing SKUs synchronization.
      if (!empty($missing[$type]['all']) && $this->io()->confirm(dt('Do you want to sync the @count @type missing SKUs?', [
        '@count' => count($missing[$type]['all']),
        '@type' => $type,
      ]))) {
        foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
          foreach (array_chunk(str_replace("'", '', $missing[$type]['all']), $chunk_size) as $chunk) {
            $this->ingestApiWrapper->productFullSync($store_id, $langcode, implode(',', $chunk), NULL, $page_size);
          }

          $this->output->writeln(dt('Sync launched for the @count @language @type missing SKUs.', [
            '@count' => count($missing[$type]['all']),
            '@language' => $languages[$langcode]->getName(),
            '@type' => $type,
          ]));
        }
      }

      // Additional SKUs synchronization/deletion.
      if (!empty($to_be_deleted[$type]['all'])) {
        $this->output->writeln(dt('Extra SKUs can be deleted in Drupal directly or can be synchronized with Magento to trigger the deletion.'));
        if ($this->io()
          ->confirm(dt('Do you want to delete the @count @type extra SKUs in Drupal directly?', [
            '@count' => count($to_be_deleted[$type]['all']),
            '@type' => $type,
          ]))) {
          foreach ($languages as $langcode => $language) {
            foreach ($to_be_deleted[$type][$langcode] as $sku) {
              if ($sku_entity = SKU::loadFromSku($sku, $langcode, FALSE, TRUE)) {
                $this->logger->notice('Removing disabled @language @type SKU @sku from the system: @sku.', [
                  '@language' => $languages[$langcode]->getName(),
                  '@type' => $type,
                  '@sku' => $sku,
                ]);

                $lock_key = 'deleteProduct' . $sku;

                // Acquire lock to ensure parallel processes are executed
                // sequentially.
                // @TODO: These 8 lines might be duplicated in multiple places. We
                // may want to create a utility service in alshaya_performance.
                do {
                  $lock_acquired = $this->lock->acquire($lock_key);

                  // Sleep for half a second before trying again.
                  // @TODO: Move this 0.5s to a config variable.
                  if (!$lock_acquired) {
                    usleep(500000);
                  }
                } while (!$lock_acquired);

                // Delete the node if it is linked to this SKU only.
                try {
                  if ($node = $sku_entity->getPluginInstance()->getDisplayNode($sku_entity, FALSE, FALSE)) {
                    $node->delete();
                  }
                }
                catch (\Exception $e) {
                  // Not doing anything, we might not have node for the sku.
                }

                // Delete the SKU.
                $sku_entity->delete();

                // Release the lock.
                $this->lock->release($lock_key);

                $this->logger()->debug(dt('Disabled @language @type SKU @sku removed from the system.', [
                  '@language' => $languages[$langcode]->getName(),
                  '@type' => $type,
                  '@sku' => $sku,
                ]));
              }
            }
          }
        }
        elseif ($this->io()
          ->confirm(dt('Do you want to sync the @count @type extra SKUs?', [
            '@count' => count($to_be_deleted[$type]['all']),
            '@type' => $type,
          ]))) {
          foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
            foreach (array_chunk(str_replace("'", '', $to_be_deleted[$type]['all']), $chunk_size) as $chunk) {
              $this->ingestApiWrapper->productFullSync($store_id, $langcode, implode(',', $chunk), NULL, $page_size);
            }

            $this->output->writeln(dt('Sync launched for the @count @language @type extra SKUs.', [
              '@count' => count($to_be_deleted[$type]['all']),
              '@language' => $languages[$langcode]->getName(),
              '@type' => $type,
            ]));
          }
        }
      }

      // Category diff sku synchronization.
      if (!empty($cat_mismatch_sync[$type]) && $this->io()->confirm(dt('Do you want to sync the @count @type category diff SKUs?', [
        '@count' => count($cat_mismatch_sync[$type]),
        '@type' => $type,
      ]))) {
        foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
          foreach (array_chunk(str_replace("'", '', $cat_mismatch_sync[$type]), $chunk_size) as $chunk) {
            $this->ingestApiWrapper->productFullSync($store_id, $langcode, implode(',', $chunk), NULL, $page_size);
          }

          $this->output->writeln(dt('Sync launched for the @count @language @type category diff SKUs.', [
            '@count' => count($cat_mismatch_sync[$type]),
            '@language' => $languages[$langcode]->getName(),
            '@type' => $type,
          ]));
        }
      }

      // Stock and price sync is only valid for simple SKUs. Also, these are
      // not translatable fields, hence we only need to sync one language.
      if ($type == 'simple') {
        $stores = $this->i18nHelper->getStoreLanguageMapping();
        $langcode = key($stores);
        $store_id = $stores[$langcode];

        // Stock mismatch synchronization.
        if (!empty($stock_mismatch_sync[$type]) && $this->io()->confirm(dt('Do you want to sync the @count @type SKUs with stock mismatch?', [
          '@count' => count($stock_mismatch_sync[$type]),
          '@type' => $type,
        ]))) {
          foreach (array_chunk(str_replace("'", '', $stock_mismatch_sync[$type]), $chunk_size) as $chunk) {
            $this->ingestApiWrapper->productFullSync($store_id, $langcode, implode(',', $chunk), NULL, $page_size);
          }

          $this->output->writeln(dt('Sync launched for the @count @type SKUs with stock mismatch.', [
            '@count' => count($stock_mismatch_sync[$type]),
            '@type' => $type,
          ]));
        }

        // Price mismatch synchronization.
        if (!empty($price_mismatch_sync[$type]) && $this->io()->confirm(dt('Do you want to sync the @count @type SKUs with price mismatch?', [
          '@count' => count($price_mismatch_sync[$type]),
          '@type' => $type,
        ]))) {
          foreach (array_chunk(str_replace("'", '', $price_mismatch_sync[$type]), $chunk_size) as $chunk) {
            $this->ingestApiWrapper->productFullSync($store_id, $langcode, implode(',', $chunk), NULL, $page_size);
          }

          $this->output->writeln(dt('Sync launched for the @count @type SKUs with price mismatch.', [
            '@count' => count($price_mismatch_sync[$type]),
            '@type' => $type,
          ]));
        }
      }
    }
  }

  /**
   * Run sanity check to get a diff of SKUs between Drupal and Magento.
   *
   * @param array $options
   *   List of options supported by drush command.
   *
   * @command alshaya_api:sanity-check-sku-diff
   *
   * @option types
   *   The comma-separated list of SKUs types to check (simple, configurable).
   * @option magento_source
   *   The source to get the SKUs (api, report). Default is merchandising
   *   report.
   * @option page_size
   *   ACM page size.
   * @option use_delete
   *   Hidden deletion option.
   * @option live_check
   *   In context of merchandising report as magento source, confirm the diff
   *   via api to avoid mismatch due to outdated report.
   * @option use_cached_report
   *
   * @aliases aascsd,alshaya-api-sanity-check-sku-diff
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function sanityCheckSkuDiff(
    array $options = [
      'types' => 'simple,configurable',
      'magento_source' => 'report',
      'page_size' => 10,
      'use_delete' => FALSE,
      'live_check' => FALSE,
      'use_cached_report' => FALSE,
    ]
  ) {
    $debug = $options['debug'];
    $verbose = $options['verbose'];

    $types = array_map('trim', explode(',', $options['types']));

    $msource = $options['magento_source'];
    $page_size = $options['page_size'];
    $use_delete = $options['use_delete'];
    $use_cached_report = $options['use_cached_report'];

    // We want to be sure the live_check is used only for report.
    $live_check = $msource == 'report' ? $options['live_check'] : FALSE;

    $languages = $this->languageManager->getLanguages();

    // Retrieve all enabled SKUs from Magento indexed by type.
    $this->output->writeln(dt('Getting @types SKUs from Magento, please wait...', [
      '@types' => implode(dt(' and '), $types),
    ]));

    if ($msource == 'report') {
      $mskus = $this->alshayaApiWrapper->getEnabledSkusFromMerchandisingReport($types, !$use_cached_report);
    }
    else {
      $mskus = $this->alshayaApiWrapper->getSkus($types);
    }

    if ($debug) {
      foreach ($types as $type) {
        $this->output->writeln(dt("@type SKUs (@count) from Magento:\n@skus", [
          '@type' => $type,
          '@count' => count($mskus[$type]),
          '@skus' => "'" . implode("','", array_keys($mskus[$type])) . "'",
        ]));
      }

      // Notify in debug mode.
      if ($msource == 'api') {
        $this->logger->notice(dt('With source=api, stock and price will not be validated.'));
      }
    }

    // Retrieve all enabled SKUs from Drupal indexed by type and langcode.
    $this->output->writeln(dt("\nGetting @types SKUs from Drupal, please wait...", [
      '@types' => implode(dt(' and '), $types),
    ]));

    foreach ($types as $type) {
      foreach ($languages as $language) {
        $dskus[$type][$language->getId()] = $this->skuManager->getSkus($language->getId(), $type);

        if ($debug) {
          $this->output->writeln(dt("@type @language SKUs (@count) from Drupal:\n@skus", [
            '@type' => $type,
            '@language' => $language->getName(),
            '@count' => count($dskus[$type][$language->getId()]),
            '@skus' => "'" . implode("','", array_keys($dskus[$type][$language->getId()])) . "'",
          ]));
        }
      }
    }

    $this->output->writeln(dt("\n#### SUMMARY ####"));

    $missing = [];
    $to_be_deleted = [];
    $stock_mismatch_sync = [];
    $price_mismatch_sync = [];

    foreach ($types as $type) {
      $missing[$type]['all'] = [];
      $to_be_deleted[$type]['all'] = [];
      $stock_mismatch[$type] = [];
      $price_mismatch[$type] = [];

      foreach ($languages as $language) {
        // The ones which are missing in Drupal.
        $missing[$type][$language->getId()] = array_diff(array_keys($mskus[$type]), array_keys($dskus[$type][$language->getId()]));

        // If live-check is enabled, we confirm the missing SKUs are enabled
        // in MDC.
        if ($live_check && !empty($missing[$type][$language->getId()])) {
          $mskus2 = $this->alshayaApiWrapper->getSkus([$type], $missing[$type][$language->getId()]);
          $missing[$type][$language->getId()] = array_diff(array_keys($mskus2[$type]), array_keys($dskus[$type][$language->getId()]));
        }
        $mall = array_merge($missing[$type]['all'], $missing[$type][$language->getId()]);
        $missing[$type]['all'] = $mall;

        // The ones which are only in Drupal and should be removed.
        $to_be_deleted[$type][$language->getId()] = array_diff(array_keys($dskus[$type][$language->getId()]), array_keys($mskus[$type]));

        // If live-check is enabled, we confirm none of the identified SKUs to
        // be deleted are actually enabled in MDC. If any, we remove these from
        // the list of SKUs to be deleted from Drupal.
        if ($live_check && !empty($to_be_deleted[$type][$language->getId()])) {
          $enabled = $this->alshayaApiWrapper->getSkus([$type], $to_be_deleted[$type][$language->getId()]);

          if (!empty($enabled[$type])) {
            $to_be_deleted[$type][$language->getId()] = array_diff($to_be_deleted[$type][$language->getId()], array_keys($enabled[$type]));
          }
        }
        $tall = array_merge($to_be_deleted[$type]['all'], $to_be_deleted[$type][$language->getId()]);
        $to_be_deleted[$type]['all'] = $tall;

        if (!empty($missing[$type][$language->getId()])) {
          $this->output->writeln(dt("\n@count @language @type's SKUs are missing in Drupal and must be synced!skus", [
            '@count' => count($missing[$type][$language->getId()]),
            '@language' => $language->getName(),
            '@type' => $type,
            '!skus' => $verbose ? ":\n'" . implode("','", $missing[$type][$language->getId()]) . "'" : '.',
          ]));
        }
        else {
          $this->output->writeln(dt("\nNo missing SKUs match for @language @type in Drupal.", [
            '@language' => $language->getName(),
            '@type' => $type,
          ]));
        }

        if (!empty($to_be_deleted[$type][$language->getId()])) {
          $this->output->writeln(dt("\n@count @language @type's SKUs are only in Drupal and must be removed!skus", [
            '@count' => count($to_be_deleted[$type][$language->getId()]),
            '@language' => $language->getName(),
            '@type' => $type,
            '!skus' => $verbose ? ":\n'" . implode("','", $to_be_deleted[$type][$language->getId()]) . "'" : '.',
          ]));
        }
        else {
          $this->output->writeln(dt("\nNo additional SKUs for @language @type found in Drupal. Nothing to delete.", [
            '@language' => $language->getName(),
            '@type' => $type,
          ]));
        }
      }

      // We check stock and price only in case of merch because the main API
      // from Magento does not provide the stock information. Also, the stock
      // and price fields are not translatable.
      if ($type == 'simple' && $msource == 'report') {
        $checked_skus = [];

        // Get total number of api requests that will be made against magento
        // on stock/price difference when `live_check' flag is used and inform
        // the user about it.
        if ($live_check) {
          $total_live_check_api_calls = [];
          $mskus_data = $mskus[$type];
          foreach ($languages as $lang) {
            $dskus_data = $dskus[$type][$lang->getId()];
            $total_live_check_api_calls[] = array_filter($dskus_data, function ($sku_data) use ($mskus_data) {
              return (
                !empty($mskus_data[$sku_data['sku']])
                && (
                  $sku_data['quantity'] != $mskus_data[$sku_data['sku']]['qty']
                  || $sku_data['price'] != $mskus_data[$sku_data['sku']]['price']
                  || $sku_data['special_price'] != $mskus_data[$sku_data['sku']]['special_price']
                )
              );
            });
          }

          if (!empty($total_live_check_api_calls)) {
            $this->io()->note(dt('Total:@count API calls will be made to MDC for stock/price difference.', [
              '@count' => count(array_merge(...$total_live_check_api_calls)),
            ]));
          }
          else {
            $this->io()->note(dt('Stock/Price difference looks fine. No api request will be made to MDC.'));
          }
        }

        foreach ($languages as $language) {
          foreach ($dskus[$type][$language->getId()] as $sku => $data) {
            // We will check the stock and price for this SKU only if it exists
            // in Magento (otherwise it must be deleted) and if it has not been
            // checked yet in another language.
            if (!empty($mskus[$type][$sku]) && !in_array($sku, $checked_skus)) {
              $checked_skus[] = $sku;

              $stock_output = '';
              $price_output = '';

              // If stock in drupal does not match with stock from merch and
              // live-check is enabled, we get the stock from API to confirm
              // the stock difference.
              if ($live_check && $data['quantity'] != $mskus[$type][$sku]['qty']) {
                if ($debug) {
                  $this->output->writeln(dt("Fetching stock from MDC for SKU:`@sku` for language:@langcode for live check.", [
                    '@sku' => $sku,
                    '@langcode' => $language->getId(),
                  ]));
                }
                $mskus[$type][$sku]['qty'] = $this->alshayaApiWrapper->getStock($sku);
              }

              // If stock in drupal does not match with stock from merch.
              if ($data['quantity'] != (int) $mskus[$type][$sku]['qty']) {
                $stock_output .= 'Drupal stock:' . $data['quantity'] . ' | ';
                $stock_output .= 'MDC stock:' . (int) $mskus[$type][$sku]['qty'];
                $stock_mismatch_sync[$type][] = $sku;
              }

              // If any of the prices from Drupal and merch report diverge and
              // live-check is enabled, we get the prices from API to confirm
              // the price differences.
              if ($live_check && (
                  $data['price'] != $mskus[$type][$sku]['price'] || $data['special_price'] != $mskus[$type][$sku]['special_price'])
              ) {
                if ($debug) {
                  $this->output->writeln(dt("Fetching price from MDC for SKU:`@sku` for language:@langcode for live check.", [
                    '@sku' => $sku,
                    '@langcode' => $language->getId(),
                  ]));
                }
                $sku_data = $this->alshayaApiWrapper->getSku($sku);

                if (isset($sku_data['price'])) {
                  $mskus[$type][$sku]['price'] = $sku_data['price'];
                }

                if (!empty($sku_data['custom_attributes'])) {
                  foreach ($sku_data['custom_attributes'] as $attribute) {
                    if ($attribute['attribute_code'] == 'special_price') {
                      $mskus[$type][$sku]['special_price'] = $attribute['value'];
                      break;
                    }
                  }
                }
              }

              // If price in drupal not matches with what in magento.
              if ($data['price'] != $mskus[$type][$sku]['price']) {
                $price_output .= 'Drupal price:' . $data['price'] . ' | ';
                $price_output .= 'MDC price:' . $mskus[$type][$sku]['price'];
                $price_mismatch_sync[$type][] = $sku;
              }

              // If special price in drupal not matches with what in magento.
              if ($data['special_price'] != $mskus[$type][$sku]['special_price']) {
                $price_output .= 'Drupal spl price:' . $data['special_price'] . ' | ';
                $price_output .= 'MDC spl price:' . $mskus[$type][$sku]['special_price'];
                $price_mismatch_sync[$type][] = $sku;
              }

              // If there any sku having stock mismatch.
              if (!empty($stock_output)) {
                $stock_mismatch[$type][$sku] = "SKU:" . $sku . " | " . $stock_output;
              }

              // If there any sku having price mismatch.
              if (!empty($price_output)) {
                $price_mismatch[$type][$sku] = "SKU:" . $sku . " | " . $price_output;
              }
            }
          }
        }

        // Output the details of stock mismatch.
        if (!empty($stock_mismatch[$type])) {
          $this->output->writeln(dt("\n@count @type's SKUs in drupal have different stock than magento!output", [
            '@count' => count($stock_mismatch[$type]),
            '@type' => $type,
            '!output' => $verbose ? ":\n" . implode("\n", $stock_mismatch[$type]) : '.',
          ]));
        }
        else {
          $this->output->writeln(dt("\nNo stock mismatch for @type's in Drupal.", [
            '@type' => $type,
          ]));
        }

        // Output the details of price mismatch.
        if (!empty($price_mismatch[$type])) {
          $this->output->writeln(dt("\n@count @type's SKUs in drupal have different price than magento!output", [
            '@count' => count($price_mismatch[$type]),
            '@type' => $type,
            '!output' => $verbose ? ":\n" . implode("\n", $price_mismatch[$type]) : '.',
          ]));
        }
        else {
          $this->output->writeln(dt("\nNo price mismatch for @type's in Drupal.", [
            '@type' => $type,
          ]));
        }
      }

      $missing[$type]['all'] = array_unique($missing[$type]['all']);
      $to_be_deleted[$type]['all'] = array_unique($to_be_deleted[$type]['all']);
      $stock_mismatch_sync[$type] = !empty($stock_mismatch_sync[$type]) ? array_unique($stock_mismatch_sync[$type]) : [];
      $price_mismatch_sync[$type] = !empty($price_mismatch_sync[$type]) ? array_unique($price_mismatch_sync[$type]) : [];
    }

    $this->output->writeln(dt("\n#### SYNCHRONIZATION ####"));

    $chunk_size = 100;

    // Sync store/price mis-match skus only for the merch report. Because
    // stock and price are not translatable, we only launch the sync for one
    // language.
    if ($msource == 'report') {
      // Sync stock/price mis-match skus.
      foreach ($types as $type) {

        // Stock/Price mis-match sku sync only for simple skus.
        if ($type != 'simple') {
          continue;
        }

        $stores = $this->i18nHelper->getStoreLanguageMapping();
        $langcode = key($stores);
        $store_id = $stores[$langcode];

        // Stock mismatch synchronization.
        if (!empty($stock_mismatch_sync[$type]) && $this->io()->confirm(dt('Do you want to sync the @count @type stock mismatch SKUs?', [
          '@count' => count($stock_mismatch_sync[$type]),
          '@type' => $type,
        ]))) {
          // We split the list of SKUs in small chunk to avoid any issue. This
          // is only to send the request to Conductor.
          foreach (array_chunk(str_replace("'", '', $stock_mismatch_sync[$type]), $chunk_size) as $chunk) {
            // @TODO: Make page size a config. It can be used in multiple places.
            // @TODO: It seems there is nothing being logged when fullSync is
            // launched.
            $this->ingestApiWrapper->productFullSync($store_id, $langcode, implode(',', $chunk), NULL, $page_size);
          }

          $this->output->writeln(dt('Sync launched for the @count @type SKUs with stock mismatch.', [
            '@count' => count($stock_mismatch_sync[$type]),
            '@type' => $type,
          ]));
        }

        // Price mismatch synchronization.
        if (!empty($price_mismatch_sync[$type]) && $this->io()->confirm(dt('Do you want to sync the @count @type price mismatch SKUs?', [
          '@count' => count($price_mismatch_sync[$type]),
          '@type' => $type,
        ]))) {
          // We split the list of SKUs in small chunk to avoid any issue. This
          // is only to send the request to Conductor.
          foreach (array_chunk(str_replace("'", '', $price_mismatch_sync[$type]), $chunk_size) as $chunk) {
            // @TODO: Make page size a config. It can be used in multiple places.
            // @TODO: It seems there is nothing being logged when fullSync is
            // launched.
            $this->ingestApiWrapper->productFullSync($store_id, $langcode, implode(',', $chunk), NULL, $page_size);
          }

          $this->output->writeln(dt('Sync launched for the @count @type SKUs with price mismatch.', [
            '@count' => count($price_mismatch_sync[$type]),
            '@type' => $type,
          ]));
        }
      }
    }

    // Retrieve missing SKUs.
    foreach ($types as $type) {
      if (!empty($missing[$type]['all']) && $this->io()->confirm(dt('Do you want to sync the @count @type missing SKUs?', [
        '@count' => count($missing[$type]['all']),
        '@type' => $type,
      ]))) {
        foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
          // We split the list of SKUs in small chunk to avoid any issue. This
          // is only to send the request to Conductor.
          foreach (array_chunk(str_replace("'", '', $missing[$type]['all']), $chunk_size) as $chunk) {
            // @TODO: Make page size a config. It can be used in multiple places.
            // @TODO: It seems there is nothing being logged when fullSync is
            // launched.
            $this->ingestApiWrapper->productFullSync($store_id, $langcode, implode(',', $chunk), NULL, $page_size);
          }

          $this->output->writeln(dt('Sync launched for the @count @language @type SKUs.', [
            '@count' => count($missing[$type]['all']),
            '@language' => $languages[$langcode]->getName(),
            '@type' => $type,
          ]));
        }
      }
    }

    // Try to resync extra SKUs.
    foreach ($types as $type) {
      if (!empty($to_be_deleted[$type]['all']) && $this->io()->confirm(dt('Do you want to sync the @count @type extra SKUs?', [
        '@count' => count($to_be_deleted[$type]['all']),
        '@type' => $type,
      ]))) {
        foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
          // We split the list of SKUs in small chunk to avoid any issue. This
          // is only to send the request to Conductor.
          foreach (array_chunk(str_replace("'", '', $to_be_deleted[$type]['all']), $chunk_size) as $chunk) {
            // @TODO: Make page size a config. It can be used in multiple places.
            // @TODO: It seems there is nothing being logged when fullSync is
            // launched.
            $this->ingestApiWrapper->productFullSync($store_id, $langcode, implode(',', $chunk), NULL, $page_size);
          }

          $this->output->writeln(dt('Sync launched for the @count @language @type SKUs.', [
            '@count' => count($to_be_deleted[$type]['all']),
            '@language' => $languages[$langcode]->getName(),
            '@type' => $type,
          ]));
        }
      }
    }

    if ($use_delete === FALSE) {
      return;
    }

    // Delete additional SKUs.
    // @TODO: Some of the code to delete node + sku is duplicate from
    // ProductSyncResource::post(). We might want a service to do that task and
    // remove code duplication.
    foreach ($types as $type) {
      if (!empty($to_be_deleted[$type]['all']) && $this->io()->confirm(dt('Do you want to delete the @count @type additional SKUs?', [
        '@count' => count($to_be_deleted[$type]['all']),
        '@type' => $type,
      ]))) {
        foreach ($languages as $langcode => $language) {
          foreach ($to_be_deleted[$type][$langcode] as $sku) {
            if ($sku_entity = SKU::loadFromSku($sku, $langcode, FALSE, TRUE)) {
              $this->logger->notice('Removing disabled @language @type SKU @sku from the system: @sku.', [
                '@language' => $languages[$langcode]->getName(),
                '@type' => $type,
                '@sku' => $sku,
              ]);

              $lock_key = 'deleteProduct' . $sku;

              // Acquire lock to ensure parallel processes are executed
              // sequentially.
              // @TODO: These 8 lines might be duplicated in multiple places. We
              // may want to create a utility service in alshaya_performance.
              do {
                $lock_acquired = $this->lock->acquire($lock_key);

                // Sleep for half a second before trying again.
                // @TODO: Move this 0.5s to a config variable.
                if (!$lock_acquired) {
                  usleep(500000);
                }
              } while (!$lock_acquired);

              // Delete the node if it is linked to this SKU only.
              try {
                if ($node = $sku_entity->getPluginInstance()->getDisplayNode($sku_entity, FALSE, FALSE)) {
                  $node->delete();
                }
              }
              catch (\Exception $e) {
                // Not doing anything, we might not have node for the sku.
              }

              // Delete the SKU.
              $sku_entity->delete();

              // Release the lock.
              $this->lock->release($lock_key);

              $this->output->writeln(dt('Disabled @language @type SKU @sku removed from the system.', [
                '@language' => $languages[$langcode]->getName(),
                '@type' => $type,
                '@sku' => $sku,
              ]));
            }
          }
        }
      }
    }
  }

  /**
   * Sanity check to identify products.
   *
   * Run Sanity check to identify products from SKUs which are not supposed to
   * be visible.
   *
   * @param array $options
   *   List of options supported by drush command.
   *
   * @command alshaya_api:sanity-check-visibility
   *
   * @aliases aascv,alshaya-api-sanity-check-visibility
   * @options display_nids Boolean flag to display list of nodes.
   */
  public function sanityCheckVisibility(array $options = ['display_nids' => FALSE]) {
    $verbose = $options['display_nids'];

    $handle = $this->alshayaApiWrapper->getMerchandisingReport();

    if (!$handle) {
      $this->output->writeln(dt('Impossible to get the merchandising report from Magento.'));
      return;
    }

    // Because the column position may vary across brands, we are browsing the
    // report's first line to identify the position of each column we need.
    $indexes = [
      'partnum' => FALSE,
      'visibility' => FALSE,
    ];

    $not_visible_ind_skus = [];

    if ($data = fgetcsv($handle, 1000, ',')) {
      foreach ($data as $position => $key) {
        foreach ($indexes as $name => $index) {
          if (trim(strtolower($key)) == $name) {
            $indexes[$name] = $position;
            continue;
          }
        }
      }

      if (in_array(FALSE, $indexes)) {
        return FALSE;
      }

      while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        // This is a weird case where not visible SKU does not have any related
        // configurable.
        if (trim(strtolower($data[$indexes['visibility']])) == 'not visible individually') {
          $not_visible_ind_skus[] = $data[$indexes['partnum']];
        }
      }
    }
    fclose($handle);

    if (empty($not_visible_ind_skus)) {
      $this->output->writeln(dt('There is no SKU which are not visible individually as per the merchandising report.'));
      return;
    }

    $query = $this->connection->select('node__field_skus', 'n');
    $query->fields('n', ['entity_id']);
    $query->condition('n.bundle', 'acq_product');
    $query->condition('n.field_skus_value', $not_visible_ind_skus, 'IN');
    $nids = $query->execute()->fetchAllKeyed(0, 0);

    $this->output->writeln(dt('@count product nodes are related to SKUs which are not supposed to be visible individually!nids', [
      '@count' => count($nids),
      '!nids' => $verbose ? ":\n'" . implode("','", $nids) . "'" : '.',
    ]));

    if (!empty($nids) && $this->io()->confirm(dt('Do you want to delete the @count product nodes?', [
      '@count' => count($nids),
    ]))) {
      $count = 1;
      foreach ($nids as $nid) {
        try {
          $storage = $this->entityTypeManager->getStorage('node');
          if ($node = $storage->load($nid)) {
            $node->delete();

            if ($count % 100 == 0) {
              $this->output->writeln(dt("Delete @count/@total...", [
                '@count' => $count,
                '@total' => count($nids),
              ]));
            }
            $count++;
          }
        }
        catch (\Exception $e) {
          $this->logger->warning('Impossible to delete the product node @nid', ['@nid' => $nid]);
        }
      }
    }
  }

}
