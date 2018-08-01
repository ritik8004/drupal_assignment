<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\acq_sku\Entity\SKU;
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
   * Title callback for the modal.
   */
  public function skumodalTitle($acq_sku) {
    $acq_sku = SKU::load($acq_sku);
    return $acq_sku->get('name')->getString();
  }

  /**
   * Page callback for the modal.
   */
  public function skumodalView($acq_sku, $js) {
    $acq_sku = SKU::load($acq_sku);
    if ($js === 'ajax') {
      $view_builder = $this->entityTypeManager()->getViewBuilder($acq_sku->getEntityTypeId());
      $build = $view_builder->view($acq_sku, 'modal');
      return $build;
    }

    $response = new RedirectResponse(Url::fromRoute('entity.acq_sku.canonical', ['acq_sku' => $acq_sku->id()])->toString());
    $response->send();
    exit;

  }

  /**
   * Page callback for modal content.
   */
  public function pdpModalLinkView($config_key = 'size_guide_modal_content_node') {
    $config = $this->config('alshaya_acm_product.pdp_modal_links');
    $content_nid = $config->get($config_key);
    $content = '';
    if (!empty($content_nid)) {
      $content = $this->entityTypeManager()->getStorage('node')->load($content_nid);
      $current_language = $this->languageManager()->getCurrentLanguage()->getId();
      // Get translated node object.
      $content = $this->entityManager()->getTranslationFromContext($content, $current_language);
      $content->setTitle('');
      $content = render($this->entityTypeManager()->getViewBuilder('node')->view($content, 'full'));
    }

    $build = [
      '#type' => 'inline_template',
      '#template' => '<div class="modal-content">{{ modal_content }}</div>',
      '#context' => [
        'modal_content' => $content,
      ],
    ];

    return $build;
  }

}
