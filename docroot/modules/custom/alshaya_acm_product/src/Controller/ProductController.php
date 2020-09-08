<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\acq_commerce\SKUInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
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
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Cache\CacheableMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ProductController.
 */
class ProductController extends ControllerBase {

  use StringTranslationTrait;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   */
  public function __construct(SkuManager $sku_manager, RequestStack $request_stack, ConfigFactoryInterface $config_factory) {
    $this->skuManager = $sku_manager;
    $this->request = $request_stack->getCurrentRequest();
    $this->acmConfig = $config_factory->get('alshaya_acm.settings');
  }

  /**
   * Title callback for the modal.
   */
  public function modalTitle(string $code) {
    $node = $this->getProductNode($code);
    return $node->label();
  }

  /**
   * Page callback for the modal.
   */
  public function modalView(string $code, $js) {
    $node = $this->getProductNode($code);
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
   * Returns the product node.
   *
   * @param string $code
   *   The node id or SKU code.
   *
   * @return \Drupal\node\NodeInterface
   *   The node object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   If the node object could not be loaded.
   */
  public function getProductNode(string $code) {
    static $static;
    if (isset($static)) {
      return $static;
    }
    if (($sku_entity = SKU::loadFromSku($code)) && ($sku_entity instanceof SKUInterface)) {
      $node = $this->skuManager->getDisplayNode($sku_entity);
      if (!($node instanceof NodeInterface)) {
        throw new NotFoundHttpException();
      }
    }
    elseif (($node = $this->entityTypeManager()->getStorage('node')->load($code)) && ($node instanceof NodeInterface)) {
    }
    else {
      throw new NotFoundHttpException('Could not load the provided entity.');
    }

    $static = $node;
    return $node;
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
   * Get related products.
   *
   * @param string $sku
   *   SKU as string.
   * @param string $type
   *   Type of related products to get.
   * @param string $device
   *   Device type.
   *
   * @return \Drupal\Core\Cache\CacheableAjaxResponse
   *   Response object.
   */
  public function getRelatedProducts(string $sku, string $type, string $device) {
    $response = new CacheableAjaxResponse();

    // Sanity check.
    if (!in_array($type, [
      AcqSkuLinkedSku::LINKED_SKU_TYPE_CROSSSELL,
      AcqSkuLinkedSku::LINKED_SKU_TYPE_UPSELL,
      AcqSkuLinkedSku::LINKED_SKU_TYPE_RELATED,
    ])) {
      throw new NotFoundHttpException();
    }

    $sku_entity = SKU::loadFromSku($sku);
    if (!($sku_entity instanceof SKU)) {
      throw new NotFoundHttpException();
    }

    $node = $this->skuManager->getDisplayNode($sku_entity);
    if (!($node instanceof NodeInterface)) {
      throw new NotFoundHttpException();
    }

    $build = [];
    $build['#cache']['tags'] = $this->acmConfig->getCacheTags();
    $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'], $node->getCacheTags());

    $related_skus = $this->skuManager->getLinkedSkusWithFirstChild($sku_entity, $type);
    if (!empty($related_skus)) {
      $related_skus = $this->skuManager->filterRelatedSkus(array_unique($related_skus));
      $selector = ($device == 'mobile') ? '.mobile-only-block ' : '.above-mobile-block ';
      $data = [];

      if ($type === 'crosssell') {
        if ($this->acmConfig->get('display_crosssell')) {
          $data = [
            'section_title' => $this->t('Customers also bought', [], ['context' => 'alshaya_static_text|pdp_crosssell_title']),
            'views_display_id' => ($this->acmConfig->get('show_crosssell_as_matchback') && $device == 'desktop') ? 'block_matchback' : 'block_product_slider',
          ];
        }
      }
      elseif ($type === 'upsell') {
        $data = [
          'section_title' => $this->t('You may also like', [], ['context' => 'alshaya_static_text|pdp_upsell_title']),
          'views_display_id' => 'block_product_slider',
        ];
      }
      elseif ($type === 'related') {
        $data = [
          'section_title' => $this->t('Related', [], ['context' => 'alshaya_static_text|pdp_related_title']),
          'views_display_id' => 'block_product_slider',
        ];
      }

      if (!empty($data)) {
        $build['related'] = [
          '#theme' => 'products_horizontal_slider',
          '#data' => $related_skus,
          '#section_title' => $data['section_title'],
          '#views_name' => 'product_slider',
          '#views_display_id' => $data['views_display_id'],
        ];
        $response->addCommand(new ReplaceCommand($selector . '.' . $type . '-products', render($build)));
      }
    }

    // Add cache metadata.
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray($build));

    return $response;
  }

}
