<?php

namespace Drupal\alshaya_acm_product\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drush\Commands\DrushCommands;
use Drupal\node\NodeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * A Drush commandfile for exporting product data.
 */
class ProductExportCommands extends DrushCommands {

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
  public const FILE_NAME_PREFIX = 'product-data';

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
    $this->drupalLogger = $logger_factory->get('alshaya_acm_product');
  }

  /**
   * Outputs product data to a csv file.
   *
   * @command alshaya_acm_product:export-product-data
   * @aliases expproddata
   *
   * @options limit The number of nodes to process per batch.
   *
   * @usage alshaya_acm_product:export-product-data --limit 50
   *   Process 50 nodes per batch and output to the file. Default is 30.
   */
  public function exportProductData($options = ['limit' => 30]) {
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
      ->condition('type', 'acq_product')
      ->condition('status', NodeInterface::PUBLISHED)
      ->addTag('get_display_node_for_sku')
      ->execute();

    $batch = [
      'title' => 'Export Product Data',
      'init' => 'Starting processing of product data to be exported',
      'finished' => [self::class, 'batchFinished'],
    ];

    foreach (array_chunk($nids, $options['limit']) as $nid_batch) {
      $batch['operations'][] = [
        [
          '\Drupal\alshaya_acm_product\Commands\ProductExportCommands',
          'exportProduct',
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
  public static function exportProduct(array $nids, &$context) {
    if (!isset($context['results']['nodes'])) {
      $context['results']['nodes'] = 0;
      $context['results']['failed_nids'] = [];
      $context['results']['data'] = [];
      $context['results']['csv_header_added'] = [];
    }

    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $sku_manager = \Drupal::service('alshaya_acm_product.skumanager');

    foreach ($nids as $nid) {
      try {
        /** @var \Drupal\node\NodeInterface */
        $node = $node_storage->load($nid);
        foreach ($node->getTranslationLanguages() as $langcode => $language) {
          $translated_node = $node->getTranslation($langcode);

          // Get the required data.
          $node_data = self::getDataForNode($translated_node);

          // We want the sku to be the first column of the table.
          $main_sku = $sku_manager->getSkuForNode($translated_node);
          $first_data = ['sku' => $main_sku];
          $data = array_merge($first_data, $node_data);

          // Add the header only once.
          if (!isset($context['results']['csv_header_added'][$langcode])) {
            $context['results']['csv_header_added'][$langcode] = TRUE;
            self::outputToFile(array_keys($data), $langcode);
          }

          // Add the actual data.
          self::outputToFile($data, $langcode);
        }

        $context['results']['nodes']++;
      }
      catch (\Exception) {
        $context['results']['failed_nids'][] = $nid;
      }
    }

    \Drupal::logger('alshaya_acm_product')->notice('@count nodes processed for exporting.', ['@count' => count($nids)]);
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
    $logger = \Drupal::logger('alshaya_acm_product');

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
      'store_view_code',
      'url_key',
      'field_meta_tags',
    ];

    $data = [];
    $current_language = NULL;
    /** @var \Drupal\Core\Language\LanguageManagerInterface $language_manager */
    $language_manager = \Drupal::languageManager();
    $current_language = $language_manager->getConfigOverrideLanguage();
    $language_manager->setConfigOverrideLanguage($node->language());
    $node_langcode = $node->language()->getId();

    foreach ($fields as $field) {
      switch ($field) {
        case 'store_view_code':
          $data[$field] = self::getStoreCode($node_langcode);
          break;

        case 'url_key':
          $url = $node->toUrl()->toString();
          // Only provide the path without langcode and domain and .html.
          $data[$field] = str_replace(["/$node_langcode/", '.html'], ['', ''], $url);
          break;

        case 'field_meta_tags':
          $field_data = self::getMetaTags($node);
          foreach ($field_data as $tag => $value) {
            $data[$tag] = $value;
          }
          break;
      }
    }

    // Revert the override for the language change.
    $language_manager->setConfigOverrideLanguage($current_language);

    return $data;
  }

  /**
   * Get the list of metatag values for the given node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   *
   * @return array
   *   Array of metatag values with the tag name as key and value as value.
   */
  private static function getMetatags(NodeInterface $node) {
    $metatag_manager = \Drupal::service('metatag.manager');

    // Get the metatags for the entity.
    $tags = $metatag_manager->tagsFromEntityWithDefaults($node);

    // Allow modules to override tags or the entity used for token replacements.
    $context = ['entity' => &$node];
    \Drupal::moduleHandler()->alter('metatags', $tags, $context);

    // If the entity was changed above, use that for generating the meta tags.
    if (isset($context['entity'])) {
      $node = $context['entity'];
    }

    self::removeNonEssentialTags($tags);

    // Generate the actual meta tag values.
    $tags = $metatag_manager->generateRawElements($tags, $node);

    $return = [];

    // Process the tags to a more suitable format.
    foreach ($tags as $key => $tag) {
      switch ($tag['#tag']) {
        case 'link':
          $href = $tag['#attributes']['href'];
          // No need to include alternate metadata.
          if ($tag['#attributes']['rel'] === 'alternate') {
            break;
          }
          if ($tag['#attributes']['rel'] === 'canonical') {
            $href = self::convertAbsoluteToRelativeUrl($tag['#attributes']['href'], $node->language()->getId());
          }

          $return[$tag['#attributes']['rel']] = $href;
          break;

        case 'meta':
          // Key can be one of the following: name or property.
          $key = $tag['#attributes']['name'] ?? $tag['#attributes']['property'];
          $value = $tag['#attributes']['content'];

          // Magento cannot import description text > 255 chars. Also the
          // SEO team recommended to keep it restricted to 250 chars.
          if ($key === 'description') {
            $value = mb_substr($value, 0, 250);
          }
          // We append "meta_" to the key as it is the requirement for the
          // Magento side import.
          $return["meta_$key"] = $value;
          break;
      }
    }

    return $return;
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

  /**
   * Returns the relative url for provided absolute url without the langcode.
   *
   * @param string $url
   *   The url.
   * @param string $langcode
   *   The langcode.
   *
   * @return string
   *   The relative url without langcode.
   */
  public static function convertAbsoluteToRelativeUrl(string $url, string $langcode) {
    static $request_stack = NULL;
    if (!$request_stack) {
      $request_stack = \Drupal::requestStack()->getCurrentRequest()->getSchemeAndHttpHost();
    }

    return str_replace(
      $request_stack . '/' . $langcode,
      '',
      $url
    );
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
