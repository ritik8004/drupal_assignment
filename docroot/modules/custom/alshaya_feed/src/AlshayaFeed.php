<?php

namespace Drupal\alshaya_feed;

use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
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
   * SKU Manager service object.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * The Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * SKU images manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImagesManager;

  /**
   * The sku info helper service.
   *
   * @var \Drupal\alshaya_feed\SkuInfoHelper
   */
  protected $skuInfoHelper;

  /**
   * AlshayaFeed constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager service object.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   File system object.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The module handler service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Config Factory service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repository.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU images manager.
   * @param \Drupal\alshaya_feed\SkuInfoHelper $sku_info_helper
   *   The sku info helper service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    FileSystemInterface $fileSystem,
    TranslationInterface $string_translation,
    LanguageManagerInterface $language_manager,
    SkuManager $sku_manager,
    ConfigFactory $configFactory,
    EntityRepositoryInterface $entity_repository,
    SkuImagesManager $sku_images_manager,
    SkuInfoHelper $sku_info_helper
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->fileSystem = $fileSystem;
    $this->skuManager = $sku_manager;
    $this->configFactory = $configFactory;
    $this->entityRepository = $entity_repository;
    $this->skuImagesManager = $sku_images_manager;
    $this->skuInfoHelper = $sku_info_helper;
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
      $context['results']['markup'] = '';
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
    // Load the Twig theme engine so we can use twig_render_template().
    include_once \Drupal::root() . '/core/themes/engines/twig/twig.engine';
    $context['results']['products'] = [];
    foreach ($nids as $nid) {
      $updates++;
      $product = $this->skuInfoHelper->process($nid);
      if (empty($product)) {
        continue;
      }
      $context['results']['products'][] = $product;
    }

    $context['results']['markup'] .= (string) twig_render_template(drupal_get_path('module', 'alshaya_feed') . '/templates/feed.html.twig', [
      'products' => $context['results']['products'],
    ]);

    return $updates;
  }

  /**
   * Dump products data into xml file.
   *
   * @param mixed|array $context
   *   The batch current context.
   */
  public function dumpXml(&$context) {
    $file_content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<feed>{$context['results']['markup']}\n</feed>";
    $path = file_create_url($this->fileSystem->realpath(file_default_scheme() . "://alshaya_feed"));
    $filename = 'feed_en_wip.xml';
    file_put_contents($path . '/' . $filename, $file_content);
  }

  /**
   * Create a feed_langcode_wip.xml file.
   *
   * Clear feed_langcode_wip.xml file if exists, create empty file if not
   * exists. (For all languages.)
   */
  public function clear() {

  }

  /**
   * Publish finally dumped xml file.
   *
   * Delete feed_langcode.xml and rename feed_langcode_wip.xml to
   * feed_langcode.xml (For all languages.)
   */
  public function publish() {

  }

}
