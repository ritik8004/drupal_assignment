<?php

namespace Drupal\alshaya_acm_product_category\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\file\Entity\File;

/**
 * Group by category data export commands class.
 */
class GroupByCategoryDataExportCommands extends DrushCommands {

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * File system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * File generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

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
  public const FILE_NAME_PREFIX = 'group-by-category-data';

  /**
   * CSV top level fields names.
   *
   * @var array
   */
  protected static $fields = [];

  /**
   * Stores information if output fields have been set.
   *
   * @var int
   */
  protected static $isFieldsSet = 0;

  /**
   * GroupByCategoryDataExportCommands constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger channel factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection manager.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file url generator.
   */
  public function __construct(
    LoggerChannelFactoryInterface $logger_factory,
    LanguageManagerInterface $language_manager,
    Connection $connection,
    FileSystemInterface $file_system,
    FileUrlGeneratorInterface $file_url_generator
    ) {
    $this->drupalLogger = $logger_factory->get('alshaya_acm_product_category');
    $this->languageManager = $language_manager;
    $this->connection = $connection;
    $this->fileSystem = $file_system;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * Command callback to export required data for group by category data.
   *
   * @command export-group-by-category-data
   *
   * @aliases egbcd
   *
   * @options batch_size
   *   The number of terms to migrate per batch.
   *
   * @usage drush egbcd --batch_size=30
   */
  public function exportGroupByCategoryData($options = ['batch_size' => 50]) {
    $path = self::PATH;
    $output_directory = $this->fileSystem->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY);

    if (!$output_directory) {
      $this->logger->notice('Could not read/create the directory to export the data.');
      return;
    }

    // Query string for the output files.
    $query_string = \time();
    // Check if it is possible to create the output files.
    foreach ($this->languageManager->getLanguages() as $langcode => $language) {
      try {
        $location = $this->fileSystem->getDestinationFilename($path . self::FILE_NAME_PREFIX . '-' . $langcode . '.csv', FileSystemInterface::EXISTS_REPLACE);
        if ($location === FALSE) {
          $this->logger->warning('Could not create the file to export the data.');
          return;
        }
        else {
          $file_url = $this->fileUrlGenerator->generateAbsoluteString($location);
          $this->logger->notice('Langcode: ' . $langcode . '. File: ' . "$file_url?$query_string");
        }

        // Make the file empty.
        $file = fopen($location, 'wb+');
        fclose($file);
      }
      catch (\Exception $e) {
        $this->logger->warning(dt('Could not create the file to export the data. Message: @message.', [
          '@message' => $e->getMessage(),
        ]));
        return;
      }
    }

    // Set category migrate batch.
    $this->setProductCategoryMigrationBatch($options['batch_size']);
    $this->drupalLogger->notice(dt('Group by category data export completed.'));
  }

  /**
   * Saves field name to the fields variable.
   *
   * @param string $field
   *   Field name.
   */
  private static function addOutputField(string $field) {
    if (self::$isFieldsSet) {
      return;
    }
    self::$fields[] = $field;
  }

  /**
   * Get the fields to print in the CSV file header.
   *
   * @return array
   *   Fields to print in the CSV file.
   */
  private static function getOutputFields() {
    return self::$fields;
  }

  /**
   * Set the batch process for data export.
   *
   * @param int|null $batch_size
   *   Limits the number of categories processed per batch.
   */
  public function setProductCategoryMigrationBatch($batch_size = 30) {
    $query = $this->connection->select('taxonomy_term_field_data', 'tfd');
    $query->fields('tfd', ['tid']);

    // Select only those terms which can be grouped by sub categories.
    $query->leftJoin('taxonomy_term__field_group_by_sub_categories', 'group_by_sub_cat', 'group_by_sub_cat.entity_id = tfd.tid');
    $query->fields('group_by_sub_cat', ['field_group_by_sub_categories_value']);
    $query->condition('group_by_sub_cat.field_group_by_sub_categories_value', 1);

    $query->condition('tfd.vid', 'acq_product_category');

    // Get the terms satisfying the above conditions.
    $tids = $query->distinct()->execute()->fetchAllKeyed(0, 0);

    // Do not process if no terms are found.
    if (!empty($tids)) {
      // // Set batch operations to migrate terms.
      $operations = [];
      foreach (array_chunk($tids, $batch_size) as $tid_chunk) {
        $operations[] = [
          [self::class, 'exportData'],
          [$tid_chunk],
        ];
      }

      $batch = [
        'title' => dt('Group by product category data export'),
        'init_message' => dt('Starting data export for group by category data...'),
        'operations' => $operations,
        'error_message' => dt('Unexpected error while exporting group by category data.'),
        'finished' => [self::class, 'batchFinished'],
      ];
      batch_set($batch);
      drush_backend_batch_process();
    }
  }

  /**
   * Get commerce ids for given term ids.
   *
   * @param array $tids
   *   Array of tids.
   *
   * @return array
   *   Array of corresponding commerce ids.
   */
  private static function getCommerceIds(array $tids) {
    $langcode = \Drupal::service('language_manager')->getCurrentLanguage()->getId();
    $query = \Drupal::database()->select('taxonomy_term__field_commerce_id', 'commerce_id');
    $query->fields('commerce_id', ['field_commerce_id_value']);
    $query->condition('langcode', $langcode);
    $query->condition('entity_id', $tids, 'IN');

    return $query->execute()->fetchCol();
  }

  /**
   * Get the required data to output in the CSV file.
   *
   * @param string $tid
   *   Term id.
   * @param string $langcode
   *   Language code.
   */
  private static function getFieldData(string $tid, string $langcode) {
    $data = [];
    /** @var \Drupal\alshaya_acm_product_category\Service\ProductCategoryPage $product_category_page */
    $product_category_page = \Drupal::service('alshaya_acm_product_category.page');
    /** @var \Drupal\taxonomy\TermInterface */
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
    if (empty($term)) {
      return $data;
    }

    if ($term->language()->getId() !== $langcode) {
      /** @var \Drupal\taxonomy\TermInterface */
      $term = $term->getTranslation($langcode);
      if (empty($term)) {
        return $data;
      }
    }
    $term_hierarchy_data = $product_category_page->getCurrentSelectedCategory('en', $tid);

    // Get group category image.
    $group_category_image = $term->get('field_plp_group_category_img')->getValue();
    $image_url = NULL;
    if (!empty($group_category_image) && !empty($group_category_image[0]['target_id'])) {
      $image_file = File::load($group_category_image[0]['target_id'])->getFileUri();
      $image_url = file_create_url($image_file);
    }
    $image_alt_text = !empty($group_category_image) ? $group_category_image[0]['alt'] : NULL;
    // Get sub cat commerce ids.
    $sub_cat_ids = $term->get('field_select_sub_categories_plp')->getValue();
    $sub_cat_commerce_ids = !empty($sub_cat_ids)
      ? self::getCommerceIds(array_column($sub_cat_ids, 'value'))
      : [];
    $data = [
      'title' => $term->getName(),
      'weight' => $term->getWeight(),
      'commerce_id' => $term->get('field_commerce_id')->getString(),
      'group_by_sub_categories' => $term->get('field_group_by_sub_categories')->getString(),
      'sub_categories_plp_tids' => !empty($sub_cat_ids) ? implode(',', array_column($sub_cat_ids, 'value')) : '',
      'sub_categories_plp_commerce_ids' => implode(',', $sub_cat_commerce_ids),
      'plp_group_category_img_url' => $image_url,
      'plp_group_category_img_alt_text' => $image_alt_text,
      'plp_group_category_desc' => $term->get('field_plp_group_category_desc')->getString(),
      'plp_group_category_title' => $term->get('field_plp_group_category_title')->getString(),
      'term_algolia_data' => serialize($term_hierarchy_data),
    ];

    if (self::$isFieldsSet == 0) {
      self::addOutputField('title');
      self::addOutputField('weight');
      self::addOutputField('commerce_id');
      self::addOutputField('group_by_sub_categories');
      self::addOutputField('sub_categories_plp_tids');
      self::addOutputField('sub_categories_plp_commerce_ids');
      self::addOutputField('plp_group_category_img_url');
      self::addOutputField('plp_group_category_img_alt_text');
      self::addOutputField('plp_group_category_desc');
      self::addOutputField('plp_group_category_title');
      self::addOutputField('term_algolia_data');
      self::$isFieldsSet = 1;
    }

    return $data;
  }

  /**
   * Batch operation for deleting product category terms.
   *
   * @param array $tids
   *   Term ids.
   * @param \DrushBatchContext $context
   *   The batch context.
   */
  public static function exportData(array $tids, \DrushBatchContext &$context) {
    // Initialize the results variable.
    if (empty($context['sandbox'])) {
      $context['results']['term_data'] = [];
      foreach (\Drupal::languageManager()->getLanguages() as $langcode => $language) {
        $context['results']['term_data'][$langcode] = [];
      }
    }

    foreach (\Drupal::languageManager()->getLanguages() as $langcode => $language) {
      foreach ($tids as $tid) {
        // Add the main term data to the output.
        $field_data = self::getFieldData($tid, $langcode);
        array_push($context['results']['term_data'][$langcode], $field_data);
        // Add the subcategory data to the output.
        if (!empty($field_data['sub_categories_plp_tids'])) {
          $sub_category_ids = explode(',', $field_data['sub_categories_plp_tids']);

          foreach ($sub_category_ids as $sub_category_id) {
            // We use trim because explode leaves some whitespace.
            $sub_cat_field_data = self::getFieldData($sub_category_id, $langcode);
            array_push($context['results']['term_data'][$langcode], $sub_cat_field_data);
          }
        }
      }

      // Remove duplicates.
      $data_commerce_ids = [];
      $context['results']['term_data'][$langcode] = array_filter($context['results']['term_data'][$langcode], function ($value, $key) use (&$data_commerce_ids, $langcode, $context) {
        if (in_array($value['commerce_id'], $data_commerce_ids)) {
          return FALSE;
        }
        $data_commerce_ids[] = $value['commerce_id'];
        return TRUE;
      }, ARRAY_FILTER_USE_BOTH);
      // Remove empty array rows.
      $context['results']['term_data'][$langcode] = array_values($context['results']['term_data'][$langcode]);
    }
  }

  /**
   * Output the data to file.
   *
   * @param array $data_to_print
   *   The output data.
   * @param string $langcode
   *   The langcode of the file to output.
   */
  public static function outputToFile(array $data_to_print, string $langcode) {
    // Get the destination file name for the given langcode.
    $location = \Drupal::service('file_system')->getDestinationFilename(self::PATH . self::FILE_NAME_PREFIX . '-' . $langcode . '.csv', FileSystemInterface::EXISTS_REPLACE);
    // Open the file to print.
    $file = fopen($location, 'a');
    fputcsv($file, self::getOutputFields());

    $cleaned_data = array_values($data_to_print);
    foreach ($cleaned_data as $row) {
      fputcsv($file, $row);
    }
    // Add the values.
    // Close the file.
    fclose($file);
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
    $logger = \Drupal::logger('alshaya_acm_product_category');

    if ($success) {
      foreach (\Drupal::languageManager()->getLanguages() as $langcode => $language) {
        self::outputToFile($results['term_data'][$langcode], $langcode);

        $logger->notice('@count items successfully processed for @langcode language.', [
          '@count' => is_countable($results['term_data'][$langcode])
          ? count($results['term_data'][$langcode])
          : 0,
          '@langcode' => $langcode,
        ]);
      }
    }
    else {
      $logger->warning('Could not successfully complete the batch process.');
    }
  }

}
