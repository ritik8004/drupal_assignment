<?php

namespace Drupal\alshaya_pdp_layouts\Plugin\PdpLayout;

/**
 * Provides the default laypout for PDP.
 *
 * @PdpLayout(
 *   id = "magazine",
 *   label = @Translation("Magazine"),
 * )
 */
class MagazinePdpLayout extends PdpLayoutBase {

  /**
   * {@inheritdoc}
   */
  public function getTemplateName(array &$suggestions, string $bundle) {
    $suggestions[] = match ($bundle) {
      'rcs_product' => 'node__rcs_product__full_magazine',
        default => 'node__acq_product__full_magazine',
    };
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderArray(array &$variables) {
    $pdp_gallery_type = \Drupal::config('alshaya_acm_product.settings')->get('pdp_gallery_type');
    if ($pdp_gallery_type == 'classic') {
      // Classic gallery for cos PDP magazine layout.
      $variables['#attached']['library'][] = 'alshaya_product_zoom/cloud_zoom_pdp_gallery';
      $variables['#attached']['library'][] = 'alshaya_white_label/attribute';
    }
    else {
      $variables['#attached']['library'][] = 'alshaya_product_zoom/magazine_gallery';
    }
    $variables['#attached']['library'][] = 'alshaya_white_label/magazine_attribute';
    $variables['#attached']['library'][] = 'alshaya_white_label/magazine_socialSharepopup';
    $variables['#attached']['library'][] = 'alshaya_seo_transac/gtm_pdp_default';
    $variables['#attached']['drupalSettings']['color_swatches_hover'] = TRUE;
    $variables['#attached']['drupalSettings']['pdp_gallery_type'] = $pdp_gallery_type;
  }

  /**
   * {@inheritdoc}
   */
  public function getCotextFromPdpLayout($context, $pdp_layout) {
    return $context . '-' . $pdp_layout;
  }

}
