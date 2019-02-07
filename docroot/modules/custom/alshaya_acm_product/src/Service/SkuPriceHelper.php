<?php

namespace Drupal\alshaya_acm_product\Service;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class SkuPriceHelper.
 *
 * @package Drupal\alshaya_acm_product\Service
 */
class SkuPriceHelper {

  const PRICE_DISPLAY_MODE_SIMPLE = 'simple';

  const PRICE_DISPLAY_MODE_FROM_TO = 'from_to';

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * Current Display Mode.
   *
   * @var string
   */
  private $displayMode;

  /**
   * SkuPriceHelper constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(SkuManager $sku_manager,
                              ConfigFactoryInterface $config_factory) {
    $this->skuManager = $sku_manager;

    $config = $config_factory->get('alshaya_acm_product.display_settings');
    $this->displayMode = $config->get('price_display_mode') ?? self::PRICE_DISPLAY_MODE_SIMPLE;
  }

}
