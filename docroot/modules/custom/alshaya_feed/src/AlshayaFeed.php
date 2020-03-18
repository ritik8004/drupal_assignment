<?php

namespace Drupal\alshaya_feed;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerInterface;

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
   * @var \Drupal\alshaya_feed\AlshayaFeedSkuInfoHelper
   */
  protected $feedSkuInfoHelper;

  /**
   * The Twig template environment.
   *
   * @var \Drupal\Core\Template\TwigEnvironment
   */
  protected $twig;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

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
   * @param \Drupal\alshaya_feed\AlshayaFeedSkuInfoHelper $sku_info_helper
   *   The sku info helper service.
   * @param \Drupal\Core\Template\TwigEnvironment $twig_environment
   *   The Twig template environment.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    FileSystemInterface $fileSystem,
    LanguageManagerInterface $language_manager,
    EntityRepositoryInterface $entity_repository,
    AlshayaFeedSkuInfoHelper $sku_info_helper,
    TwigEnvironment $twig_environment,
    LoggerInterface $logger
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->fileSystem = $fileSystem;
    $this->entityRepository = $entity_repository;
    $this->feedSkuInfoHelper = $sku_info_helper;
    $this->twig = $twig_environment;
    $this->logger = $logger;
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
  public function getNodesQuery() {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    return $query->condition('type', 'acq_product')
      ->condition('status', NodeInterface::PUBLISHED)
      ->addTag('get_display_node_for_sku');
  }

  /**
   * Process given nids and get product related info.
   *
   * @param array $nids
   *   The array of nids to process.
   * @param mixed|array $context
   *   The batch current context.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Runtime
   * @throws \Twig_Error_Syntax
   */
  public function process(array $nids, &$context) {
    $context['results']['count'] += count($nids);

    foreach ($nids as $nid) {
      $product = $this->feedSkuInfoHelper->prepareFeedData($nid);
      if (empty($product)) {
        continue;
      }

      foreach ($product as $lang => $items) {
        foreach ($items as $item) {
          $file_content = PHP_EOL;
          if (!isset($context['results']['files'][$lang])) {
            $file_content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<feed>\n<products>" . PHP_EOL;
            $context['results']['files'][$lang] = file_create_url($this->fileSystem->realpath(file_default_scheme() . "://feed_{$lang}_wip.xml"));
          }

          $file_content .= $this->twig
            ->loadTemplate($context['results']['feed_template'])
            ->render(['product' => $item]) . PHP_EOL;

          if (!file_put_contents($context['results']['files'][$lang], $file_content, FILE_APPEND)) {
            $this->logger->error('could not create feed file: @file', ['@file' => $context['results']['files'][$lang]]);
          }
        }
      }
    }

    $context['message'] = $this->t('Updated feeds for @count out of @total.', [
      '@count' => $context['results']['count'],
      '@total' => $context['results']['total'],
    ]);
  }

  /**
   * Dump products data into xml file.
   *
   * @param mixed|array $context
   *   The batch current context.
   */
  public function dumpXml(&$context) {
    foreach ($context['results']['files'] as $path) {
      $file_content = "</products>\n</feed>";
      if (!file_put_contents($path, $file_content, FILE_APPEND)) {
        $this->logger->error('could not create feed file: @file', ['@file' => $path]);
      }
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
      else {
        $this->logger->info('Can not publish a feed file, wip feed file:: @file :: does not exists.', ['@file' => $wip_file]);
      }
    }
  }

}
