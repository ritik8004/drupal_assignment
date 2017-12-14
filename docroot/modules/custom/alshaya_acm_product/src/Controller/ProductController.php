<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\node\entity\Node;

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
    $config = \Drupal::config('alshaya_acm_product.size_guide');
    $size_guide_enabled = $config->get('size_guide_enabled');
    $build = [];

    // If size guide is enabled on site.
    if ($size_guide_enabled) {
      $size_guide_content_nid = $config->get('size_guide_modal_content_node');
      $size_guide_content = '';
      if (!empty($size_guide_content_nid)) {
        $size_guide_content = Node::load($size_guide_content_nid);
        $size_guide_content = \Drupal::entityTypeManager()->getViewBuilder('node')->view($size_guide_content, 'full');
      }

      $build = [
        '#type' => 'inline_template',
        '#template' => '<div class="size-guide-content">{{ size_guide_content }}</div>',
        '#context' => [
          'size_guide_content' => $size_guide_content,
        ],
      ];
    }
    return $build;
  }

}
