<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Cache\Cache;
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
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Cache\CacheableMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\image\Entity\ImageStyle;
use Drupal\image\ImageStyleInterface;
use Drupal\acq_commerce\SKUInterface;

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
   * The SKU Image Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('request_stack'),
      $container->get('config.factory'),
      $container->get('alshaya_acm_product.sku_images_manager')
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
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_image_manager
   *   The SKU Image Manager.
   */
  public function __construct(SkuManager $sku_manager, RequestStack $request_stack, ConfigFactoryInterface $config_factory, SkuImagesManager $sku_image_manager) {
    $this->skuManager = $sku_manager;
    $this->request = $request_stack->getCurrentRequest();
    $this->acmConfig = $config_factory->get('alshaya_acm.settings');
    $this->skuImageManager = $sku_image_manager;
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
        // Getting the json response
        // for new PDP layout.
        if ($this->request->query->get('type') == 'json') {
          $related_products = $this->getRelatedProductsJson($related_skus, $data);
          return new JsonResponse($related_products);
        }
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

  /**
   * Get related products.
   *
   * @param array $related_skus
   *   Related SKUs array.
   * @param array $data
   *   Data array.
   *
   * @return array
   *   Related products data.
   */
  public function getRelatedProductsJson(array $related_skus, array $data) {
    foreach ($related_skus as $related_sku => $value) {
      $related_sku_entity = SKU::loadFromSku($related_sku);
      if ($related_sku_entity instanceof SKUInterface) {
        $sku_media = $this->skuImageManager->getFirstImage($related_sku_entity);

        if (!empty($sku_media['drupal_uri'])) {
          $image_style = ImageStyle::load('product_zoom_medium_606x504');
          if ($image_style instanceof ImageStyleInterface) {
            $image = $image_style->buildUrl($sku_media['drupal_uri']);
          }
        }
        $priceHelper = _alshaya_acm_product_get_price_helper();
        $related_sku_price = $priceHelper->getPriceBlockForSku($related_sku_entity, []);
        $price = $related_sku_price['#price']['#price'];
        $final_price = isset($related_sku_price['#final_price']) ? $related_sku_price['#final_price']['#price'] : $price;
        $title = $related_sku_entity->label();
        $related_products['products'][$related_sku]['gallery']['mediumurl'] = $image;
        $related_products['products'][$related_sku]['finalPrice'] = $final_price;
        $related_products['products'][$related_sku]['priceRaw'] = $price;
        $related_products['products'][$related_sku]['title'] = $title;
        $related_products['products'][$related_sku]['productLabels'] = $this->skuManager->getLabelsData($related_sku_entity, 'pdp');
        $related_products['products'][$related_sku]['promotions'] = $this->skuManager->getPromotions($related_sku_entity);
        $related_products['section_title'] = render($data['section_title']);
      }
    }
    return $related_products;
  }

}
