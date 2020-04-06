<?php

namespace Drupal\alshaya_pdp_layouts\Plugin\PdpLayout;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides the default laypout for PDP.
 *
 * @PdpLayout(
 *   id = "default",
 *   label = @Translation("Default"),
 * )
 */
class DefaultPdpLayout extends PdpLayoutBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getTemplateName() {
    return 'node__acq_product__full';
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderArray(array &$variables) {

  }

}
