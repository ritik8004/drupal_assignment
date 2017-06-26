<?php
namespace Drupal\alshaya_acm_product;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
/**
 * Class AlshayaGetProductFields.
 *
 * @package Drupal\alshaya_acm_product
 */
class AlshayaGetProductFields {
  /**
   * Sku entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $skuStorage;
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
    $this->skuStorage = $entity_type_manager->getStorage('acq_sku');
  }
  /**
   * Get Promotion node object(s) related to provided SKU.
   *
   * @param mixed $sku
   *   The SKU ID, for which linked promotions need to be fetched.
   * @param bool $getLinks
   *   Boolen to identify if Links are required.
   *
   * @return array|\Drupal\Core\Entity\EntityInterface[]
   *   blank array, if no promotions found, else Array of promotion entities.
   */
  public function getPromotionsFromSkuId($sku, $getLinks = FALSE) {
    if ($sku instanceof SKU) {
      $sku = $sku->id();
    }
    $promos = [];
    $query = $this->nodeStorage->getQuery();
    $query->condition('type', 'acq_promotion');
    $query->condition('field_acq_promotion_sku', $sku);
    $promotionIDs = $query->execute();
    if (!empty($promotionIDs)) {
      $promotions = Node::loadMultiple($promotionIDs);
      foreach ($promotions as $promotion) {
        /* @var \Drupal\node\Entity\Node $promotion */
        if ($getLinks) {
          $promos[$promotion->id()] = $promotion->toLink($promotion->getTitle())->toString()->getGeneratedLink();
        }
        else {
          $promos[$promotion->id()] = [
            'text' => $promotion->getTitle(),
            'description' => $promotion->get('field_acq_promotion_description')->first()->getValue(),
          ];
        }
      }
    }
    return $promos;
  }
  /**
   * Helper function to fetch Entity id given SKU for a product.
   *
   * @param string $sku
   *   Sku identifier of the product variant.
   *
   * @return int
   *   Entity id of the Sku
   */
  public function getIdFromSku($sku) {
    $query = $this->skuStorage->getQuery();
    $query->condition('sku', $sku);
    $sku_entity_ids = $query->execute();
    return array_shift($sku_entity_ids);
  }
}
