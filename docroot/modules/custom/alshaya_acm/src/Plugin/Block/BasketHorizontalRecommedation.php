<?php

namespace Drupal\alshaya_acm\Plugin\Block;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Block\BlockBase;
use Drupal\acq_cart\CartStorageInterface;
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CartStorageInterface $cart_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->cartStorage = $cart_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('acq_cart.cart_storage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get current cart skus.
    $cartSkus = $this->cartStorage->getCartSkus();
    if (!empty($cartSkus)) {
      // Get all cross sell SKU.
      $items = SKU::getCrossSellSkus($cartSkus);
    }

    if (!empty($items)) {
      return views_embed_view('product_slider', 'block_product_slider', implode(',', $items));
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
