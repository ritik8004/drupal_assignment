<?php

namespace Drupal\alshaya_acm_product\Plugin\Block;

use Drupal\acq_sku\AcqSkuLinkedSku;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Block\BlockBase;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Extension\ModuleHandlerInterface;
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
   * SKU Manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Module Handler object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

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
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler object.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              CartStorageInterface $cart_storage,
                              SkuManager $sku_manager,
                              ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->cartStorage = $cart_storage;
    $this->skuManager = $sku_manager;
    $this->moduleHandler = $module_handler;
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
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this->moduleHandler->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');

    // Cross sell SKUs.
    $cross_sell_skus = [];

    // Get current cart skus.
    if ($cart = $this->cartStorage->getCart(FALSE)) {
      $skus = [];
      $items = $cart->items();

      foreach ($items as $item) {
        // Get linked skus of current sku always.
        $skus[] = $item['sku'];

        // Get linked skus of parent as well.
        if ($parent_sku = alshaya_acm_product_get_parent_sku_by_sku($item['sku'])) {
          $skus[] = $parent_sku->getSku();
        }
      }

      if (!empty($skus)) {
        foreach ($skus as $sku) {
          if ($sku_entity = SKU::loadFromSku($sku)) {
            $cross_sell_skus += $this->skuManager->getLinkedSkus($sku_entity, AcqSkuLinkedSku::LINKED_SKU_TYPE_CROSSSELL);
          }
        }
      }
    }

    if (!empty($cross_sell_skus)) {
      // Get all cross sell SKU.
      $cross_sell_skus = array_diff($cross_sell_skus, $skus);
    }

    if (!empty($cross_sell_skus)) {
      $view_skus = array_unique($cross_sell_skus);
      $view_skus = $this->skuManager->filterRelatedSkus($view_skus);

      if (!empty($view_skus)) {
        return [
          '#theme' => 'products_horizontal_slider',
          '#data' => $view_skus,
          '#section_title' => '',
          '#views_name' => 'product_slider',
          '#views_display_id' => 'block_product_slider',
        ];
      }
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // Vary based on session as each session will have different cart.
    return Cache::mergeContexts(parent::getCacheContexts(), ['session']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = parent::getCacheTags();

    // As soon as we have cart, we have session.
    // As soon as we have session, varnish is disabled.
    // We are good to have no cache tag based on cart if there is none.
    if ($cart = $this->cartStorage->getCart(FALSE)) {
      // Custom cache tag here will be cleared in API Wrapper after every
      // update cart call.
      $cache_tags = Cache::mergeTags($cache_tags, [
        'cart:' . $cart->id(),
      ]);
    }

    return $cache_tags;
  }

}
