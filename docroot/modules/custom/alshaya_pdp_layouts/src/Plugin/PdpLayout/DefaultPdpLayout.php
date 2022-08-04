<?php

namespace Drupal\alshaya_pdp_layouts\Plugin\PdpLayout;

/**
 * Provides the default laypout for PDP.
 *
 * @PdpLayout(
 *   id = "default",
 *   label = @Translation("Classic"),
 * )
 */
class DefaultPdpLayout extends PdpLayoutBase {

  /**
   * {@inheritdoc}
   */
  public function getTemplateName(array &$suggestions, string $bundle) {
    $suggestions[] = match ($bundle) {
      'rcs_product' => 'node__rcs_product__full',
        default => 'node__acq_product__full',
    };
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderArray(array &$variables) {
    $variables['#attached']['library'][] = 'alshaya_product_zoom/cloud_zoom_pdp_gallery';
    $variables['#attached']['library'][] = 'alshaya_white_label/attribute';
    $variables['#attached']['library'][] = 'alshaya_white_label/stickybutton';
    $variables['#attached']['library'][] = 'alshaya_seo_transac/gtm_pdp_default';
  }

  /**
   * {@inheritdoc}
   */
  public function getCotextFromPdpLayout($context, $pdp_layout) {
    return $context;
  }

}
