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
    $config = $this->config('alshaya_acm_product.size_guide');
    $size_guide_enabled = $config->get('size_guide_enabled');
    $build = [];

    // If size guide is enabled on site.
    if ($size_guide_enabled) {
      $size_guide_content_nid = $config->get('size_guide_modal_content_node');
      $size_guide_content = '';
      if (!empty($size_guide_content_nid)) {
        $size_guide_content = $this->entityTypeManager()->getStorage('node')->load($size_guide_content_nid);
        $current_language = $this->languageManager()->getCurrentLanguage()->getId();
        // Get translated node object.
        $size_guide_content = $this->entityManager()->getTranslationFromContext($size_guide_content, $current_language);
        $size_guide_content->setTitle('');
        $size_guide_content = render($this->entityTypeManager()->getViewBuilder('node')->view($size_guide_content, 'full'));
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
