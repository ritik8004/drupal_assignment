<?php

namespace Drupal\alshaya_acm_product\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drush\Commands\DrushCommands;
use Drupal\node\NodeInterface;
use Drupal\Core\Language\LanguageManagerInterface;

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
  protected $langugageManager;

  /**
   * File system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The export directory path.
   *
   * @var string
   */
  const PATH = 'public://exports/v2/';

  /**
   * The filename prefix for the output file.
   *
   * @var string
   */
  const FILE_NAME_PREFIX = 'product-data';

  /**
   * Constructor for the class.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    FileSystemInterface $file_system
  ) {
    $this->entityQuery = $entity_type_manager->getStorage('node')->getQuery();
    $this->languageManager = $language_manager;
    $this->fileSystem = $file_system;
  }

  /**
   * Outputs some product data to a csv file.
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
      $this->logger->notice('Could not read/create the directory to export the data.');
      return;
    }

    // Check if it is possible to create the output files.
    foreach ($this->languageManager->getLanguages() as $langcode => $language) {
      $location = $this->fileSystem->getDestinationFilename($path . '/' . self::FILE_NAME_PREFIX . '-' . $langcode . '.csv', FileSystemInterface::EXISTS_REPLACE);
      if ($location === FALSE) {
        $this->logger->notice('Could not create the file to export the data.');
        return;
      }
    }

    $nids = $this->entityQuery
      ->condition('type', 'acq_product')
      ->condition('status', NodeInterface::PUBLISHED)
      ->addTag('get_display_node_for_sku')
      ->range(0, 60)
      ->execute();

    $batch = [
      'title' => 'Export Product Data',
      'init' => 'Starting processing of product data to be exported',
      'finished' => [__CLASS__, 'batchFinished'],
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

          // Store the data in the context.
          $context['results']['data'][$langcode][] = $data;
        }

        $context['results']['nodes']++;
      }
      catch (\Exception $e) {
        $context['results']['failed_nids'][] = $nid;
      }
    }
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
      foreach (\Drupal::languageManager()->getLanguages() as $langcode => $lang) {
        // Get the destination file name for the given langcode.
        $location = \Drupal::service('file_system')->getDestinationFilename(self::PATH . '/' . self::FILE_NAME_PREFIX . '-' . $langcode . '.csv', FileSystemInterface::EXISTS_REPLACE);

        $file = fopen($location, 'wb');
        // Add the header.
        fputcsv($file, array_keys($results['data'][$langcode][0]));
        // Add the values.
        foreach ($results['data'][$langcode] as $data) {
          fputcsv($file, array_values($data));
        }
        // Close the file.
        fclose($file);
      }

      // Here we do something meaningful with the results.
      $logger->notice('@count items successfully processed.', ['@count' => $results['nodes']]);
    }
    else {
      $logger->warning('Could not successfully complete the batch process.');
    }

    if (!empty($results['failed_nids'])) {
      $logger->warning('Could not successfully process @count items. Items: @items', [
        '@count' => count($results['failed_nids']),
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
      'field_meta_tags',
    ];

    $data = [];

    foreach ($fields as $field) {
      switch ($field) {
        case 'url_alias':
          $url = $node->toUrl()->toString();
          // Only provide the path without langcode and domain.
          $data[$field] = str_replace('/' . $node->language()->getId(), '', $url);
          break;

        case 'field_meta_tags':
          $field_data = self::getMetaTags($node);
          foreach ($field_data as $tag => $value) {
            $data[$tag] = $value;
          }
          break;
      }
    }

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

    // Generate the actual meta tag values.
    $tags = $metatag_manager->generateRawElements($tags, $node);

    $return = [];
    $request_stack = \Drupal::requestStack()->getCurrentRequest()->getSchemeAndHttpHost();

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
            // Convert to relative url.
            $href = str_replace(
              $request_stack . '/' . $node->language()->getId(),
              '',
              $tag['#attributes']['href']
            );
          }

          $return[$tag['#attributes']['rel']] = $href;
          break;

        case 'meta':
          // Key can be one of the following: name or property.
          $key = isset($tag['#attributes']['name']) ? $tag['#attributes']['name'] : $tag['#attributes']['property'];
          $return[$key] = $tag['#attributes']['content'];
          break;
      }
    }

    return $return;
  }

}
