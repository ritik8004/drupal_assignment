<?php

namespace Drupal\alshaya_acm_product;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Utility\Unicode;
use Drupal\acq_commerce\SKUInterface;

/**
 * Class ProductHelper.
 *
 * @package Drupal\alshaya_acm_product
 */
class ProductHelper {

  use StringTranslationTrait;

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * Product display settings (alshaya_acm_product.display_settings).
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $productDisplaySettings;

  /**
   * SkuImagesManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU images manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              SkuManager $sku_manager,
                              SkuImagesManager $sku_images_manager) {
    $this->configFactory = $config_factory;
    $this->skuManager = $sku_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->productDisplaySettings = $this->configFactory->get('alshaya_acm_product.display_settings');
  }

  /**
   * Get Short description based from HTML.
   *
   * @param array $build
   *   Build array to modify.
   * @param string $html
   *   HTML Markup.
   */
  public function updateShortDescription(array &$build, $html) {
    $short_desc['label'] = [
      '#markup' => $this->t('Short Description'),
    ];
    $desc = $this->createShortDescription($html);
    // It is very unlikely but description might be too short to process.
    if (!isset($desc['read_more'])) {
      $build['read_more_style']['value'] = [
        '#markup' => 'display:none;',
      ];
    }
    $short_desc['value'] = [
      '#markup' => $desc['html'],
    ];
    $build['short_desc'] = $short_desc;
  }

  /**
   * Create short description from given html.
   */
  public function createShortDescription($html, $limit = NULL) {
    $limit = ($limit == NULL) ? $this->productDisplaySettings->get('short_desc_characters') : $limit;
    $desc_stripped = strip_tags($html);
    // It is very unlikely but description might be too short to process.
    if (mb_strlen($html) <= $limit || mb_strlen($desc_stripped) <= $limit) {
      $return = [
        'html' => $html,
      ];
    }
    else {
      $desc = ($this->productDisplaySettings->get('short_desc_text_summary'))
        ? text_summary($html, NULL, $limit)
        : Unicode::truncate($desc_stripped, $limit, TRUE, FALSE);
      $return = [
        'html' => $desc . '...',
        'read_more' => TRUE,
      ];
    }
    return $return;
  }

  /**
   * Process short description for ellipsis.
   *
   * Process the short description array and add the ellipses in the last html
   * tag so that its not rendered in the second line.
   *
   * @param string $short_desc
   *   Short description.
   *
   * @return string
   *   Short description.
   */
  public function processShortDescEllipsis(string $short_desc) {
    // If normal string without any html tag.
    if (strip_tags($short_desc) == $short_desc) {
      return $short_desc;
    }
    // Remove the ellipses appended at last if there any.
    if (Unicode::substr($short_desc, -3) == '...') {
      $short_desc = Unicode::substr($short_desc, 0, -3);
    }
    // To suppress errors by the DomDocument.
    libxml_use_internal_errors(TRUE);
    $dom = new \DOMDocument();
    $dom->loadHTML(mb_convert_encoding($short_desc, 'HTML-ENTITIES', 'UTF-8'));
    $last_child = &$dom->lastChild;
    // Iterate recursively until we reach the last child element.
    while ($last_child) {
      if (!$last_child->lastChild) {
        // Append ellipsis on last child element content.
        $last_child->textContent .= '...';
      }
      $last_child = &$last_child->lastChild;
    }
    $short_desc = trim($dom->saveHTML());
    return $short_desc;
  }

  /**
   * Display swatches on PLP/Search/Promo.
   *
   * @param array $build
   *   Build array to modify.
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   */
  public function updateSwatches(array &$build, SKUInterface $sku) {
    // We do not display swatches for color nodes.
    $color = $build['#color'] ?? '';
    // Display swatches only if enabled in configuration and not color node.
    if ($this->productDisplaySettings->get('color_swatches') && empty($color)) {
      // Get swatches for this product from media.
      $swatches = $this->skuImagesManager->getSwatches($sku);
      if (!empty($swatches)) {
        // Display the colors count for mobile only if different variants images
        // being shown in gallery on PLP.
        if ($this->productDisplaySettings->get('show_variants_thumbnail_plp_gallery')) {
          $build['#swatch_color_count'] = count($swatches) > 1
            ? $this->t('@swatch_count colors', ['@swatch_count' => count($swatches)])
            : $this->t('@swatch_count color', ['@swatch_count' => count($swatches)]);
        }
        else {
          // Show only first 'X' swatches as defined in configuration.
          $swatch_plp_limit = $this->productDisplaySettings->get('swatch_plp_limit');
          $build['#swatches'] = array_slice($swatches, 0, $swatch_plp_limit, TRUE);
          $build['#swatch_more_text'] = count($swatches) > $swatch_plp_limit
            ? $this->t('+ @swatch_count colors', ['@swatch_count' => count($swatches) - $swatch_plp_limit])
            : FALSE;
        }
      }
    }
  }

}
