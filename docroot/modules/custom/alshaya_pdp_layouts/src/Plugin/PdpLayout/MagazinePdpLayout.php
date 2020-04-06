<?php

namespace Drupal\alshaya_pdp_layouts\Plugin\PdpLayout;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides the default laypout for PDP.
 *
 * @PdpLayout(
 *   id = "magazine",
 *   label = @Translation("Magazine"),
 * )
 */
class MagazinePdpLayout extends PdpLayoutBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getTemplateName(array &$suggestions) {
    $suggestions[] = 'node__acq_product__full_magazine';
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderArray(array &$variables) {
    $variables['#attached']['library'][] = 'alshaya_white_label/magazine_attribute';
    $variables['#attached']['library'][] = 'alshaya_white_label/magazine_socialSharepopup';
    $variables['#attached']['drupalSettings']['color_swatches_hover'] = TRUE;
  }

}
