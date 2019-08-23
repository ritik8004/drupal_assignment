<?php

namespace Drupal\alshaya_acm_product;

use Drupal\acq_commerce\SKUInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\metatag\MetatagManager;
use Drupal\node\NodeInterface;

/**
 * Class SkuInfoHelper.
 *
 * @package Drupal\alshaya_acm_product
 */
class SkuInfoHelper {

  /**
   * SKU Manager service object.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * SKU images manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImagesManager;

  /**
   * The Metatag manager.
   *
   * @var \Drupal\metatag\MetatagManager
   */
  protected $metatagManager;

  /**
   * SkuInfoHelper constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU images manager.
   * @param \Drupal\metatag\MetatagManager $metatagManager
   *   The metatag manager object.
   */
  public function __construct(
    SkuManager $sku_manager,
    SkuImagesManager $sku_images_manager,
    MetatagManager $metatagManager
  ) {
    $this->skuManager = $sku_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->metatagManager = $metatagManager;
  }

  /**
   * Get translation of given entity for given langcode.
   *
   * @param object $entity
   *   The entity object.
   * @param string $langcode
   *   The language code.
   *
   * @return object
   *   Return entity object with translation if exists otherwise as is.
   */
  public function getEntityTranslation($entity, $langcode) {
    if (($entity instanceof ContentEntityInterface
         || $entity instanceof ConfigEntityInterface)
        && $entity->language()->getId() != $langcode
        && $entity->hasTranslation($langcode)
    ) {
      $entity = $entity->getTranslation($langcode);
    }
    return $entity;
  }

  /**
   * Get the size attributes with code and label.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Return the keyed array of size attributes code and label.
   */
  public function getSizeLabels(SKUInterface $sku): array {
    $size_array = &drupal_static(__METHOD__, []);

    if ($sku->bundle() == 'simple') {
      $plugin = $sku->getPluginInstance();
      if (($parent = $plugin->getParentSku($sku)) && $parent instanceof SKUInterface) {
        $sku = $parent;
      }
    }
    $sku_string = $sku->get('sku')->getString();

    if (!isset($size_array[$sku_string])) {
      $configurables = unserialize(
        $sku->get('field_configurable_attributes')->getString()
      );

      if (empty($configurables)) {
        return [];
      }

      $size_key = array_search('size', array_column($configurables, 'label'));
      if (!isset($configurables[$size_key])) {
        return [];
      }

      $size_options = [];
      array_walk($configurables[$size_key]['values'], function ($value, $key) use (&$size_options) {
        $size_options[$value['value_id']] = $value['label'];
      });
      $size_array[$sku_string] = $size_options;
    }

    return $size_array[$sku_string];
  }

  /**
   * Wrapper function get attributes.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param array $extra_unused_options
   *   (Optional) any extra unused options.
   *
   * @return array
   *   Attributes.
   */
  public function getAttributes(SKUInterface $sku, array $extra_unused_options = []): array {
    $skuData = $sku->toArray();

    // @TODO: We should really think of returning what is required instead
    // of removing stuff. Can be done later when we start developing and testing
    // for all brands.
    $unused_options = [
      'options_container',
      'required_options',
      'has_options',
      'product_activation_date',
      'url_key',
      'msrp_display_actual_price_type',
      'product_state',
      'tax_class_id',
      'gift_message_available',
      'gift_wrapping_available',
      'is_returnable',
      'special_from_date',
      'special_to_date',
      'custom_design_from',
      'custom_design_to',
      'ignore_price_update',
      'image',
      'small_image',
      'thumbnail',
      'swatch_image',
      'meta_description',
      'meta_keyword',
      'meta_title',
    ] + $extra_unused_options;

    $attributes = [];
    foreach ($skuData['attributes'] as $row) {
      if (in_array($row['key'], $unused_options)) {
        continue;
      }

      // Can not use data from $skuData['attributes'] as it is key_value
      // field type, and value is varchar field with limit of 255, Which strips
      // the text beyond the limit for description, and some of fields have key
      // stored instead of value, value is saved in it's separate table.
      if (isset($skuData["attr_{$row['key']}"])) {
        $row['value'] = $skuData["attr_{$row['key']}"][0]['value'];
      }
      // Remove un-wanted description key.
      unset($row['description']);
      $attributes[$row['key']] = $row['value'];
    };

    return $attributes;
  }

  /**
   * Get the metatags info of given node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   * @param array $selected_tags
   *   (Optional) If given, process only selected tags.
   *
   * @return array
   *   Return the array of metatags.
   */
  public function metaTags(NodeInterface $node, array $selected_tags = []) {
    $tags = $this->metatagManager->tagsFromEntityWithDefaults($node);
    if (!empty($selected_tags)) {
      $tags = array_intersect_key($tags, array_combine($selected_tags, $selected_tags));
    }
    $metaTags = $this->metatagManager->generateRawElements($tags, $node);

    $return = [];
    if (!empty($metaTags)) {
      foreach ($metaTags as $key => $tag) {
        $return[$key] = $tag['#attributes']['content'];
      }
    }

    return $return;
  }

  /**
   * Return formatted price.
   *
   * @param float $price
   *   The price.
   *
   * @return string
   *   Return string price upto configured decimal points.
   */
  public function formatPriceDisplay(float $price): string {
    return (string) _alshaya_acm_format_price_with_decimal($price);
  }

  /**
   * Wrapper function to get media items for an SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $context
   *   Context.
   *
   * @return array
   *   Media Items.
   */
  public function getMedia(SKUInterface $sku, string $context): array {
    /** @var \Drupal\acq_sku\Entity\SKU $sku */
    $media = $this->skuImagesManager->getProductMedia($sku, $context);

    $return = [
      'images' => [],
      'videos' => [],
    ];

    foreach ($media['media_items']['images'] ?? [] as $media_item) {
      $return['images'][] = [
        'url' => file_create_url($media_item['drupal_uri']),
        'image_type' => $media_item['sortAssetType'] ?? 'image',
      ];
    }

    foreach ($media['media_items']['videos'] ?? [] as $media_item) {
      $return['videos'][] = $media_item['video_url'];
    }

    return $return;
  }

  /**
   * Get categories associated with node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   * @param string|null $lang
   *   The lang code.
   *
   * @return array
   *   The array of terms with name, id and url.
   */
  public function getProductCategories(NodeInterface $node, $lang = NULL) {
    $categories = $node->get('field_category')->referencedEntities();
    $terms = [];
    if (!empty($categories)) {
      foreach ($categories as $term) {
        $term = $this->getEntityTranslation($term, $lang);
        $terms[] = [
          'name' => $term->label(),
          'id' => $term->id(),
          'url' => $this->getEntityUrl($term),
        ];
      }
    }
    return $terms;
  }

  /**
   * Get the entity Url.
   *
   * @param object $entity
   *   The entity object.
   *
   * @return mixed
   *   Return the generate url of the entity.
   */
  public function getEntityUrl($entity) {
    return $entity->toUrl('canonical', ['absolute' => TRUE])
      ->toString(TRUE)
      ->getGeneratedUrl();
  }

  /**
   * Get the stock info.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku_entity
   *   The sku entity.
   *
   * @return array
   *   The array with in_stock and stock.
   */
  public function stockInfo(SKUInterface $sku_entity): array {
    return [
      'in_stock' => $this->skuManager->isProductInStock($sku_entity),
      'stock' => (int) $this->skuManager->getStockQuantity($sku_entity),
    ];
  }

  /**
   * Function get linked skus.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $linked_type
   *   Linked type.
   *
   * @return array
   *   Linked SKUs.
   */
  public function getLinkedSkus(SKUInterface $sku, string $linked_type) {
    $linkedSkus = $this->skuManager->getLinkedSkus($sku, $linked_type);
    $linkedSkus = $this->skuManager->filterRelatedSkus($linkedSkus);

    return $linkedSkus;
  }

}
