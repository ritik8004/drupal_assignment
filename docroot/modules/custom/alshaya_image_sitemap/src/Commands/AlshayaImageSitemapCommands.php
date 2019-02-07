<?php

namespace Drupal\alshaya_image_sitemap\Commands;

use Drupal\alshaya_image_sitemap\AlshayaImageSitemapGenerator;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drush\Commands\DrushCommands;

/**
 * Class AlshayaImageSitemapCommands.
 *
 * @package Drupal\alshaya_image_sitemap\Commands
 */
class AlshayaImageSitemapCommands extends DrushCommands {
  use StringTranslationTrait;

  /**
   * Alshaya image sitemap generator.
   *
   * @var \Drupal\alshaya_image_sitemap\AlshayaImageSitemapGenerator
   */
  private $alshayaImageSitemapGenerator;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * AlshayaImageSitemapCommands constructor.
   *
   * @param \Drupal\alshaya_image_sitemap\AlshayaImageSitemapGenerator $alshayaImageSitemapGenerator
   *   Alshaya image sitemap generator.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger factory.
   */
  public function __construct(AlshayaImageSitemapGenerator $alshayaImageSitemapGenerator,
                              ConfigFactoryInterface $configFactory,
                              LoggerChannelFactoryInterface $loggerChannelFactory) {
    $this->alshayaImageSitemapGenerator = $alshayaImageSitemapGenerator;
    $this->configFactory = $configFactory->get('alshaya_image_sitemap.settings');
    $this->logger = $loggerChannelFactory->get('alshaya_image_sitemap');
  }

  /**
   * Create Image Sitemap.
   *
   * @command alshaya_image_sitemap:generate
   *
   * @aliases cis,create-img-sitemap
   *
   * @usage drush create-img-sitemap
   *   Create image sitemap.
   */
  public function generateImageSitemap() {
    $this->output->writeln('Creating image sitemap.');

    $this->alshayaImageSitemapGenerator->getSitemapReady();

    $batch = [
      'operations' => [],
      'finished' => '\Drupal\alshaya_image_sitemap\Commands\AlshayaImageSitemapCommands::imageSitemapBatchFinishCallback',
      'title' => $this->t('Create Image Sitemap'),
      'init_message' => $this->t('Starting sitemap creation.....'),
      'progress_message' => $this->t('Completed @current step of @total.'),
      'error_message' => $this->t('Sitemap creation has encountered an error.'),
    ];

    $nids = $this->alshayaImageSitemapGenerator->getNodes();
    $batch_size = $this->configFactory->get('image_sitemap_batch_chunk_size');

    $nid_chunks = array_chunk($nids, $batch_size);
    foreach ($nid_chunks as $nid_chunk) {
      $batch['operations'][] = [
        '\Drupal\alshaya_image_sitemap\Commands\AlshayaImageSitemapCommands::imageSitemapBatchProcess',
        [$nid_chunk],
      ];
    }

    // Initialize the batch.
    batch_set($batch);

    // Start the batch process.
    drush_backend_batch_process();
  }

  /**
   * Implements batch start function.
   *
   * @param array $nids
   *   Array of nids.
   */
  public static function imageSitemapBatchProcess(array $nids) {
    \Drupal::service('alshaya_image_sitemap.generator')->process($nids);
  }

  /**
   * Batch finished callback.
   *
   * @param bool $success
   *   Success or fail import.
   * @param array $results
   *   Result array.
   * @param array $operations
   *   Operation array.
   */
  public static function imageSitemapBatchFinishCallback($success, array $results = [], array $operations = []) {
    if ($success) {
      \Drupal::service('alshaya_image_sitemap.generator')
        ->sitemapGenerateFinished();
      \Drupal::state()->set('alshaya_image_sitemap.last_generated', REQUEST_TIME);
      drush_print(dt('Image Sitemap Generated Successfully.'));
    }
    else {
      drush_print(dt('There was some error while importing redirects.'));
    }
  }

}
