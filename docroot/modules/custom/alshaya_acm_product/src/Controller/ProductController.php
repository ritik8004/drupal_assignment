<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\acq_commerce\SKUInterface;
use Drupal\alshaya_acm_product\SkuImagesHelper;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Site\Settings;
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
use Drupal\Core\Extension\ModuleHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Drupal\Core\Entity\EntityRepositoryInterface;

/**
 * Class Product Controller.
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
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Sku images helper.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesHelper
   */
  protected $skuImagesHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('request_stack'),
      $container->get('config.factory'),
      $container->get('alshaya_acm_product.sku_images_manager'),
      $container->get('module_handler'),
      $container->get('entity.repository'),
      $container->get('alshaya_acm_product.sku_images_helper')
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
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   The Module Handler service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\alshaya_acm_product\SkuImagesHelper $images_helper
   *   Sku imagese helper.
   */
  public function __construct(
      SkuManager $sku_manager,
      RequestStack $request_stack,
      ConfigFactoryInterface $config_factory,
      SkuImagesManager $sku_image_manager,
      ModuleHandler $module_handler,
      EntityRepositoryInterface $entity_repository,
      SkuImagesHelper $images_helper
    ) {
    $this->skuManager = $sku_manager;
    $this->request = $request_stack->getCurrentRequest();
    $this->acmConfig = $config_factory->get('alshaya_acm.settings');
    $this->skuImageManager = $sku_image_manager;
    $this->moduleHandler = $module_handler;
    $this->entityRepository = $entity_repository;
    $this->skuImagesHelper = $images_helper;
  }

  /**
   * Title callback for the modal.
   *
   * @param string $code
   *   The SKU code or node id.
   *
   * @return string
   *   The label of the node.
   */
  public function modalTitle(string $code) {
    // Do nothing for requests from bots.
    if (Settings::get('product_quick_view_block_json_requests', 1)
      && $this->request->query->get('_wrapper_format') === 'json') {
      return '';
    }

    try {
      $node = $this->getProductNode($code);
    }
    catch (HttpException $e) {
      return new Response($e->getMessage(), $e->getStatusCode());
    }

    return $node->label();
  }

  /**
   * Page callback for the modal.
   *
   * @param string $code
   *   The SKU code or node id.
   * @param string $js
   *   Indicates whether request is AJAX request or not.
   *
   * @return array
   *   The render array of the node if it is an AJAX request. Else redirects
   *   users to node page.
   */
  public function modalView(string $code, $js) {
    // Do nothing for requests from bots.
    if (Settings::get('product_quick_view_block_json_requests', 1)
      && $this->request->query->get('_wrapper_format') === 'json') {
      // Return empty JSON response so full page with empty body is not loaded.
      return new CacheableJsonResponse([]);
    }

    try {
      $node = $this->getProductNode($code);
    }
    catch (HttpException $e) {
      return new Response($e->getMessage(), $e->getStatusCode());
    }

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
    $build = [];
    // Type mapping.
    $types = [
      'size-guide' => 'size_guide_modal_content_node',
      'delivery' => 'delivery_content_node',
    ];
    // Redirect to 404 page if type is invalid.
    if (!isset($types[$type])) {
      throw new NotFoundHttpException();
    }
    $content_nid = $this->config('alshaya_acm_product.pdp_modal_links')->get($types[$type]);
    $content = '';
    if (!empty($content_nid)) {
      $node = $this->entityTypeManager()->getStorage('node')->load($content_nid);

      if ($node instanceof NodeInterface) {
        // Get translated node object.
        $node = $this->entityRepository->getTranslationFromContext($node);

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

    // Allow other modules to alter build.
    $this->moduleHandler->alter('alshaya_acm_product_modal_build', $build);

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

    // Base64 decode sku from url.
    $sku = base64_decode($sku);
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
            'views_display_id' => $this->acmConfig->get('show_crosssell_as_matchback') ? ($device == 'desktop' ? 'block_matchback' : 'block_matchback_mobile') : 'block_product_slider',
          ];
        }
      }
      elseif ($type === 'upsell') {
        if ($this->acmConfig->get('display_upsell')) {
          $data = [
            'section_title' => $this->t('You may also like', [], ['context' => 'alshaya_static_text|pdp_upsell_title']),
            'views_display_id' => 'block_product_slider',
          ];
        }
      }
      elseif ($type === 'related') {
        if ($this->acmConfig->get('display_related')) {
          $data = [
            'section_title' => $this->t('Related', [], ['context' => 'alshaya_static_text|pdp_related_title']),
            'views_display_id' => 'block_product_slider',
          ];
        }
      }

      if (!empty($data)) {
        // Getting the json response
        // for new PDP layout.
        if ($this->request->query->get('type') == 'json') {
          $related_products = $this->getRelatedProductsJson($related_skus, $data);
          if (!empty($related_products)) {
            $hook_data = [
              'type' => $type,
              'products' => $related_products,
              'format' => 'json',
            ];
            $this->moduleHandler->alter('alshaya_acm_product_recommended_products_data', $hook_data);
          }
          return new JsonResponse($related_products);
        }

        $hook_data = [
          'type' => $type,
          'products' => $related_skus,
          'format' => NULL,
          'data' => $data,
        ];
        if (!empty($related_skus)) {
          $this->moduleHandler->alter('alshaya_acm_product_recommended_products_data', $hook_data);
        }

        $build['related'] = [
          '#theme' => 'products_horizontal_slider',
          '#data' => $related_skus,
          '#section_title' => $hook_data['data']['section_title'],
          '#views_name' => 'product_slider',
          '#views_display_id' => $hook_data['data']['views_display_id'],
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
    $related_products = [];
    foreach ($related_skus as $related_sku => $value) {
      $related_sku_entity = SKU::loadFromSku($related_sku);
      if ($related_sku_entity instanceof SKU) {
        $sku_media = $this->skuImageManager->getFirstImage($related_sku_entity);

        if (!empty($sku_media)) {
          $image = $this->skuImagesHelper->getImageStyleUrl($sku_media, SkuImagesHelper::STYLE_PRODUCT_SLIDE);
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
        $node = $this->skuManager->getDisplayNode($related_sku_entity);
        if (!($node instanceof NodeInterface)) {
          throw new NotFoundHttpException();
        }
        $link = $node
          ? $node->toUrl('canonical', ['absolute' => TRUE])
            ->toString(TRUE)
            ->getGeneratedUrl()
          : '';
        $related_products['products'][$related_sku]['productUrl'] = $link;
      }
    }
    return $related_products;
  }

}
