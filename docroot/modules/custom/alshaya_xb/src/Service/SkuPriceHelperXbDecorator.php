<?php

namespace Drupal\alshaya_xb\Service;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\Service\SkuPriceHelper;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\RendererInterface;

/**
 * Class Sku Price Helper XB decorator.
 *
 * @package Drupal\alshaya_xb\Service
 */
class SkuPriceHelperXbDecorator extends SkuPriceHelper {
  /**
   * Inner service SkuImagesHelper.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesHelper
   */
  protected $innerService;

  /**
   * Domain config overrides.
   *
   * @var \Drupal\alshaya_xb\Service\DomainConfigOverrides
   */
  protected $domainConfig;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * SkuPriceHelper constructor.
   *
   * @param \Drupal\alshaya_acm_product\Service\SkuPriceHelper $sku_price_helper
   *   SKU price helper inner service.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer.
   * @param \Drupal\alshaya_xb\Service\DomainConfigOverrides $domain_config
   *   Domain configs.
   */
  public function __construct(SkuPriceHelper $sku_price_helper,
                              SkuManager $sku_manager,
                              ConfigFactoryInterface $config_factory,
                              RendererInterface $renderer,
                              DomainConfigOverrides $domain_config) {
    $this->innerService = $sku_price_helper;
    $this->skuManager = $sku_manager;
    $this->domainConfig = $domain_config;
    parent::__construct(
      $sku_manager,
      $config_factory,
      $renderer
    );
  }

  /**
   * {@inheritDoc}
   */
  protected function buildPriceBlockSimple(SKU $sku, string $color = '', string $langcode = '') {
    $prices = $this->skuManager->getMinPrices($sku, $color);
    $price = $prices['price'];
    $final_price = $prices['final_price'];

    $data_attribute_price = [];
    $data_attribute_special_price = [];
    if (!empty($prices['fixed_price'])) {
      foreach (json_decode($prices['fixed_price'], TRUE) as $key => $value) {
        $key = strtolower($key);
        if (!empty($value['price'])) {
          $data_attribute_price[$key] = $value['price'];
        }
        if (!empty($value['special_price'])) {
          $data_attribute_special_price[$key] = $value['special_price'];
        }
      }
    }

    // Get config overrides by domain.
    $config = $this->domainConfig->getConfigByDomain();

    // When fixed price (Catalog price) exist, we override the MDC prices.
    if (!empty($data_attribute_special_price) && array_key_exists($config['code'], $data_attribute_special_price)) {
      // If Sku has special_price value in fixed_price attribute for site's
      // currency, then render price with discount by setting final_price to
      // 0.01 if not set from commerce backend.
      $final_price = ($final_price && ($price > $final_price)) ? $final_price : 0.01;
    }

    if ($price) {
      $this->build['#price'] = [
        '#theme' => 'acq_commerce_price',
        '#price' => $price,
        '#fixed_price' => json_encode($data_attribute_price),
      ];

      // Get the discounted price.
      if ($final_price) {
        // Final price could be same as price, we dont need to show discount.
        if ($final_price >= $price) {
          return;
        }

        $this->build['#final_price'] = [
          '#theme' => 'acq_commerce_price',
          '#price' => $final_price,
          '#fixed_price' => json_encode($data_attribute_special_price),
        ];

        // Get discount if discounted price available.
        $this->build['#discount'] = [
          '#markup' => $this->skuManager->getDiscountedPriceMarkup($price, $final_price, $langcode),
        ];
      }
    }
    elseif ($final_price) {
      $this->build['#price'] = [
        '#theme' => 'acq_commerce_price',
        '#price' => $final_price,
        '#fixed_price' => json_encode($data_attribute_price),
      ];
    }
  }

}
