<?php

namespace Drupal\alshaya_acm_product;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class ProductHelper.
 *
 * @package Drupal\alshaya_acm_product
 */
class ProductHelper {

  use StringTranslationTrait;

  /**
   * Max length to show in short description.
   *
   * @var string
   */
  protected $shortDescMaxLength;

  /**
   * SkuManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->shortDescMaxLength = $config_factory
      ->get('alshaya_acm_product.display_settings')
      ->get('short_desc_characters');
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

    // It is very unlikely but description might be too short to process.
    if (strlen($html) <= $this->shortDescMaxLength) {
      $build['read_more_style']['value'] = [
        '#markup' => 'display:none;',
      ];

      $short_desc['value'] = [
        '#markup' => $html,
      ];
    }
    else {
      $desc = text_summary($html, NULL, $this->shortDescMaxLength);

      // Remove html tags from short desc. We don't want to show empty tags.
      $desc = strip_tags($desc);

      $short_desc['value'] = [
        '#markup' => $desc . '...',
      ];
    }

    $build['short_desc'] = $short_desc;
  }

}
