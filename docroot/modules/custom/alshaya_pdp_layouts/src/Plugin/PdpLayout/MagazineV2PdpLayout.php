<?php

namespace Drupal\alshaya_pdp_layouts\Plugin\PdpLayout;

/**
 * Provides the default laypout for PDP.
 *
 * @PdpLayout(
 *   id = "magazine_v2",
 *   label = @Translation("Magazine 2.0"),
 * )
 */
class MagazineV2PdpLayout extends PdpLayoutBase {

  /**
   * {@inheritdoc}
   */
  public function getTemplateName(array &$suggestions) {
    $suggestions[] = 'node__acq_product__full_magazine_v2';
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderArray(array &$variables) {
    $variables['#attached']['library'][] = 'alshaya_pdp_react/pdp_magazine_v2_layout';
  }

}
