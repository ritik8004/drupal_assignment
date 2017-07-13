<?php

namespace Drupal\alshaya_acm_product;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;

/**
 * Class SkuManager.
 *
 * @package Drupal\alshaya_acm_product
 */
class SkuManager {

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $connection;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The Entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * SkuManager constructor.
   *
   * @param \Drupal\Core\Database\Driver\mysql\Connection $connection
   *   Database service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   The lnaguage manager service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository service.
   */
  public function __construct(Connection $connection,
                              EntityTypeManagerInterface $entity_type_manager,
                              LanguageManager $languageManager,
                              EntityRepositoryInterface $entityRepository) {
    $this->connection = $connection;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->skuStorage = $entity_type_manager->getStorage('acq_sku');
    $this->languageManager = $languageManager;
    $this->entityRepository = $entityRepository;
  }

  /**
   * Utility function to return media files for a SKU.
   *
   * @param mixed $sku
   *   SKU text or full entity object.
   * @param bool $first_image_only
   *   Flag to indicate if we want only the first image and not the whole array.
   *
   * @return array
   *   Array of media files.
   */
  public function getSkuMedia($sku, $first_image_only = FALSE) {
    $media = [];

    $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);

    if (!($sku_entity instanceof SKU)) {
      return [];
    }

    if ($first_image_only) {
      return $sku_entity->getThumbnail();
    }

    return $sku_entity->getMedia();
  }

  /**
   * Get Image tag from media item array.
   *
   * @param array $media
   *   Media array containing image details.
   * @param string $image_style
   *   Image style to apply to the image.
   * @param string $rel_image_style
   *   For some sliders we may want full/big image url in rel.
   *
   * @return array
   *   Image build array.
   */
  public function getSkuImage(array $media, $image_style = '', $rel_image_style = '') {
    $image = [
      '#theme' => 'image_style',
      '#style_name' => $image_style,
      '#uri' => $media['file']->getFileUri(),
      '#title' => $media['label'],
      '#alt' => $media['label'],
    ];

    if ($rel_image_style) {
      $image['#attributes']['rel'] = ImageStyle::load($rel_image_style)->buildUrl($image['#uri']);
    }

    return $image;
  }

  /**
   * Helper function to add price, final_price and discount info in build array.
   *
   * @param array $build
   *   Build array to modify.
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   SKU entity to use for getting price.
   */
  public function buildPrice(array &$build, SKU $sku_entity) {
    // Get the price, discounted price and discount.
    $build['price'] = $build['final_price'] = $build['discount'] = [];

    if ($price = (float) $sku_entity->get('price')->getString()) {
      $build['price'] = [
        '#theme' => 'acq_commerce_price',
        '#price' => $price,
      ];

      // Get the discounted price.
      if ($final_price = (float) $sku_entity->get('final_price')->getString()) {
        // Final price could be same as price, we dont need to show discount.
        if ($final_price >= $price) {
          return;
        }

        $build['final_price'] = [
          '#theme' => 'acq_commerce_price',
          '#price' => $final_price,
        ];

        // Get discount if discounted price available.
        $discount = floor((($price - $final_price) * 100) / $price);
        $build['discount'] = [
          '#markup' => t('Save @discount', ['@discount' => $discount . '%']),
        ];
      }
    }
    elseif ($final_price = (float) $sku_entity->get('final_price')->getString()) {
      $build['price'] = [
        '#theme' => 'acq_commerce_price',
        '#price' => $final_price,
      ];
    }
  }

  /**
   * Function to get price block build for a SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   SKU Entity.
   *
   * @return array
   *   Price block build array.
   */
  public function getPriceBlock(SKU $sku_entity) {
    $build = [];

    $this->buildPrice($build, $sku_entity);

    $price_build = [
      '#theme' => 'product_price_block',
      '#price' => $build['price'],
      '#final_price' => $build['final_price'],
      '#discount' => $build['discount'],
    ];

    return $price_build;
  }

  /**
   * Helper function to build discounted price for Sku in cart.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   Sku entity from cart for which discount needs to be calculated.
   * @param float $item_price
   *   Unit price for the sku item in the cart.
   *
   * @return mixed
   *   Calculated cart item price.
   */
  public function buildCartItemPrice(SKU $sku_entity, $item_price) {
    $sku_cart_price['price'] = (float) $sku_entity->get('price')->getString();
    $final_price = (float) $item_price;

    if ($final_price !== $sku_cart_price['price']) {
      $sku_cart_price['final_price'] = number_format($final_price, 3);
      $discount = (($sku_cart_price['price'] - $final_price) * 100 / $sku_cart_price['price']);
      $sku_cart_price['discount'] = t('Save @discount', ['@discount' => $discount . '%']);
    }

    return $sku_cart_price;
  }

  /**
   * Helper function to fetch sku from entity id rather than loading the SKU.
   *
   * @param int $sku_entity_id
   *   Entity id of the Sku item.
   *
   * @return string
   *   Sku Id of the item.
   *
   * @throws \Drupal\Core\Database\InvalidQueryException
   */
  public function getSkuByEntityId($sku_entity_id) {
    $query = $this->connection->select('acq_sku_field_data', 'asfd')
      ->fields('asfd', ['sku'])
      ->condition('id', $sku_entity_id)
      ->range(0, 1);

    return $query->execute()->fetchField();
  }

  /**
   * Helper function to fetch entity id from sku rather than loading the SKU.
   *
   * @param string $sku_text
   *   Sku text of the Sku item.
   *
   * @return int
   *   Entity Id of sku item.
   *
   * @throws \Drupal\Core\Database\InvalidQueryException
   */
  public function getEntityIdBySku($sku_text) {
    $query = $this->connection->select('acq_sku_field_data', 'asfd')
      ->fields('asfd', ['id'])
      ->condition('sku', $sku_text)
      ->range(0, 1);

    return $query->execute()->fetchField();
  }

  /**
   * Helper function to fetch child skus of a configurable Sku.
   *
   * @param mixed $sku
   *   sku text or Sku object.
   *
   * @return array
   *   Array of child skus.
   *
   * @throws \Drupal\Core\Database\InvalidQueryException
   */
  public function getChildSkus($sku) {
    $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);
    $child_skus = [];

    if ($sku_entity->getType() == 'configurable') {
      $query = $this->connection->select('acq_sku__field_configured_skus', 'asfcs')
        ->fields('asfcs', ['field_configured_skus_value'])
        ->condition('asfcs.entity_id', $sku_entity->id());

      $result = $query->execute();

      while ($row = $result->fetchAssoc()) {
        $child_skus[] = SKU::loadFromSku($row['field_configured_skus_value']);
      }
    }

    return $child_skus;
  }

  /**
   * Get Promotion node object(s) related to provided SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   The SKU Entity, for which linked promotions need to be fetched.
   * @param bool $getLinks
   *   Boolen to identify if Links are required.
   * @param array $types
   *   Type of promotion to filter on.
   *
   * @return array|\Drupal\Core\Entity\EntityInterface[]
   *   blank array, if no promotions found, else Array of promotion entities.
   */
  public function getPromotionsFromSkuId(SKU $sku,
                                         $getLinks = FALSE,
                                         array $types = ['cart', 'category']) {
    $promos = [];
    $promotion_nids = [];
    $promotions = [];
    // Fetch child skus, if its a parent sku.
    $child_skus = $this->getChildSkus($sku);

    // If not child skus, its a simple product.
    if (empty($child_skus)) {
      $child_skus[] = $sku;
    }

    $promotions[] = $sku->get('field_acq_sku_promotions')->getValue();

    foreach ($child_skus as $child_sku) {
      if ($child_sku) {
        $promotions[] = $child_sku->get('field_acq_sku_promotions')->getValue();
      }
    }

    foreach ($promotions as $promotion) {
      foreach ($promotion as $promo) {
        $promotion_nids[] = $promo['target_id'];
      }
    }

    $promotion_nids = array_unique($promotion_nids);
    if (!empty($promotion_nids)) {
      $promotion_nodes = Node::loadMultiple($promotion_nids);
      foreach ($promotion_nodes as $promotion_node) {
        $promotion_type = $promotion_node->get('field_acq_promotion_type')->getString();
        if (in_array($promotion_type, $types, TRUE)) {
          $langcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)
            ->getId();
          // Get the promotion with language fallback, if it did not have a
          // translation for $langcode.
          $promotion_node = $this->entityRepository->getTranslationFromContext($promotion_node, $langcode);
          $promotion_text = $promotion_node->get('field_acq_promotion_label')
            ->getString();
          $discount_type = $promotion_node->get('field_acq_promotion_disc_type')
            ->getString();
          $discount_value = $promotion_node->get('field_acq_promotion_discount')
            ->getString();
          /* @var \Drupal\node\Entity\Node $promotion */
          if ($getLinks) {
            $promos[$promotion_node->id()] = $promotion_node->toLink($promotion_text)
              ->toString()
              ->getGeneratedLink();
          }
          else {
            $description = $promotion_node->get('field_acq_promotion_description')
              ->getString();
            $promos[$promotion_node->id()] = [
              'text' => $promotion_text,
              'description' => $description,
              'discount_type' => $discount_type,
              'discount_value' => $discount_value,
            ];
          }
        }
      }
    }

    return $promos;
  }

}
