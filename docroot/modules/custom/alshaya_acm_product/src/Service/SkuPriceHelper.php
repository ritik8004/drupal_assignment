<?php

namespace Drupal\alshaya_acm_product\Service;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class Sku Price Helper.
 *
 * @package Drupal\alshaya_acm_product\Service
 */
class SkuPriceHelper {

  use StringTranslationTrait;

  public const PRICE_DISPLAY_MODE_SIMPLE = 'simple';

  public const PRICE_DISPLAY_MODE_FROM_TO = 'from_to';

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
   * Whether to show currency on second price or not.
   *
   * @var bool
   */
  private $showCurrencyOnSecondPrice;

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
  protected $build;

  /**
   * Cache tags from config to add to $build.
   *
   * @var string[]
   */
  private $configCacheTags;

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

    $display_settings = $config_factory->get('alshaya_acm_product.display_settings');

    $this->displayMode = $display_settings->get('price_display_mode') ?? self::PRICE_DISPLAY_MODE_SIMPLE;
    $this->showCurrencyOnSecondPrice = $display_settings->get('show_currency_on_second_price') ?? TRUE;

    $currency_config = $config_factory->get('acq_commerce.currency');
    $this->decimalPoints = (int) $currency_config->get('decimal_points');

    $this->configCacheTags = array_merge($display_settings->getCacheTags(), $currency_config->getCacheTags());
  }

  /**
   * Wrapper function to check if price mode is from to.
   *
   * @return bool
   *   TRUE if price mode is set to from to.
   */
  public function isPriceModeFromTo() {
    return $this->displayMode === self::PRICE_DISPLAY_MODE_FROM_TO;
  }

  /**
   * Get price block for specific sku.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Product sku for which we want the price block.
   * @param array $options
   *   Additional flags like color, langcode.
   *
   * @return array
   *   Build array.
   */
  public function getPriceBlockForSku(SKU $sku, array $options = []):array {
    $this->build = [
      '#theme' => 'product_price_block',
      '#cache' => ['tags' => $this->configCacheTags],
    ];

    if (empty($options['color'])) {
      $options['color'] = '';
    }

    $langcode = $options['langcode'] ?? '';

    $case = $sku->bundle() == 'configurable' ? $this->displayMode : 'simple';
    switch ($case) {
      case self::PRICE_DISPLAY_MODE_FROM_TO:
        $this->buildPriceBlockFromTo($sku, $options['color'], $langcode);
        break;

      case self::PRICE_DISPLAY_MODE_SIMPLE:
      default:
        $this->buildPriceBlockSimple($sku, $options['color'], $langcode);
        break;
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
   * @param string $langcode
   *   Language code used.
   */
  protected function buildPriceBlockSimple(SKU $sku, string $color = '', string $langcode = '') {
    $prices = $this->skuManager->getMinPrices($sku, $color);
    $price = $prices['price'];
    $final_price = $prices['final_price'];

    $data_attribute_price = [];
    $data_attribute_special_price = [];
    if (!empty($prices['fixed_price'])) {
      foreach (json_decode($prices['fixed_price'], TRUE) as $key => $value) {
        if (!empty($value['price'])) {
          $data_attribute_price[$key] = $value['price'];
        }
        if (!empty($value['special_price'])) {
          $data_attribute_special_price[$key] = $value['special_price'];
        }
      }
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

  /**
   * Update build array to display price in from-to mode.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Product sku for which we want the price block.
   * @param string $color
   *   Color value for limiting scope of variants.
   * @param string $langcode
   *   Language code used.
   */
  private function buildPriceBlockFromTo(SKU $sku, string $color = '', string $langcode = '') {
    $prices = $this->skuManager->getMinPrices($sku, $color);

    // Ignore proceeding with building range price block when product is OOS.
    if (empty($prices['children'])) {
      return $this->buildPriceBlockSimple($sku, $color, $langcode);
    }

    $child_prices = array_column($prices['children'], 'price');
    $child_final_prices = array_column($prices['children'], 'final_price');
    $discounts = array_column($prices['children'], 'discount');
    $selling_prices = array_column($prices['children'], 'selling_price');

    // We show normal price(no range) only in below conditions.
    // 0. It is possible that one product has final price but other doesn't
    // have it, we need to show range here.
    // 1. If no variant available.
    // 2. If all variants have same price.
    // 3. If all variants have same final_price.
    // If final_price available but the discount is
    // zero(discount=price-final_price), in this case we show
    // range(only when final_prices are not same).
    if (count(array_filter($child_prices)) == count(array_filter($child_final_prices))
      && ((is_countable($prices['children']) ? count($prices['children']) : 0) <= 1
      || count(array_unique(array_filter($child_prices))) == 1
      || count(array_unique($selling_prices)) == 1)) {

      return $this->buildPriceBlockSimple($sku, $color, $langcode);
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
      $this->build['#price'] = [
        '#markup' => $this->getMinMax($selling_prices),
      ];
    }

    $this->build['#discount'] = [
      '#markup' => $this->getDiscountedPriceMarkup($discounts, $langcode),
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

    return $this->getFormattedPrice($min) . '<span class="min-max-separator">-</span>' . $this->getFormattedPrice($max, $this->showCurrencyOnSecondPrice);
  }

  /**
   * Wrapper function to get formatted price.
   *
   * @param string|float $price
   *   Price.
   * @param bool $show_currency
   *   Whether currency code to show or not.
   *
   * @return string
   *   Formatted price.
   */
  private function getFormattedPrice($price, bool $show_currency = TRUE):string {
    $build = [
      '#theme' => 'commerce_price_with_currency',
      '#price' => number_format($price, $this->decimalPoints),
      '#show_currency' => $show_currency,
    ];

    return (string) $this->renderer->renderPlain($build);
  }

  /**
   * Get Discounted Price markup.
   *
   * @param array $discounts
   *   Discount percents.
   * @param string $langcode
   *   Language code used.
   *
   * @return string
   *   Price markup.
   */
  public function getDiscountedPriceMarkup(array $discounts, string $langcode = ''):string {
    if (empty($discounts) || empty(max($discounts))) {
      return '';
    }
    $options = $langcode ? ['langcode' => $langcode] : [];

    if (count(array_unique($discounts)) == 1) {
      return (string) $this->t('Save @discount%', ['@discount' => reset($discounts)], $options);
    }

    return (string) $this->t('Save upto @discount%', ['@discount' => max($discounts)], $options);
  }

}
