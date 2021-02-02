<?php

namespace Drupal\alshaya_algolia_react\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\alshaya_acm_product\Service\ProductQueueUtility;
use Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyAliases;
use Drupal\alshaya_algolia_react\Event\ToggleAlgoliaProductListEvent;
use Drupal\alshaya_search_algolia\Service\AlshayaAlgoliaIndexHelper;
use Drupal\block\BlockInterface;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drush\Commands\DrushCommands;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Algolia Commands.
 *
 * Enable/Disable alogolia block for plps.
 *
 * @package Drupal\alshaya_algolia_react\Commands
 */
class AlshayaAlgoliaReactCommands extends DrushCommands {

  const FACET_SOURCE_PLP = 'search_api:views_block__alshaya_product_list__block_1';
  const FACET_SOURCE_PROMOTION = 'search_api:views_block__alshaya_product_list__block_2';

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The facet manger.
   *
   * @var \Drupal\facets\FacetManager\DefaultFacetManager
   */
  protected $facetManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The algolia index helper.
   *
   * @var \Drupal\alshaya_search_algolia\Service\AlshayaAlgoliaIndexHelper
   */
  protected $algoliaIndexHelper;

  /**
   * Utility to queue products for processing.
   *
   * @var \Drupal\alshaya_acm_product\Service\ProductQueueUtility
   */
  protected $queueUtility;

  /**
   * Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * AlshayaAlgoliaReactCommands constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger Factory.
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facet_manager
   *   Facet manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\alshaya_search_algolia\Service\AlshayaAlgoliaIndexHelper $algolia_index_helper
   *   The algolia index helper.
   * @param \Drupal\alshaya_acm_product\Service\ProductQueueUtility $queue_utility
   *   The produce queue utility.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event Dispatcher.
   */
  public function __construct(
    LoggerChannelFactoryInterface $loggerChannelFactory,
    DefaultFacetManager $facet_manager,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
    Connection $database,
    AlshayaAlgoliaIndexHelper $algolia_index_helper,
    ProductQueueUtility $queue_utility,
    EventDispatcherInterface $dispatcher
  ) {
    $this->logger = $loggerChannelFactory->get('alshaya_algolia_react');
    $this->facetManager = $facet_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->connection = $database;
    $this->algoliaIndexHelper = $algolia_index_helper;
    $this->queueUtility = $queue_utility;
    $this->dispatcher = $dispatcher;
  }

  /**
   * Toggle algolia status for plp.
   *
   * @param string $algolia_plp_status
   *   Status of block.
   *
   * @command alshaya_algolia_react:plp
   *
   * @option $algolia_plp_status
   *   Status of block. ('enable' to enable the algolia for plp else disable.)
   *
   * @aliases alshaya-algolia-react-plp
   *
   * @usage drush alshaya-algolia-react-plp enable
   *   Enable algolia for plp.
   * @usage drush alshaya-algolia-react-plp disable
   *   Disable algolia for plp and activate db.
   */
  public function toggleAlgoliaPlp($algolia_plp_status = 'enable') {
    $index_storage = $this->entityTypeManager->getStorage('search_api_index');
    $block_storage = $this->entityTypeManager->getStorage('block');

    /** @var \Drupal\search_api\Entity\Index $index */
    $index = $index_storage->load('product');

    // Store the status in variable to use it in conditions.
    $enable = ($algolia_plp_status === 'enable');

    if (!($index->status()) && $enable) {
      $this->logger->warning('PLP move to Algolia is already done once.');
    }

    $sources = [
      self::FACET_SOURCE_PLP,
      self::FACET_SOURCE_PROMOTION,
    ];

    foreach ($sources as $source) {
      $facets = $this->facetManager->getFacetsByFacetSourceId($source);

      foreach ($facets as $facet) {
        $block_id = str_replace('_', '', $facet->id());
        $facet_block = $block_storage->load($block_id);
        if ($facet_block instanceof BlockInterface) {
          $facet_block->setStatus(!$enable);
          $facet_block->save();
        }
      }
    }

    $other_blocks = [
      'exposedformalshaya_product_listblock_1',
      'alshaya_plp_facets_block_all',
      'alshayagridcountblock_plp',
      'categoryfacetplp',
      'plpcategoryfacet',
      'views_block__alshaya_product_list_block_1',
      'filterbarplp',
      'exposedformalshaya_product_listblock_2',
      'alshaya_promo_facets_block_all',
      'alshayagridcountblock_promo',
      'views_block__alshaya_product_list_block_2',
      'filterbarpromotions',
    ];

    foreach ($other_blocks as $other_plp_block) {
      $block = $block_storage->load($other_plp_block);
      if ($block instanceof BlockInterface) {
        $block->setStatus(!$enable);
        $block->save();
      }
    }

    // Update status of PLP block.
    $plp_block = $block_storage->load('alshaya_algolia_react_plp');
    if ($plp_block instanceof BlockInterface) {
      $plp_block->setStatus($enable);
      $plp_block->save();
    }

    // Update status of Promotion block.
    $promotion_block = $block_storage->load('alshayaalgoliareactpromotion');
    if ($promotion_block instanceof BlockInterface) {
      $promotion_block->setStatus($enable);
      $promotion_block->save();
    }

    // Update status of sub category block.
    $sub_category_block = $block_storage->load('subcategoryblock');
    if ($sub_category_block instanceof BlockInterface) {
      $sub_category_block->setStatus(!$enable);
      $sub_category_block->save();
    }

    // Truncate alias table as we are going to update aliases for algolia plp.
    $this->connection->truncate(AlshayaFacetsPrettyAliases::ALIAS_TABLE)->execute();

    // Dispatch event so action can be taken to eanble/disable
    // product list algolia block.
    $this->dispatcher->dispatch(ToggleAlgoliaProductListEvent::EVENT_NAME, new ToggleAlgoliaProductListEvent($enable));

    // Update database index status.
    $index->setStatus(!$enable);
    $index->save();

    // Re-index all products again for "lhn_category" & "promotion_nid".
    $this->queueUtility->queueAllProducts();
  }

}
