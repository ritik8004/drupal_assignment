<?php

namespace Drupal\alshaya_image_sitemap\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_image_sitemap\AlshayaImageSitemapGenerator;
use Drupal\Core\State\StateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class AlshayaImageSitemapController.
 *
 * @package Drupal\alshaya_image_sitemap\Controller
 */
class AlshayaImageSitemapController extends ControllerBase {

  /**
   * Drupal\Core\State\StateInterface definition.
   *
   * @var Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Drupal\alshaya_image_sitemap\AlshayaImageSitemapGenerator.
   *
   * @var Drupal\alshaya_image_sitemap\AlshayaImageSitemapGenerator
   */
  protected $generator;

  /**
   * {@inheritdoc}
   */
  public function __construct(StateInterface $state, AlshayaImageSitemapGenerator $generator) {
    $this->state = $state;
    $this->generator = $generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('alshaya_image_sitemap.generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getImageSitemapUrl() {
    return new RedirectResponse(file_create_url('public://alshaya_image_sitemap/image_sitemap.xml'));
  }

  /**
   * {@inheritdoc}
   */
  public function listImageSitemap() {
    $header = [
      $this->t('SITEMAP URL'),
      $this->t('CREATED DATE'),
      $this->t('TOTAL LINKS'),
      $this->t('ACTIONS'),
    ];

    $rows = [];
    $url = 'public://alshaya_image_sitemap/image_sitemap.xml';
    $url = file_create_url($url);

    // Rows of table.
    $image_sitemap_created = $this->state->get('alshaya_image_sitemap.last_generated');
    $image_sitemap_number_of_urls = $this->state->get('alshaya_image_sitemap.url_count');
    if (isset($image_sitemap_created) && isset($image_sitemap_number_of_urls)) {
      $rows[] = [
        Link::fromTextAndUrl($url, Url::fromUri($url)),
        date('d-M-Y ', $image_sitemap_created),
        $image_sitemap_number_of_urls,
        Link::fromTextAndUrl($this->t('Re-generate'), Url::fromRoute('alshaya_image_sitemap.alshaya_image_sitemap_batch_controller_generate'))
          ->toString(),
      ];
    }

    return [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => Link::fromTextAndUrl($this->t('Add a new image sitemap'), Url::fromRoute('alshaya_image_sitemap.alshaya_image_sitemap_batch_controller_generate')),
    ];
  }

  /**
   * Generate site map.
   */
  public function generate() {
    $this->generator->getSitemapReady();
    $batch = [
      'operations' => [],
      'finished' => [__CLASS__, 'batchFinishedCallback'],
      'title' => $this->t('Create Image Sitemap'),
      'init_message' => $this->t('Starting sitemap creation.....'),
      'progress_message' => $this->t('Completed @current step of @total.'),
      'error_message' => $this->t('Sitemap creation has encountered an error.'),
    ];

    $nids = $this->generator->getNodes();
    $batch_size = $this->config('alshaya_image_sitemap.settings')->get('image_sitemap_batch_chunk_size');
    $nid_chunks = array_chunk($nids, $batch_size);
    foreach ($nid_chunks as $nid_chunk) {
      $batch['operations'][] = [
        [__CLASS__, 'batchStartCallback'],
        [$nid_chunk],
      ];
    }

    // Initialize the batch.
    batch_set($batch);

    return batch_process('admin/config/search/alshaya_image_sitemap');
  }

  /**
   * Batch start callback.
   *
   * @param array $nids
   *   Array of nids.
   * @codingStandardsIgnoreStart
   */
  public static function batchStartCallback(array $nids) {
    \Drupal::service('alshaya_image_sitemap.generator')->process($nids);
    // @codingStandardsIgnoreEnd
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
   * @codingStandardsIgnoreStart
   */
  public static function batchFinishedCallback($success, array $results = [], array $operations = []) {
    if ($success) {
      \Drupal::service('alshaya_image_sitemap.generator')
        ->sitemapGenerateFinished();
      \Drupal::state()->set('alshaya_image_sitemap.last_generated', REQUEST_TIME);
      // @codingStandardsIgnoreEnd
      drupal_set_message(t('Image Sitemap Generated Successfully.'), 'success');
    }
    else {
      drupal_set_message(t('There was some error while importing redirects.'), 'error');
    }
  }

}
