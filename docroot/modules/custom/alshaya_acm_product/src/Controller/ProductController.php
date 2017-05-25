<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class ProductController.
 */
class ProductController extends ControllerBase {

  /**
   * Title callback for the modal.
   */
  public function modalTitle(EntityInterface $node) {
    return $node->label();
  }

  /**
   * Page callback for the modal.
   */
  public function modalView(EntityInterface $node) {
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder($node->getEntityTypeId());
    $build = $view_builder->view($node, 'modal');
    return $build;
  }

  /**
   * Page callback for size guide modal.
   */
  public function sizeGuideModal() {
    $product_config = \Drupal::config('alshaya_acm_product.settings');
    $size_guide_enabled = $product_config->get('size_guide_link');
    $build = [];

    // If size guide is enabled on site.
    if ($size_guide_enabled) {
      $size_guide_content = $product_config->get('size_guide_modal_content.value');
      $build = [
        '#markup' => $size_guide_content,
      ];
    }
    return $build;
  }

}
