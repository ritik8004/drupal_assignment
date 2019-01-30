<?php

namespace Drupal\alshaya_acm_product\EventSubscriber;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\alshaya_acm_product\Commands\AlshayaAcmProductCommands;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ProcessFinishEventSubscriber.
 *
 * @package Drupal\alshaya_acm_product\EventSubscriber
 */
class ProcessFinishEventSubscriber implements EventSubscriberInterface {

  /**
   * Contains configurable skus on update/add/delete.
   *
   * @var array
   *   Array of configurable skus.
   *
   * @see _alshaya_acm_product_post_sku_operation().
   */
  public static $colorNodeSkus = [];

  /**
   * Contains color node nids those will be deleted.
   *
   * @var array
   *   Array of color node nids.
   *
   * @see alshaya_acm_product_node_delete().
   * @see \Drupal\alshaya_acm_product\SkuManager::processColorNodesForConfigurable().
   */
  public static $colorNodesToBeDeleted = [];

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * ProcessFinishEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   */
  public function __construct(SkuManager $sku_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->skuManager = $sku_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('ProcessFinishEventSubscriber');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::TERMINATE][] = ['onKernelTerminate', 200];
    $events[AlshayaAcmProductCommands::POST_DRUSH_COMMAND_EVENT][] = ['postDrushCommand', 200];
    return $events;
  }

  /**
   * Mark the color nodes for sku for re-indexing.
   *
   * For any configurable sku edit/deleted/create operation, mark all the
   * color nodes for indexing for that SKU.
   *
   * @param \Symfony\Component\HttpKernel\Event\PostResponseEvent $event
   *   Event object.
   */
  public function onKernelTerminate(PostResponseEvent $event) {
    // Deleting the color node.
    $this->deleteColorNodes();
    // Process search index for the color nodes of the skus.
    $this->processSkuColorNodes();
  }

  /**
   * Mark the color nodes for sku for re-indexing after each drush command.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   Event object.
   */
  public function postDrushCommand(Event $event) {
    $this->deleteColorNodes();
    $this->processSkuColorNodes();
  }

  /**
   * Mark color nodes of the configurable skus for indexing.
   */
  protected function processSkuColorNodes() {
    foreach (self::$colorNodeSkus as $sku) {
      if (!empty($color_nodes = $this->skuManager->getColorNodeIds($sku))) {
        foreach ($color_nodes as $nid) {
          $node = $this->entityTypeManager->getStorage('node')->load($nid);
          if ($node instanceof NodeInterface) {
            $node->original = clone $node;
            // Mark color node for indexing.
            search_api_entity_update($node);
          }
        }
      }
    }
  }

  /**
   * Deletes the color nodes.
   */
  protected function deleteColorNodes() {
    if (!empty(self::$colorNodesToBeDeleted)) {
      try {
        $storage = $this->entityTypeManager->getStorage('node');
        $color_nodes = $storage->loadMultiple(self::$colorNodesToBeDeleted);
        $storage->delete($color_nodes);
        $this->logger->notice('Color nodes:@color_nids deleted successfully in method: @method.', [
          '@color_nids' => implode(',', self::$colorNodesToBeDeleted),
          '@method' => 'ProcessFinishEventSubscriber::deleteColorNodes()',
        ]);
      }
      catch (\Exception $e) {
        $this->logger->error('Error while deleting color nodes: @nids in method: @method. Message: @message.', [
          '@nids' => implode(',', self::$colorNodesToBeDeleted),
          '@message' => $e->getMessage(),
          '@method' => 'ProcessFinishEventSubscriber::deleteColorNodes()',
        ]);
      }
    }
  }

}
