<?php

namespace Drupal\alshaya_acm_product\EventSubscriber;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\alshaya_acm_product\Commands\AlshayaAcmProductCommands;
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
   * ProcessFinishEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(SkuManager $sku_manager,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->skuManager = $sku_manager;
    $this->entityTypeManager = $entity_type_manager;
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
    if ($this->skuManager->isListingModeNonAggregated()) {
      $this->processSkuColorNodes();
    }
  }

  /**
   * Mark the color nodes for sku for re-indexing after each drush command.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   Event object.
   */
  public function postDrushCommand(Event $event) {
    if ($this->skuManager->isListingModeNonAggregated()) {
      $this->processSkuColorNodes();
    }
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

}
