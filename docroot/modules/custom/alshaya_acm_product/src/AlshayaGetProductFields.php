<?php

namespace Drupal\alshaya_acm_product;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;

/**
 * Class AlshayaGetProductFields.
 *
 * @package Drupal\alshaya_acm_product
 */
class AlshayaGetProductFields {

  /**
   * Node Entity Storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  private $nodeStorage;

  /**
   * AlshayaGetProductFields constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
  }

  /**
   * Get Promotion node object(s) related to provided SKU.
   *
   * @param string $skuID
   *   The SKU ID, for which linked promotions need to be fetched.
   *
   * @return array|\Drupal\Core\Entity\EntityInterface[]
   *   blank array, if no promotions found, else Array of promotion entities.
   */
  public function getPromotionsFromSkuId($skuID) {
    $query = $this->nodeStorage->getQuery();
    $query->condition('type', 'acq_promotion');
    $query->condition('field_acq_promotion_sku', $skuID);
    $promotionIDs = $query->execute();

    if (!empty($promotionIDs)) {
      $promotions = Node::loadMultiple($promotionIDs);

      $links = [];
      foreach ($promotions as $promotion) {
        /* @var \Drupal\node\Entity\Node $promotion */
        $links[] = $promotion->toLink($promotion->getTitle())->toString()->getGeneratedLink();
      }
      return $links;
    }

    return [];
  }

}
