<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\acq_sku\AcqSkuLinkedSku;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Cache\CacheableAjaxResponse;
use Drupal\Core\Config\ConfigFactoryInterface;

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
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   *   The HTTP request object.
   */
  protected $request;

  /**
   * ACM config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $acmConfig;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('request_stack'),
      $container->get('config.factory')
    );
  }

  /**
   * ProductController constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   */
  public function __construct(SkuManager $sku_manager, RequestStack $request_stack, ConfigFactoryInterface $config_factory) {
    $this->skuManager = $sku_manager;
    $this->request = $request_stack->getCurrentRequest();
    $this->acmConfig = $config_factory->get('alshaya_acm.settings');
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

    $build['modal_content'] = [
      '#type' => 'inline_template',
      '#template' => '<div class="modal-content">{{ modal_content }}</div>',
      '#context' => [
        'modal_content' => $content,
      ],
    ];

    if ($type == 'size-guide') {
      $build['#attached']['drupalSettings']['size_guide'] = [
        'params' => $this->request->query->all(),
      ];
    }
    return $build;
  }

  /**
   * Get matchback products.
   */
  public function getMatchbackProducts($sku, $type) {
    $response = new CacheableAjaxResponse();

    // Get matchback block for the product.
    if ($this->acmConfig->get('display_crosssell')) {
      $sku_entity = SKU::loadFromSku($sku);
      if ($sku_entity instanceof SKU) {
        if (!empty($cross_sell_skus = $this->skuManager->getLinkedSkusWithFirstChild($sku_entity, AcqSkuLinkedSku::LINKED_SKU_TYPE_CROSSSELL))) {
          $cross_sell_related_skus = $this->skuManager->filterRelatedSkus(array_unique($cross_sell_skus));
          $build['cross_sell'] = [
            '#theme' => 'products_horizontal_slider',
            '#data' => $cross_sell_related_skus,
            '#section_title' => t('Customers also bought', [], ['context' => 'alshaya_static_text|pdp_crosssell_title']),
            '#views_name' => 'product_slider',
            '#views_display_id' => $this->acmConfig->get('show_crosssell_as_matchback') ? 'block_matchback' : 'block_product_slider',
          ];
          $build['cross_sell_mobile'] = [
            '#theme' => 'products_horizontal_slider',
            '#data' => $cross_sell_related_skus,
            '#section_title' => t('Customers also bought', [], ['context' => 'alshaya_static_text|pdp_crosssell_title']),
            '#views_name' => 'product_slider',
            '#views_display_id' => 'block_product_slider',
          ];
          $response->addCommand(new ReplaceCommand('.above-mobile-block #matchback-products', render($build['cross_sell'])));
          $response->addCommand(new ReplaceCommand('.mobile-only-block #matchback-products', render($build['cross_sell_mobile'])));
        }
      }
    }

    return $response;
  }

  /**
   * Get upsell products.
   */
  public function getUpsellProducts($sku) {
    $response = new CacheableAjaxResponse();
    $sku_entity = SKU::loadFromSku($sku);

    if (!empty($up_sell_skus = $this->skuManager->getLinkedSkusWithFirstChild($sku_entity, AcqSkuLinkedSku::LINKED_SKU_TYPE_UPSELL))) {
      $build['up_sell'] = [
        '#theme' => 'products_horizontal_slider',
        '#data' => $this->skuManager->filterRelatedSkus(array_unique($up_sell_skus)),
        '#section_title' => t('You may also like', [], ['context' => 'alshaya_static_text|pdp_upsell_title']),
        '#views_name' => 'product_slider',
        '#views_display_id' => 'block_product_slider',
      ];
      $response->addCommand(new ReplaceCommand('.above-mobile-block #upsell-products', render($build)));
      $response->addCommand(new ReplaceCommand('.mobile-only-block #upsell-products', render($build)));
    }

    return $response;
  }

  /**
   * Get related products.
   */
  public function getRelatedProducts($sku) {
    $response = new CacheableAjaxResponse();
    $sku_entity = SKU::loadFromSku($sku);

    if (!empty($related_skus = $this->skuManager->getLinkedSkusWithFirstChild($sku_entity, AcqSkuLinkedSku::LINKED_SKU_TYPE_RELATED))) {
      $build['related'] = [
        '#theme' => 'products_horizontal_slider',
        '#data' => $this->skuManager->filterRelatedSkus(array_unique($related_skus)),
        '#section_title' => t('Related', [], ['context' => 'alshaya_static_text|pdp_related_title']),
        '#views_name' => 'product_slider',
        '#views_display_id' => 'block_product_slider',
      ];
      $response->addCommand(new ReplaceCommand('.above-mobile-block #related-products', render($build)));
      $response->addCommand(new ReplaceCommand('.mobile-only-block #related-products', render($build)));
    }

    return $response;
  }


}
