<?php

namespace Drupal\alshaya_acm_product_category\Commands;

use Drupal\alshaya_acm_product_category\Service\ProductCategorySyncManager;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drush\Commands\DrushCommands;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\metatag\MetatagManagerInterface;
use Drupal\metatag\MetatagToken;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Expose drush commands for alshaya_acm_product_category.
 */
class AlshayaAcmProductCategoryDrushCommands extends DrushCommands {

  /**
   * Product Category Sync manager service.
   *
   * @var \Drupal\alshaya_acm_product_category\Service\ProductCategorySyncManager
   */
  protected $categorySyncManager;

  /**
   * Product category tree.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $productCategoryTree;

  /**
   * File system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Metatag manager service.
   *
   * @var \Drupal\metatag\MetatagManagerInterface
   */
  protected $metatagManager;

  /**
   * The Metatag token service.
   *
   * @var \Drupal\metatag\MetatagToken
   */
  protected $metatagToken;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The export directory path.
   *
   * @var string
   */
  public const PATH = 'public://exports/v2/';

  /**
   * The filename prefix for the output file.
   *
   * @var string
   */
  public const FILE_NAME_PREFIX = 'product-category-data';

  /**
   * AlshayaAcmProductCategoryDrushCommands constructor.
   *
   * @param \Drupal\alshaya_acm_product_category\Service\ProductCategorySyncManager $category_sync_manager
   *   Product Category Sync manager service.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product category tree.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   File system service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\metatag\MetatagManagerInterface $metatag_manager
   *   The Metatag manager service.
   * @param \Drupal\metatag\MetatagToken $metatag_token
   *   The Metatag token service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   */
  public function __construct(
    ProductCategorySyncManager $category_sync_manager,
    ProductCategoryTree $product_category_tree,
    FileSystemInterface $file_system,
    EntityTypeManagerInterface $entity_type_manager,
    MetatagManagerInterface $metatag_manager,
    MetatagToken $metatag_token,
    LoggerChannelFactoryInterface $logger_factory,
    LanguageManagerInterface $language_manager,
    ConfigFactoryInterface $config_factory
    ) {
    parent::__construct();
    $this->categorySyncManager = $category_sync_manager;
    $this->productCategoryTree = $product_category_tree;
    $this->fileSystem = $file_system;
    $this->entityTypeManager = $entity_type_manager;
    $this->metatagManager = $metatag_manager;
    $this->metatagToken = $metatag_token;
    $this->drupalLogger = $logger_factory->get('alshaya_acm_product_category');
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * Removes the categories no longer available in Commerce system.
   *
   * @command alshaya_acm_product_category:remove-orphan-categories
   *
   * @aliases remove-orphan-cats
   *
   * @usage drush remove-orphan-cats
   *   Removes the categories no longer available in Commerce system.
   */
  public function removeOrphanCategories() {
    $this->categorySyncManager->removeOrphanCategories();
  }

  /**
   * Export the product category data as CSV.
   *
   * @command alshaya_acm_product_category:export-data
   *
   * @aliases export-pcdata
   *
   * @options limit The number of terms to process per batch.
   *
   * @usage drush export-product-category-data --limit 50
   *   Process 50 terms per batch and output to the file. Default is 30.
   */
  public function exportProductCategoryData($options = ['limit' => 30]) {
    // Get all product category terms if supercategory is enabled
    // else get child terms.
    if ($this->configFactory->get('alshaya_super_category.settings')->get('status')) {
      $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('acq_product_category');
      $term_ids = array_map(fn ($term) => $term->tid, $terms);
      $term_ids = !empty($term_ids)
        ? $term_ids
        : [];
    }
    else {
      $term_ids = array_keys($this->productCategoryTree->getChildTermIds());
    }

    // Combine parent and child tids.
    $tids = [];
    foreach ($term_ids as $term_id) {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id);
      $child_tids = $this->productCategoryTree->getNestedChildrenTids($term);
      $tids = array_merge($tids, [$term_id], $child_tids);
    }

    // Return if no product category term found.
    if (empty($tids)) {
      $this->drupalLogger->notice('Product category terms not found, no data exported.');
      return;
    }

    $path = self::PATH;
    $output_directory = $this->fileSystem->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY);
    if (!$output_directory) {
      $this->drupalLogger->notice('Could not read/create the directory to export the data.');
      return;
    }

    // Query string for the output files.
    $query_string = \time();
    // Check if it is possible to create the output files.
    foreach ($this->languageManager->getLanguages() as $langcode => $language) {
      try {
        $location = $this->fileSystem->getDestinationFilename($path . self::FILE_NAME_PREFIX . '-' . $langcode . '.csv', FileSystemInterface::EXISTS_REPLACE);
        if ($location === FALSE) {
          $this->drupalLogger->warning('Could not create the file to export the data.');
          return;
        }
        else {
          $file_url = file_create_url($location);
          $this->drupalLogger->notice('Langcode: ' . $langcode . '. File: ' . "$file_url?$query_string");
        }

        // Make the file empty.
        $file = fopen($location, 'wb+');
        fclose($file);
      }
      catch (\Exception $e) {
        $this->drupalLogger->warning(dt('Could not create the file to export the data. Message: @message.', [
          '@message' => $e->getMessage(),
        ]));
        return;
      }
    }

    $batch = [
      'title' => 'Export Product Category Data',
      'init' => 'Starting product category data export process..',
      'finished' => [self::class, 'batchFinished'],
    ];

    foreach (array_chunk($tids, $options['limit']) as $tid_batch) {
      $batch['operations'][] = [
        [
          '\Drupal\alshaya_acm_product_category\Commands\AlshayaAcmProductCategoryDrushCommands',
          'exportProductCategory',
        ],
        [$tid_batch],
      ];
    }

    batch_set($batch);
    drush_backend_batch_process();
  }

  /**
   * Main batch operation which aggregates the data to be exported.
   *
   * @param array $tids
   *   The tids for which data is to be exported.
   * @param mixed $context
   *   The batch context.
   */
  public static function exportProductCategory(array $tids, &$context) {
    if (!isset($context['results']['terms'])) {
      $context['results']['terms'] = 0;
      $context['results']['failed_tids'] = [];
      $context['results']['data'] = [];
      $context['results']['csv_header_added'] = [];
    }

    $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    // Process all the terms and put the data in csv file.
    foreach ($tids as $tid) {
      try {
        // Export the translated term data as well.
        /** @var \Drupal\taxonomy\TermInterface $term */
        $term = $term_storage->load($tid);
        foreach ($term->getTranslationLanguages() as $langcode => $language) {
          $translated_term = $term->getTranslation($langcode);
          // Get the term data of the required fields.
          $data = self::getTermData($translated_term);
          $location = \Drupal::service('file_system')->getDestinationFilename(self::PATH . self::FILE_NAME_PREFIX . '-' . $langcode . '.csv', FileSystemInterface::EXISTS_REPLACE);
          $handle = fopen($location, 'a');

          // Add the header only once.
          if (!isset($context['results']['csv_header_added'][$langcode])) {
            $context['results']['csv_header_added'][$langcode] = TRUE;
            fputcsv($handle, array_keys($data));
          }
          // Add the data we exported to the next line of the CSV.
          fputcsv($handle, array_values($data));
          // Close the file handler since we don't need it anymore.  We are not
          // storing this file anywhere in the filesystem.
          fclose($handle);
        }
        $context['results']['terms']++;
      }
      catch (\Exception) {
        $context['results']['failed_tids'][] = $tid;
      }
    }

    \Drupal::logger('alshaya_acm_product_category')->notice('@count terms processed for exporting.', ['@count' => count($tids)]);
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
  public static function batchFinished($success, array $results, array $operations) {
    $drupalLogger = \Drupal::logger('alshaya_acm_product_category');

    if ($success) {
      // Here we do something meaningful with the results.
      $drupalLogger->notice('@count items successfully processed.', ['@count' => $results['terms']]);
    }
    else {
      $drupalLogger->warning('Could not successfully complete the batch process.');
    }

    if (!empty($results['failed_tids'])) {
      $drupalLogger->warning('Could not successfully process @count items. Items: @items', [
        '@count' => is_countable($results['failed_tids']) ? count($results['failed_tids']) : 0,
        '@items' => implode(',', $results['failed_tids']),
      ]);
    }
  }

  /**
   * Get the machine name of all the fields.
   *
   * @return array
   *   An array of machine name of all the fields required in CSV file.
   */
  protected static function getRequiredFields() {
    return [
      'store_code',
      'url_alias',
      'field_commerce_id',
      'field_show_in_lhn',
      'field_show_in_app_navigation',
      'field_mobile_only_dpt_page_link',
      'field_category_quicklink_plp_mob',
      'field_show_on_department_page',
      'field_category_google',
      'field_sorting_order',
      'field_sorting_labels',
      'field_sorting_options',
      'field_sort_options_labels',
      'field_promo_banner_for_mobile',
      'field_promotion_banner_mobile',
      'field_promotion_banner',
      'field_write_review_form_fields',
      'field_rating_review',
      'field_select_sub_categories_plp',
      'field_plp_group_category_title',
      'field_plp_group_category_img',
      'field_plp_group_category_desc',
      'field_group_by_sub_categories',
      'field_meta_tags',
      'field_logo_active_image',
      'field_logo_header_image',
      'field_logo_inactive_image',
    ];
  }

  /**
   * Get required term data for export.
   *
   * @param \Drupal\taxonomy\Entity\Term $term
   *   The term object.
   *
   * @return array
   *   An array of data required for the term.
   */
  public static function getTermData(Term $term) {
    // Get the list of fields required.
    $fields = self::getRequiredFields();
    $data = [];

    /** @var \Drupal\Core\Language\LanguageManagerInterface $language_manager */
    $language_manager = \Drupal::languageManager();
    $current_language = $language_manager->getConfigOverrideLanguage();
    $language_manager->setConfigOverrideLanguage($term->language());
    $term_langcode = $term->language()->getId();

    foreach ($fields as $field) {
      // Proceed only if field is present.
      if (in_array($field, ['store_code', 'url_alias']) || $term->hasField($field)) {
        // Assigning empty value to maintain the CSV file mapping.
        $data[$field] = '';
        // Process all the fields based on machine name.
        switch ($field) {
          case 'store_code':
            $data[$field] = self::getStoreCode($term_langcode);
            break;

          case 'field_plp_group_category_img':
          case 'field_promotion_banner':
          case 'field_promotion_banner_mobile':
          case 'field_logo_active_image':
          case 'field_logo_header_image':
          case 'field_logo_inactive_image':
            $value = $term->get($field)->getValue();
            if (!empty($value) && array_key_exists('target_id', $value[0])) {
              $target_id = $value[0]['target_id'];
              // Load file and get the Image URL.
              /** @var \Drupal\file\FileInterface $image */
              $image = \Drupal::entityTypeManager()->getStorage('file')->load($target_id);
              if ($image) {
                $data[$field] = $image->createFileUrl(FALSE);
              }
            }
            break;

          case 'field_meta_tags':
            $tags = \Drupal::service('metatag.manager')->tagsFromEntityWithDefaults($term);
            // Allow modules to override tags or the entity used for token
            // replacements.
            $context = ['entity' => &$term];
            \Drupal::moduleHandler()->alter('metatags', $tags, $context);

            self::removeNonEssentialTags($tags);

            // If the entity was changed above, use that for generating the
            // meta tags.
            if (isset($context['entity'])) {
              $term = $context['entity'];
            }
            // Iterate through the $tags and pass it to metatag::replace.
            foreach ($tags as $key => $value) {
              $data[$key] = \Drupal::service('metatag.token')->replace($value, ['taxonomy_term' => $term], [
                'langcode' => $term_langcode,
              ]);
            }
            // Remove metatag empty field and canonical url.
            unset($data['field_meta_tags']);
            unset($data['canonical_url']);
            break;

          case 'field_select_sub_categories_plp':
            $ids = $term->get($field)->getValue();
            $items = [];
            foreach ($ids as $id) {
              // The value is the tid of the term. So getting the term name.
              $target_term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($id['value']);
              if ($target_term) {
                array_push($items, $target_term->label());
              }
            }
            $data[$field] = implode(',', $items);
            break;

          case 'field_plp_group_category_desc':
            $value = $term->get($field)->getValue();
            if ($value) {
              $data[$field] = $value['value'];
            }
            break;

          case 'url_alias':
            $url = $term->toUrl()->toString();
            // Only provide the path without langcode and domain.
            $data[$field] = str_replace('/' . $term_langcode, '', $url);
            break;

          default:
            $data[$field] = $term->get($field)->getString();
            break;
        }
      }
    }
    // Sorting based on key value so that order of data remains same.
    ksort($data);

    // Revert the override for the language change.
    $language_manager->setConfigOverrideLanguage($current_language);

    return $data;
  }

  /**
   * Removes metatags that are not required in the output.
   *
   * @param array $tags
   *   The tags array.
   */
  public static function removeNonEssentialTags(array &$tags) {
    $tags_to_keep = [
      'title',
      'description',
      'keywords',
    ];

    foreach ($tags as $tag => $replacements) {
      if (!in_array($tag, $tags_to_keep)) {
        unset($tags[$tag]);
      }
    }
  }

  /**
   * Gets the magento store code for the given language.
   *
   * @param string $langcode
   *   The langcode.
   *
   * @return string
   *   The store code.
   */
  public static function getStoreCode(string $langcode) {
    static $store_code = [];
    if (isset($store_code[$langcode])) {
      return $store_code[$langcode];
    }

    $store_code[$langcode] = \Drupal::configFactory()
      ->get('alshaya_api.settings')
      ->get('magento_lang_prefix')[$langcode];

    return $store_code[$langcode];
  }

}
