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
  public function getTemplateName(array &$suggestions) {
    return 'node__acq_product__full';
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderArray(array &$variables) {
    $variables['#attached']['library'][] = 'alshaya_product_zoom/cloud_zoom_pdp_gallery';
    $variables['#attached']['library'][] = 'alshaya_white_label/attribute';
    $variables['#attached']['library'][] = 'alshaya_white_label/stickybutton';
  }

  /**
   * {@inheritdoc}
   */
  public function getContextFromPluginId($context, $pdp_layout) {
    return $context;
  }

}
