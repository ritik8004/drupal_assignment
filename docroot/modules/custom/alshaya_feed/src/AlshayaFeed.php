<?php

namespace Drupal\alshaya_feed;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactory;
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
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    FileSystemInterface $fileSystem,
    TranslationInterface $string_translation,
    LanguageManagerInterface $language_manager,
    SkuManager $sku_manager,
    ConfigFactory $configFactory
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->fileSystem = $fileSystem;
    $this->skuManager = $sku_manager;
    $this->configFactory = $configFactory;
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
    foreach ($nids as $nid) {
      $node = $this->entityTypeManager->getStorage('node')->load($nid);
      $context['results']['products'][] = [
        'title' => $node->label(),
      ];
      $updates++;
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
    print_r($context['results']['products']);
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
