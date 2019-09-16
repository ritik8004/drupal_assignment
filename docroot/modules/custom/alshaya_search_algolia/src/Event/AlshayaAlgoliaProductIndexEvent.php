<?php

namespace Drupal\alshaya_search_algolia\Event;

use Drupal\acq_sku\Entity\SKU;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class AlshayaAlgoliaProductIndexEvent.
 *
 * @package Drupal\alshaya_search_algolia\Event
 */
class AlshayaAlgoliaProductIndexEvent extends Event {

  const PRODUCT_INDEX = 'alshaya_algolia.product_index';

  /**
   * SKU Entity.
   *
   * @var \Drupal\acq_sku\Entity\SKU
   */
  private $sku;


  /**
   * The node object.
   *
   * @var \Drupal\node\NodeInterface
   */
  private $node;

  /**
   * The index item array.
   *
   * @var string
   */
  private $item;

  /**
   * AlshayaAlgoliaProductIndexEvent constructor.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   The sku entity.
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   * @param array $item
   *   The item being indexed.
   */
  public function __construct(SKU $sku, NodeInterface $node, array $item) {
    $this->sku = $sku;
    $this->node = $node;
    $this->item = $item;
  }

  /**
   * Get SKU Entity.
   *
   * @return \Drupal\acq_sku\Entity\SKU
   *   SKU Entity.
   */
  public function getSkuEntity(): SKU {
    return $this->sku;
  }

  /**
   * Get Node object.
   *
   * @return \Drupal\node\NodeInterface
   *   The node object.
   */
  public function getNodeEntity(): NodeInterface {
    return $this->node;
  }

  /**
   * Get performed operation.
   *
   * @return string
   *   Operation performed - update, insert, delete.
   */
  public function getItem(): array {
    return $this->item;
  }

  /**
   * Sets the updated index item.
   *
   * @param array $item
   *   The index item array.
   */
  public function setItem(array $item) {
    $this->item = $item;
  }

}
