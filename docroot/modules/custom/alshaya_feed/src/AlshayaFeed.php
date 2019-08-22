<?php

namespace Drupal\alshaya_feed;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\node\NodeInterface;

/**
 * Class AlshayaFeed.
 *
 * @package Drupal\alshaya_feed
 */
class AlshayaFeed {

  use StringTranslationTrait;

  /**
   * Entity Type Manager service object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * File system object.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The language manager interface.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The sku info helper service.
   *
   * @var \Drupal\alshaya_feed\SkuInfoHelper
   */
  protected $skuInfoHelper;

  /**
   * The Twig template environment.
   *
   * @var \Drupal\Core\Template\TwigEnvironment
   */
  protected $twig;

  /**
   * AlshayaFeed constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager service object.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   File system object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repository.
   * @param \Drupal\alshaya_feed\SkuInfoHelper $sku_info_helper
   *   The sku info helper service.
   * @param \Drupal\Core\Template\TwigEnvironment $twig_environment
   *   The Twig template environment.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    FileSystemInterface $fileSystem,
    LanguageManagerInterface $language_manager,
    EntityRepositoryInterface $entity_repository,
    SkuInfoHelper $sku_info_helper,
    TwigEnvironment $twig_environment
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->fileSystem = $fileSystem;
    $this->entityRepository = $entity_repository;
    $this->skuInfoHelper = $sku_info_helper;
    $this->twig = $twig_environment;
  }

  /**
   * Query to get the product nodes.
   *
   * @return \Drupal\Core\Database\Query\AlterableInterface
   *   Return a query object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getNodes() {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    return $query->condition('type', 'acq_product')
      ->condition('status', NodeInterface::PUBLISHED)
      ->addTag('get_display_node_for_sku');
  }

  /**
   * Batch process helper method to store context data.
   *
   * @param int $batch_size
   *   The batch size.
   * @param mixed|array $context
   *   The batch current context.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function batchProcess($batch_size, &$context) {
    if (!isset($context['sandbox']['current'])) {
      $context['sandbox']['count'] = 0;
      $context['sandbox']['current'] = 0;
      $context['sandbox']['feed_template'] = drupal_get_path('module', 'alshaya_feed') . '/templates/feed.html.twig';
    }

    $query = $this->getNodes();
    // Get the total amount of items to process.
    if (!isset($context['sandbox']['total'])) {
      $countQuery = clone $query;
      $context['sandbox']['total'] = $countQuery->count()->execute();

      // If there are no entities to update, then stop immediately.
      if (!$context['sandbox']['total']) {
        $context['finished'] = 1;
        return;
      }
    }

    $query->range(0, $batch_size);
    $nids = $query->execute();
    $updates = $this->process($nids, $context);

    $context['sandbox']['count'] += count($nids);
    $context['sandbox']['current'] = !empty($nids) ? current($nids) : 0;
    $context['results']['updates'] += $updates;
    $context['message'] = $this->t('Updated feeds for @count.', ['@count' => $context['sandbox']['count']]);

    if ($context['sandbox']['count'] != $context['sandbox']['total']) {
      $context['finished'] = $context['sandbox']['count'] / $context['sandbox']['total'];
    }
  }

  /**
   * Process given nids and get product related info.
   *
   * @param array $nids
   *   The array of nids to process.
   * @param mixed|array $context
   *   The batch current context.
   *
   * @return int
   *   Return total number of nid processed.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function process(array $nids, &$context) {
    $updates = 0;
    $context['results']['products'] = [];
    foreach ($nids as $nid) {
      $updates++;
      $product = $this->skuInfoHelper->process($nid);
      if (empty($product)) {
        continue;
      }
      foreach ($product as $lang => $item) {
        $context['results']['products'][$lang][] = $this->twig
          ->loadTemplate($context['sandbox']['feed_template'])
          ->render(['product' => $item]);
      }
    }

    foreach ($context['results']['products'] as $lang => $products) {
      $context['results']['markups'][$lang][] = implode("\n", $products);
    }

    return $updates;
  }

  /**
   * Dump products data into xml file.
   *
   * @param mixed|array $context
   *   The batch current context.
   */
  public function dumpXml(&$context) {
    foreach ($context['results']['markups'] as $lang => $markup) {
      $markup = implode("\n", $markup);
      $file_content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<feed>\n<products>{$markup}\n</products>\n</feed>";
      $path = file_create_url($this->fileSystem->realpath(file_default_scheme() . "://feed_{$lang}_wip.xml"));
      file_put_contents($path, $file_content);
    }
  }

  /**
   * Create a feed_langcode_wip.xml file.
   *
   * Clear feed_langcode_wip.xml file if exists, create empty file if not
   * exists. (For all languages.)
   */
  public function clear() {
    foreach ($this->languageManager->getLanguages() as $lang => $language) {
      $wip_file = $this->fileSystem->realpath(file_default_scheme() . "://feed_{$lang}_wip.xml");
      if (file_exists($wip_file)) {
        $this->fileSystem->delete($wip_file);
      }
    }
  }

  /**
   * Publish finally dumped xml file.
   *
   * Delete feed_langcode.xml and rename feed_langcode_wip.xml to
   * feed_langcode.xml (For all languages.)
   */
  public function publish() {
    foreach ($this->languageManager->getLanguages() as $lang => $language) {
      $wip_file = $this->fileSystem->realpath(file_default_scheme() . "://feed_{$lang}_wip.xml");
      if (file_exists($wip_file)) {
        $this->fileSystem->move(
          $wip_file,
          $this->fileSystem->realpath(file_default_scheme() . "://feed_{$lang}.xml"),
          FileSystemInterface::EXISTS_REPLACE
        );
      }
    }
  }

}
