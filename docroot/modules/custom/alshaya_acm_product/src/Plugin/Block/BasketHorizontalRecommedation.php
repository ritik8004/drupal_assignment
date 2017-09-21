<?php

namespace Drupal\alshaya_acm_product\Plugin\Block;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Block\BlockBase;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_sku\AcqSkuLinkedSku;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides a 'Basket: Horizontal Product Recommendation' block.
 *
 * @Block(
 *   id = "basket_horizontal_recommendation",
 *   admin_label = @Translation("Basket: Horizontal Product Recommendation")
 * )
 */
class BasketHorizontalRecommedation extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\acq_cart\CartStorageInterface definition.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * The linked sku service.
   *
   * @var \Drupal\acq_sku\AcqSkuLinkedSku
   */
  protected $linkedSkus;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart session.
   * @param \Drupal\acq_sku\AcqSkuLinkedSku $linked_skus
   *   Linked sku service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CartStorageInterface $cart_storage, AcqSkuLinkedSku $linked_skus) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->cartStorage = $cart_storage;
    $this->linkedSkus = $linked_skus;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('acq_cart.cart_storage'),
      $container->get('acq_sku.linked_sku')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');

    $view_skus = [];

    // Cross sell skus from the related rule.
    $cross_sell_rule_skus = [];

    // Get current cart skus.
    if ($cart = $this->cartStorage->getCart(FALSE)) {
      $skus = [];
      $items = $cart->items();

      foreach ($items as $item) {
        if ($parent_sku = alshaya_acm_product_get_parent_sku_by_sku($item['sku'])) {
          $skus[] = $parent_sku->getSku();
        }
        else {
          $skus[] = $item['sku'];
        }
      }

      if (!empty($skus)) {
        foreach ($skus as $sku) {
          if ($sku_entity = SKU::loadFromSku($sku)) {
            $cross_sell_rule_skus += $this->linkedSkus->getLinkedSKus($sku_entity, 'crosssell');
            $cross_sell_manual_skus = $sku_entity->getCrossSell();
            foreach ($cross_sell_manual_skus as $cross_sell_sku) {
              $view_skus[] = $cross_sell_sku['value'];
            }
          }
        }
      }
    }

    if (!empty($view_skus)) {
      // Get all cross sell SKU.
      $view_skus = array_diff($view_skus, $skus);
    }

    // Merging the manual cross sell skus from product and cross sell skus from
    // the related rule.
    $view_skus = array_merge($view_skus, $cross_sell_rule_skus);

    if (!empty($view_skus)) {
      $related_items_size = \Drupal::config('alshaya_acm_product.settings')->get('related_items_size');

      $view_skus = array_unique($view_skus);
      $view_skus = array_slice($view_skus, 0, $related_items_size, TRUE);
      return views_embed_view('product_slider', 'block_product_slider', implode(',', $view_skus));
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
