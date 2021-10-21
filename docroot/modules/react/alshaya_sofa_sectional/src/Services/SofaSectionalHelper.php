<?php

namespace Drupal\alshaya_sofa_sectional\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\acq_sku\Entity\SKU;
use Drupal\node\Entity\Node;

/**
 * General Helper service for the Add To Bag feature.
 */
class SofaSectionalHelper {

  /**
   * Config factory service.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Sku manager service.
   *
   * @var Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Constructor for the SofaSectionalHelper service.
   *
   * @param Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   The sku manager service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    SkuManager $sku_manager
  ) {
    $this->configFactory = $config_factory;
    $this->skuManager = $sku_manager;
  }

  /**
   * Detects if the Sofa and Sectional feature is enabled or not.
   */
  public function isSofaSectionalFeatureEnabled() {
    return $this->configFactory->get('alshaya_sofa_sectional.settings')->get('enabled');
  }

  /**
   * Detects if the Sofa and Sectional feature is applicable for product.
   */
  public function isSofaSectionalFeatureApplicable(Node $entity) {
    // Return if feature is not enabled,
    // entity isn't a product.
    if (!$this->isSofaSectionalFeatureEnabled()
      || $entity->bundle() !== 'acq_product') {
      return FALSE;
    }

    // Get SKU for the node.
    $sku_string = $this->skuManager->getSkuForNode($entity);

    /** @var \Drupal\acq_sku\SKU  $sku */
    $sku = SKU::loadFromSku($sku_string);

    // Return if sku in not available.
    if (!($sku instanceof SKU)) {
      return FALSE;
    }

    // Don't show the react form for following conditions,
    // if Sku is not in stock,
    // if Sku is a simple sku,
    // if Sku is not buyable,
    // if checkout is disable.
    if (!$this->skuManager->isProductInStock($sku)
      || $sku->bundle() == 'simple'
      || !alshaya_acm_product_is_buyable($sku)
      || $this->configFactory->get('alshaya_acm.cart_config')->get('checkout_feature') === 'disabled') {
      return FALSE;
    }

    // Get category ids from config.
    $category_ids = $this->configFactory->get('alshaya_sofa_sectional.settings')->get('category_ids');

    // Get category ids from product field.
    $categories = $entity->get('field_category')->getValue();
    $categories = array_column($categories, 'target_id');

    // Check if category ids from field are present in config.
    $categories = array_intersect($categories, $category_ids);
    if (empty($categories)) {
      return FALSE;
    }

    // Return true if all conditions are passed.
    return TRUE;
  }

}
