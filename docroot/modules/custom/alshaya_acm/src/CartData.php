<?php

namespace Drupal\alshaya_acm;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Cache\Cache;

/**
 * Class Cart Data.
 *
 * @package Drupal\alshaya_acm
 */
class CartData {

  /**
   * Reference to object of current class.
   *
   * @var \Drupal\alshaya_acm\CartData|null
   */
  private static $selfReference = NULL;

  /**
   * Cart items.
   *
   * @var array
   */
  private $items;

  /**
   * Cart subtotal.
   *
   * @var float
   */
  private $subtotal;

  /**
   * Cart applied rules.
   *
   * @var array
   */
  private $appliedRules;

  /**
   * CartData constructor.
   *
   * @param array $items
   *   Cart items.
   * @param float $subtotal
   *   Cart subtotal.
   * @param array $applied_rules
   *   Cart applied rules.
   */
  public function __construct(array $items,
                              float $subtotal,
                              array $applied_rules = []) {
    $this->items = $items;
    $this->subtotal = $subtotal;
    $this->appliedRules = $applied_rules;
  }

  /**
   * Return cart data object if available.
   *
   * @return null|\Drupal\alshaya_acm\CartData
   *   CartData object if available.
   */
  public static function getCart() {
    return self::$selfReference;
  }

  /**
   * Wrapper to prepare cart data and create object.
   *
   * @param array $data
   *   Data from GET.
   *
   * @return \Drupal\alshaya_acm\CartData
   *   Cart data object.
   */
  public static function createFromArray(array $data) {
    if (isset(self::$selfReference)) {
      return self::$selfReference;
    }

    $items = [];

    foreach ($data['products'] ?? [] as $product) {
      if (empty($product['sku']) || empty($product['quantity'])) {
        throw new \InvalidArgumentException();
      }

      $entity = SKU::loadFromSku($product['sku']);

      if (!($entity instanceof SKUInterface)) {
        throw new \InvalidArgumentException();
      }

      $item = [
        'sku' => $product['sku'],
        'entity' => $entity,
        'quantity' => (int) $product['quantity'],
        'price' => (float) $product['price'],
      ];

      $item['total'] = $item['quantity'] * $item['price'];
      $items[$product['sku']] = $item;
    }

    $subtotal = (float) $data['cart']['subtotal'] ?? 0;
    $applied_rules = explode(',', $data['cart']['applied_rules'] ?? '');

    if (empty($items) || empty($subtotal)) {
      throw new \InvalidArgumentException();
    }

    self::$selfReference = new static($items, $subtotal, $applied_rules);
    return self::$selfReference;
  }

  /**
   * Get cart items.
   *
   * @return array
   *   Cart items.
   */
  public function getItems() {
    return $this->items;
  }

  /**
   * Get skus in cart.
   *
   * @return array
   *   SKUs array.
   */
  public function getSkus() {
    return array_column($this->items, 'sku');
  }

  /**
   * Get cart subtotal.
   *
   * @return float
   *   Cart subtotal.
   */
  public function getSubTotal() {
    return $this->subtotal;
  }

  /**
   * Get cart applied rules.
   *
   * @return array
   *   Array of applied rule ids.
   */
  public function getAppliedRules() {
    return $this->appliedRules;
  }

  /**
   * Get total number of items in cart.
   *
   * @return int
   *   Total number of items.
   */
  public function getTotalQuantity() {
    $total = 0;

    foreach ($this->getItems() as $item) {
      $total += $item['quantity'];
    }

    return $total;
  }

  /**
   * Get cache tags for cart.
   *
   * @return array
   *   Cache tags.
   */
  public function getCacheTags() {
    $tags = [];

    foreach ($this->items as $item) {
      Cache::mergeTags($tags, $item['entity']->getCacheTags());
    }

    return $tags;
  }

}
