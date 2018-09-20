<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ProductController.
 */
class ProductController extends ControllerBase {

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm_product.skumanager')
    );
  }

  /**
   * ProductController constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   */
  public function __construct(SkuManager $sku_manager) {
    $this->skuManager = $sku_manager;
  }

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
    $sku = $this->skuManager->loadSkuById((int) $acq_sku);
    return $sku->get('name')->getString();
  }

  /**
   * Page callback for the modal.
   */
  public function skumodalView($acq_sku, $js) {
    $sku = $this->skuManager->loadSkuById((int) $acq_sku);
    if ($js === 'ajax') {
      $view_builder = $this->entityTypeManager()->getViewBuilder($sku->getEntityTypeId());
      $build = $view_builder->view($sku, 'modal');
      return $build;
    }

    $response = new RedirectResponse(Url::fromRoute('entity.acq_sku.canonical', ['acq_sku' => $sku->id()])->toString());
    $response->send();
    exit;

  }

  /**
   * Page callback for modal content.
   */
  public function pdpModalLinkView($type = 'size-guide') {
    // Type mapping.
    $types = [
      'size-guide' => 'size_guide_modal_content_node',
      'delivery' => 'delivery_content_node',
    ];
    $content_nid = $this->config('alshaya_acm_product.pdp_modal_links')->get($types[$type]);
    $content = '';
    if (!empty($content_nid)) {
      $node = $this->entityTypeManager()->getStorage('node')->load($content_nid);

      if ($node instanceof NodeInterface) {
        // Get translated node object.
        $node = $this->entityManager()->getTranslationFromContext($node);

        // Set the title to empty string. We don't want to display title.
        $node->setTitle('');

        // Prepare content using full view mode.
        $content = $this->entityTypeManager()->getViewBuilder('node')->view($node, 'full');
      }
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
