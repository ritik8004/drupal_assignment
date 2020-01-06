<?php

namespace Drupal\alshaya_seo\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Url;
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

}
