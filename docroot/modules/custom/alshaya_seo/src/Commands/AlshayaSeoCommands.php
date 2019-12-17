<?php

namespace Drupal\alshaya_seo\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\redirect\Entity\Redirect;
use Drupal\redirect\RedirectRepository;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;

/**
 * Class AlshayaSeoCommands.
 *
 * @package Drupal\alshaya_seo\Commands
 */
class AlshayaSeoCommands extends DrushCommands {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Redirect repository.
   *
   * @var \Drupal\redirect\RedirectRepository
   */
  private $redirectRepository;

  /**
   * Static reference to logger object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected static $loggerStatic;

  /**
   * AlshayaSeoCommands constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\redirect\RedirectRepository $redirectRepository
   *   Redirect repository.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger channel factory.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager,
                              RedirectRepository $redirectRepository,
                              LoggerChannelFactoryInterface $loggerChannelFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->redirectRepository = $redirectRepository;
    $this->logger = $loggerChannelFactory->get('alshaya_seo');
    self::$loggerStatic = $loggerChannelFactory->get('alshaya_seo');
  }

  /**
   * Creates redirect rules for categories arabic URLs.
   *
   * @command alshaya_seo:redirect-arabic-categories
   *
   * @aliases rac,redirect-arabic-categories
   */
  public function redirectArabicCategories() {

    $this->output->writeln('Creating redirect rules for arabic Product categories URLs...');

    $vid = 'acq_product_category';
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vid);
    foreach ($terms as $term) {
      $path = Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->tid])->toString();

      // Redirect storage.
      $source_url = str_replace('/en/', '', $path);
      $destination_url = 'entity:taxonomy_term/' . $term->tid;
      try {

        // Check if redirect already exists.
        $redirect_exists = $this->redirectRepository->findMatchingRedirect($source_url, [], 'ar');
        if ($redirect_exists) {
          $this->output->writeln(dt('Redirect rule already exists for : /ar/@url', [
            '@url' => $source_url,
          ]));
          continue;
        }
        // Create redirect for the path.
        $redirect_entity = [
          'redirect_source' => $source_url,
          'redirect_redirect' => $destination_url,
          'status_code' => '301',
          'language' => 'ar',
        ];

        $new_redirect = Redirect::create($redirect_entity);
        $new_redirect->save();
        $this->output->writeln(dt('Created redirect rule for : /ar/@url', [
          '@url' => $source_url,
        ]));
      }
      catch (\Exception $e) {
        // If any exception.
        $this->logger->error(dt('There was some problem in adding redirect for the url @url. Please check if redirect already exists or not.', ['@url' => $source_url]));
      }
    }

    $this->output->writeln('Done creating redirect rules');
  }

  /**
   * Creates bulk redirects.
   *
   * @param string $file
   *   Path to the csv file.
   *
   * @throws \Drush\Exceptions\UserAbortException
   *
   * @command alshaya_seo:bulk-redirect-import
   *
   * @aliases brc,bulk-redirect-import
   */
  public function bulkImportRedirects($file) {
    if (empty($file)) {
      $this->output->writeln('Please provide a valid file path');
      throw new UserAbortException();
    }
    elseif (!is_file($file)) {
      $this->output->writeln("File not found. Make sure you specified the correct path.");
      throw new UserAbortException();
    }
    elseif (!$this->io()->confirm(dt("Are you sure you want to import the URL Redirects? Please make sure the csv doesn't have a header."))) {
      throw new UserAbortException();
    }

    $redirects_created_count = 0;
    $this->output->writeln('Importing Redirects...');
    if ($handle = fopen($file, 'r')) {
      while ($data = fgetcsv($handle, NULL, "\r")) {
        foreach ($data as $d) {
          $value = explode(',', $d);
          if (empty($value[2])) {
            $value[2] = 'und';
          }
          $redirect_exists = $this->redirectRepository->findMatchingRedirect($value[0], [], $value[2]);

          // Check if the redirect already exists.
          if ($redirect_exists) {
            $this->output->writeln(dt('Redirect rule already exists for language :@lang and source path @url', [
              '@lang' => $value[2],
              '@url' => $value[0],
            ]));
            continue;
          }
          else {
            // Create redirect for the path.
            $redirect_entity = [
              'redirect_source' => $value[0],
              'redirect_redirect' => 'internal:/' . $value[1],
              'status_code' => '301',
              'language' => $value[2],
            ];
            $new_redirect = Redirect::create($redirect_entity);
            $new_redirect->save();
            $redirects_created_count++;
          }
        }
      }
      $this->output->writeln(dt('@count redirects created.', [
        '@count' => $redirects_created_count,
      ]));
    }
  }

  /**
   * Reset sitemap index based on l1 category variants.
   *
   * @param string $entity_type
   *   The entity type.
   * @param array $options
   *   (optional) An array of options.
   *
   * @command alshaya_seo:resetIndex
   *
   * @aliases rs, reset-sitemap-index
   *
   * @option batch-size
   *   The number of items to generate/process per batch run.
   *
   * @usage drush reset-sitemap
   *   Reset Sitemap based on l1 variants.
   * @usage drush reset-sitemap-index node --batch-size=200
   *   Reset sitemap index based on l1 category variants with batch of 200.
   * @usage drush reset-sitemap-index taxonomy_term --batch-size=200
   *   Reset sitemap index based on l1 category variants with batch of 200.
   */
  public function resetSitemapIndex(string $entity_type, array $options = ['batch-size' => NULL]) {
    $this->output->writeln('Re-setting sitemap indexation based on L1 category variants.');

    if (empty($entity_type)) {
      $this->logger->error(dt('Entity type i.e. node/taxonomy is missing as argument.'));
      return;
    }

    $batch = [
      'operations' => [],
      'init_message' => dt('Starting sitemap index reset.....'),
      'progress_message' => dt('Completed @current step of @total.'),
      'error_message' => dt('Sitemap index resetting has encountered an error.'),
    ];

    if ($entity_type == 'node') {
      $entity_ids = $this->getNodes();
    }
    elseif ($entity_type == 'taxonomy_term') {
      $entity_ids = $this->getCategries();
    }
    else {
      $this->logger->error(dt('Entity type: @entity_type is invalid - Please use either node or taxonomy_term', [
        '@entity_type' => $entity_type,
      ]));
      return;
    }

    foreach (array_chunk($entity_ids, $options['batch-size']) as $chunk) {
      $batch['operations'][] = [
        [__CLASS__, 'resetSitemapBatchProcess'],
        [$entity_type, $chunk],
      ];
    }

    // Initialize the batch.
    batch_set($batch);

    // Process the batch.
    drush_backend_batch_process();

    $this->logger->notice(dt('Process finished'));
  }

  /**
   * Get all published product nodes.
   */
  public function getNodes() {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();

    return $query->condition('type', 'acq_product')
      ->condition('status', NodeInterface::PUBLISHED)
      // Add tag to ensure this can be altered easily in custom modules.
      ->addTag('get_display_node_for_sku')
      ->execute();
  }

  /**
   * Get all product categories.
   */
  public function getCategries() {
    $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', 'acq_product_category');

    return $query->execute();
  }

  /**
   * Implements batch start function.
   *
   * @param string $entity_type
   *   The entity type.
   * @param array $entity_ids
   *   Array of entity_ids.
   */
  public static function resetSitemapBatchProcess($entity_type, array $entity_ids) {
    if (empty($entity_ids)) {
      return;
    }

    $sitemap = \Drupal::service('alshaya_seo_transac.alshaya_sitemap_manager');

    if ($entity_type == 'taxonomy_term') {
      foreach ($entity_ids as $entity_id) {
        $term = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term')->load($entity_id);
        $sitemap->acqProductCategoryOperation($entity_id, $entity_type, $term->get('field_commerce_status')->getString());
      }
    }
    elseif ($entity_type == 'node') {
      foreach ($entity_ids as $entity_id) {
        $sitemap->acqProductOperation($entity_id, $entity_type);
      }
    }

    self::$loggerStatic->notice(dt('Reseting sitemap index based on L1 type: @entity_type,  @items:', [
      '@items' => json_encode($entity_ids),
      '@entity_type' => json_encode($entity_type),
    ]));
  }

}
