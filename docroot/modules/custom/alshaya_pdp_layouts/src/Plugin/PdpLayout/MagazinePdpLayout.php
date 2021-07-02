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
    switch ($bundle) {
      case 'rcs_product':
        $suggestions[] = 'node__rcs_product__full_magazine';
        break;

      default:
        $suggestions[] = 'node__acq_product__full_magazine';
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderArray(array &$variables) {
    $variables['#attached']['library'][] = 'alshaya_product_zoom/magazine_gallery';
    $variables['#attached']['library'][] = 'alshaya_white_label/magazine_attribute';
    $variables['#attached']['library'][] = 'alshaya_white_label/magazine_socialSharepopup';
    $variables['#attached']['drupalSettings']['color_swatches_hover'] = TRUE;
    $variables['#attached']['library'][] = 'alshaya_seo_transac/gtm_pdp_default';
  }

  /**
   * {@inheritdoc}
   */
  public function getCotextFromPdpLayout($context, $pdp_layout) {
    return $context . '-' . $pdp_layout;
  }

}
