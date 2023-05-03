<?php

namespace Drupal\alshaya_acm_promotion\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drush\Commands\DrushCommands;
use Drupal\node\NodeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * A Drush commandfile for exporting promotion data.
 */
class PromotionExportCommand extends DrushCommands {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * File system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Entity Query.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $entityQuery;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

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
  public const FILE_NAME_PREFIX = 'promotion-data';

  /**
   * Constructor for the class.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    FileSystemInterface $file_system,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->entityQuery = $entity_type_manager->getStorage('node')->getQuery();
    $this->languageManager = $language_manager;
    $this->fileSystem = $file_system;
    $this->drupalLogger = $logger_factory->get('alshaya_acm_promotion');
  }

  /**
   * Outputs promotion data to a csv file.
   *
   * @command alshaya_acm_promotion:export-promotion-data
   * @aliases export-promodata
   *
   * @options limit The number of nodes to process per batch.
   *
   * @usage alshaya_acm_promotion:export-promotion-data --limit 50
   *   Process 50 nodes per batch and output to the file. Default is 30.
   */
  public function exportPromotionData($options = ['limit' => 30]) {
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

    $nids = $this->entityQuery
      ->condition('type', 'acq_promotion')
      ->condition('status', NodeInterface::PUBLISHED)
      ->execute();

    $batch = [
      'title' => 'Export Promotion Data',
      'init' => 'Starting processing of promotion data to be exported',
      'finished' => [self::class, 'batchFinished'],
    ];

    foreach (array_chunk($nids, $options['limit']) as $nid_batch) {
      $batch['operations'][] = [
        [
          '\Drupal\alshaya_acm_promotion\Commands\PromotionExportCommand',
          'exportPromotion',
        ],
        [$nid_batch],
      ];
    }

    batch_set($batch);
    drush_backend_batch_process();
  }

  /**
   * Main batch operation which aggregates the data to be exported.
   *
   * @param array $nids
   *   The nids for which data is to be fecthed.
   * @param mixed $context
   *   The batch context.
   */
  public static function exportPromotion(array $nids, &$context) {
    if (!isset($context['results']['nodes'])) {
      $context['results']['nodes'] = 0;
      $context['results']['failed_nids'] = [];
      $context['results']['data'] = [];
      $context['results']['csv_header_added'] = [];
    }

    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    foreach ($nids as $nid) {
      try {
        /** @var \Drupal\node\NodeInterface */
        $node = $node_storage->load($nid);
        foreach ($node->getTranslationLanguages() as $langcode => $language) {
          $translated_node = $node->getTranslation($langcode);

          // Get the required data.
          $node_data = self::getDataForNode($translated_node);

          // Add the header only once.
          if (!isset($context['results']['csv_header_added'][$langcode])) {
            $context['results']['csv_header_added'][$langcode] = TRUE;
            self::outputToFile(array_keys($node_data), $langcode);
          }

          // Add the actual data.
          self::outputToFile($node_data, $langcode);
        }

        $context['results']['nodes']++;
      }
      catch (\Exception) {
        $context['results']['failed_nids'][] = $nid;
      }
    }

    \Drupal::logger('alshaya_acm_promotion')->notice('@count nodes processed for exporting.', ['@count' => count($nids)]);
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
    $logger = \Drupal::logger('alshaya_acm_promotion');

    if ($success) {
      // Here we do something meaningful with the results.
      $logger->notice('@count items successfully processed.', ['@count' => $results['nodes']]);
    }
    else {
      $logger->warning('Could not successfully complete the batch process.');
    }

    if (!empty($results['failed_nids'])) {
      $logger->warning('Could not successfully process @count items. Items: @items', [
        '@count' => is_countable($results['failed_nids']) ? count($results['failed_nids']) : 0,
        '@items' => implode(',', $results['failed_nids']),
      ]);
    }
  }

  /**
   * Get the required data for nodes.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   *
   * @return array
   *   All the required data.
   */
  private static function getDataForNode(NodeInterface $node) {
    $fields = [
      'url_alias',
      'field_acq_promotion_rule_id',
    ];

    $data = [];
    $current_language = NULL;
    /** @var \Drupal\Core\Language\LanguageManagerInterface $language_manager */
    $language_manager = \Drupal::languageManager();
    $current_language = $language_manager->getConfigOverrideLanguage();
    $language_manager->setConfigOverrideLanguage($node->language());

    foreach ($fields as $field) {
      switch ($field) {
        case 'url_alias':
          $url = $node->toUrl()->toString();
          // Only provide the path without langcode and domain.
          $data[$field] = str_replace('/' . $node->language()->getId(), '', $url);
          break;

        default:
          $data[$field] = $node->get($field)->getString();
          break;
      }
    }

    // Revert the override for the language change.
    $language_manager->setConfigOverrideLanguage($current_language);

    return $data;
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
    // Add the values.
    fputcsv($file, $data_to_print);
    // Close the file.
    fclose($file);
  }

}
