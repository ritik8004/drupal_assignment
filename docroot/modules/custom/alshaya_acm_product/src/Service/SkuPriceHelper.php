<?php

namespace Drupal\alshaya_acm_product\Service;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class SkuPriceHelper.
 *
 * @package Drupal\alshaya_acm_product\Service
 */
class SkuPriceHelper {

  use StringTranslationTrait;

  const PRICE_DISPLAY_MODE_SIMPLE = 'simple';

  const PRICE_DISPLAY_MODE_FROM_TO = 'from_to';

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  private $renderer;

  /**
   * Current Display Mode.
   *
   * @var string
   */
  private $displayMode;

  /**
   * Decimal points to show for price.
   *
   * @var int
   */
  private $decimalPoints;

  /**
   * Build array updated across functions.
   *
   * @var array
   */
  private $build;

  /**
   * SkuPriceHelper constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer.
   */
  public function __construct(SkuManager $sku_manager,
                              ConfigFactoryInterface $config_factory,
                              RendererInterface $renderer) {
    $this->skuManager = $sku_manager;
    $this->renderer = $renderer;

    $this->displayMode = $config_factory
      ->get('alshaya_acm_product.display_settings')
      ->get('price_display_mode') ?? self::PRICE_DISPLAY_MODE_SIMPLE;

    $this->decimalPoints = (int) $config_factory
      ->get('acq_commerce.currency')
      ->get('decimal_points');
  }

  /**
   * Get price block for specific sku.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Product sku for which we want the price block.
   * @param array $options
   *   Additional flags like vat text is required or not.
   *
   * @return array
   *   Build array.
   */
  public function getPriceBlockForSku(SKU $sku, array $options = ['with_vat' => 1]):array {
    $this->build = [
      '#theme' => 'product_price_block',
    ];

    if (empty($options['color'])) {
      $options['color'] = '';
    }

    $case = $sku->bundle() == 'configurable' ? $this->displayMode : 'simple';
    switch ($case) {
      case self::PRICE_DISPLAY_MODE_FROM_TO:
        $this->buildPriceBlockFromTo($sku, $options['color']);
        break;

      case self::PRICE_DISPLAY_MODE_SIMPLE:
      default:
        $this->buildPriceBlockSimple($sku, $options['color']);
        break;
    }

    if (!empty($options['with_vat'])) {
      $this->build['#vat_text'] = $this->skuManager->getVatText();
    }

    return $this->build;
  }

  /**
   * Update build array to display price in simple mode.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Product sku for which we want the price block.
   * @param string $color
   *   Color value for limiting scope of variants.
   */
  private function buildPriceBlockSimple(SKU $sku, string $color = '') {
    $prices = $this->skuManager->getMinPrices($sku, $color);
    $price = $prices['price'];
    $final_price = $prices['final_price'];

    if ($price) {
      $this->build['#price'] = [
        '#theme' => 'acq_commerce_price',
        '#price' => $price,
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
        ];

        // Get discount if discounted price available.
        $this->build['#discount'] = [
          '#markup' => $this->skuManager->getDiscountedPriceMarkup($price, $final_price),
        ];
      }
    }
    elseif ($final_price) {
      $this->build['#price'] = [
        '#theme' => 'acq_commerce_price',
        '#price' => $final_price,
      ];
    }
  }

  /**
   * Update build array to display price in from-to mode.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Product sku for which we want the price block.
   * @param string $color
   *   Color value for limiting scope of variants.
   */
  private function buildPriceBlockFromTo(SKU $sku, string $color = '') {
    $prices = $this->skuManager->getMinPrices($sku, $color);

    $child_prices = array_column($prices['children'], 'price');
    $child_final_prices = array_column($prices['children'], 'final_price');
    $discounts = array_column($prices['children'], 'discount');

    if (count($prices['children']) <= 1
      || count(array_unique(array_filter($child_prices))) == 1
      || count(array_unique(array_filter($child_final_prices))) == 1) {

      return $this->buildPriceBlockSimple($sku);
    }

    if (count(array_filter($child_prices)) == count(array_filter($child_final_prices))) {
      $this->build['#price'] = [
        '#markup' => $this->getMinMax($child_prices),
      ];

      $this->build['#final_price'] = [
        '#markup' => $this->getMinMax($child_final_prices),
      ];
    }
    else {
      $selling_prices = array_column($prices['children'], 'selling_price');
      $this->build['#price'] = [
        '#markup' => $this->getMinMax($selling_prices),
      ];
    }

    $this->build['#discount'] = [
      '#markup' => $this->getDiscountedPriceMarkup($discounts),
    ];
  }

  /**
   * Wrapper function to get min - max string.
   *
   * @param array $prices
   *   Prices array.
   *
   * @return string
   *   String with min - max or just min.
   */
  private function getMinMax(array $prices):string {
    $min = min($prices);
    $max = max($prices);

    if ($min == $max) {
      return $this->getFormattedPrice($min);
    }

    return $this->getFormattedPrice($min) . ' - ' . $this->getFormattedPrice($max);
  }

  /**
   * Wrapper function to get formatted price.
   *
   * @param string|float $price
   *   Price.
   *
   * @return string
   *   Formatted price.
   */
  private function getFormattedPrice($price):string {
    $build = [
      '#theme' => 'commerce_price_with_currency',
      '#price' => number_format($price, $this->decimalPoints),
    ];

    return (string) $this->renderer->renderPlain($build);
  }

  /**
   * Get Discounted Price markup.
   *
   * @param array $discounts
   *   Discount percents.
   *
   * @return string
   *   Price markup.
   */
  public function getDiscountedPriceMarkup(array $discounts):string {
    if (empty($discounts) || empty(max($discounts))) {
      return '';
    }

    if (count(array_unique($discounts)) == 1) {
      return (string) $this->t('Save @discount%', ['@discount' => reset($discounts)]);
    }

    return (string) $this->t('Save upto @discount%', ['@discount' => max($discounts)]);
  }

}
