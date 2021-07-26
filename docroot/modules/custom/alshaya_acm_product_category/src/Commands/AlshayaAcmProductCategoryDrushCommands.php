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
   */
  public function __construct(
    ProductCategorySyncManager $category_sync_manager,
    ProductCategoryTree $product_category_tree,
    FileSystemInterface $file_system,
    EntityTypeManagerInterface $entity_type_manager,
    MetatagManagerInterface $metatag_manager,
    MetatagToken $metatag_token,
    LoggerChannelFactoryInterface $logger_factory
    ) {
    parent::__construct();
    $this->categorySyncManager = $category_sync_manager;
    $this->productCategoryTree = $product_category_tree;
    $this->fileSystem = $file_system;
    $this->entityTypeManager = $entity_type_manager;
    $this->metatagManager = $metatag_manager;
    $this->metatagToken = $metatag_token;
    $this->drupalLogger = $logger_factory->get('alshaya_acm_product_category');
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
   * @aliases export-product-category-data
   *
   * @usage drush export-product-category-data --file-name=category_data
   *   Export the product category data as CSV.
   */
  public function exportProductCategoryData($options = ['file-name' => 'product_category_data']) {
    // Get tid of all the parent product category.
    $term_ids = array_keys($this->productCategoryTree->getChildTermIds());

    // Path where we want to export the csv file.
    $path = 'public://exports/v2/';
    if ($this->fileSystem->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY)) {
      $location = $this->fileSystem->createFilename($options['file-name'] . '.csv', $path);
      $handle = fopen($location, 'wb');
      // Combine parent and child tids.
      $tids = [];
      foreach ($term_ids as $term_id) {
        $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id);
        $child_tids = $this->productCategoryTree->getNestedChildrenTids($term);
        $tids = array_merge($tids, [$term_id], $child_tids);
      }
      // Flag variable to track if header is added in the CSV file or not.
      $header_flag = FALSE;
      // Process all the terms and put the data in csv file.
      foreach ($tids as $tid) {
        $data = $this->getTermData($tid);
        if (!$header_flag) {
          // Add the header as the first line of the CSV.
          fputcsv($handle, array_keys($data));
          $header_flag = TRUE;
        }
        // Add the data we exported to the next line of the CSV.
        fputcsv($handle, array_values($data));
      }

      // Close the file handler since we don't need it anymore.  We are not
      // storing this file anywhere in the filesystem.
      fclose($handle);
      $file_path = file_create_url($location);
      $this->drupalLogger->notice(dt('Data exported successfully at Location: @location', ['@location' => $file_path]));
    }
    else {
      $this->drupalLogger->warning(dt('Something went wrong, Please check if the folder permissions are proper.'));
    }
  }

  /**
   * Get the machine name of all the fields.
   *
   * @return array
   *   An array of machine name of all the fields required in CSV file.
   */
  protected function getRequiredFields() {
    return [
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
    ];
  }

  /**
   * Get required term data for export.
   *
   * @param string $term_id
   *   The term id for which we need data.
   *
   * @return array
   *   An array of data required for the term.
   */
  protected function getTermData(string $term_id) {
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id);
    // Get the list of fields required.
    $fields = $this->getRequiredFields();
    $data = [];
    foreach ($fields as $field) {
      // Proceed only if field is present.
      if ($term->hasField($field)) {
        // Assigning empty value to maintain the CSV file mapping.
        $data[$field] = '';
        // Process all the fields based on machine name.
        switch ($field) {
          case 'field_plp_group_category_img':
          case 'field_promotion_banner':
          case 'field_promotion_banner_mobile':
            $value = $term->get($field)->getValue();
            if (!empty($value) && array_key_exists('target_id', $value[0])) {
              $target_id = $value[0]['target_id'];
              // Load file and get the Image URL.
              $image = $this->entityTypeManager->getStorage('file')->load($target_id);
              if ($image) {
                $data[$field] = $image->url();
              }
            }
            break;

          case 'field_meta_tags':
            $tags = $this->metatagManager->tagsFromEntityWithDefaults($term);
            // Iterate through the $tags and pass it to metatag::replace.
            foreach ($tags as $key => $value) {
              $data[$key] = $this->metatagToken->replace($value, ['taxonomy_term' => $term]);
            }
            // Remove metatag empty field.
            unset($data['field_meta_tags']);
            // Change the canonical url to relative url.
            $relative_url = parse_url($data['canonical_url'], PHP_URL_PATH);
            $relative_url = explode('/', $relative_url);
            // Unset the language prefix.
            unset($relative_url['1']);
            $relative_url = implode('/', $relative_url);
            $data['canonical_url'] = $relative_url;
            break;

          case 'field_select_sub_categories_plp':
            $value = $term->get($field)->getString();
            // The value is the tid of the term. So getting the term name.
            $target_term = $this->entityTypeManager->getStorage('taxonomy_term')->load($value);
            if ($target_term) {
              $data[$field] = $target_term->label();
            }
            break;

          case 'field_plp_group_category_desc':
            $value = $term->get($field)->getValue();
            if ($value) {
              $data[$field] = $value['value'];
            }
            break;

          default:
            $data[$field] = $term->get($field)->getString();
            break;
        }
      }
    }

    return $data;
  }

}
