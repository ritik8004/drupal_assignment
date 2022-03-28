<?php

namespace Drupal\alshaya_pdp_layouts\Plugin\PdpLayout;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for Pdp Layout plugins.
 */
abstract class PdpLayoutBase extends PluginBase implements PdpLayoutInterface {

  /**
   * {@inheritdoc}
   */
  public function getLayoutId() {
    return $this->pluginId;
  }

  /**
   * {@inheritdoc}
   */
  public function getLayoutName() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getTemplateName(array &$suggestions, string $bundle) {
    switch ($bundle) {
      case 'rcs_product':
        $suggestions[] = 'node__rcs_product__full';
        break;

      default:
        $suggestions[] = 'node__acq_product__full';
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderArray(array &$variables) {
    return $variables;
  }

  /**
   * {@inheritdoc}
   */
  public function getCotextFromPdpLayout($context, $pdp_layout) {
    return $context;
  }

}
