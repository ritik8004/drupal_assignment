<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
  public function modalView(EntityInterface $node, $js) {
    if ($js === 'ajax') {
      $view_builder = $this->entityTypeManager()->getViewBuilder($node->getEntityTypeId());
      $build = $view_builder->view($node, 'modal');
      return $build;
    }

    $response = new RedirectResponse(Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString());
    $response->send();
    exit;

  }

  /**
   * Page callback for size guide modal.
   */
  public function sizeGuideModal() {
    $product_config = \Drupal::config('alshaya_acm_product.size_guide');
    $size_guide_enabled = $product_config->get('size_guide_link');
    $build = [];

    // If size guide is enabled on site.
    if ($size_guide_enabled) {
      $size_guide_content = $product_config->get('size_guide_modal_content.value');
      $build = [
        '#type' => 'inline_template',
        '#template' => '<div class="size-guide-content">{{ size_guide_content | raw }}</div>',
        '#context' => [
          'size_guide_content' => $size_guide_content,
        ],
      ];
    }
    return $build;
  }

}
