<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\Core\Cache\CacheableMetadata;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\rest\ResourceResponse;
use Drupal\node\NodeInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\alshaya_acm_product\Service\SkuPriceHelper;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;

/**
 * Provides a resource to get deeplink.
 *
 * @RestResource(
 *   id = "magazine_detail_page",
 *   label = @Translation("Magazine Detail Page"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/page/magazine-detail"
 *   }
 * )
 */
class MagazineDetailPage extends ResourceBase {

  /**
   * Node bundle machine name.
   */
  const NODE_TYPE = 'magazine_article';
  /**
   * The SKU entity manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;
  /**
   * The core Render service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;
  /**
   * The SKU entity info service.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuInfoHelper
   */
  protected $skuInfo;
  /**
   * The SKU entity price service.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuPriceHelper
   */
  protected $skuPrice;
  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;


  /**
   * AdvancedPageResource constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param array $serializer_formats
   *   Serializer formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   The SKU manager.
   * @param \Drupal\alshaya_acm_product\Service\SkuInfoHelper $skuInfo
   *   The SKU Info.
   * @param \Drupal\alshaya_acm_product\Service\SkuPriceHelper $skuPrice
   *   The SKU price.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer interface.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    MobileAppUtility $mobile_app_utility,
    RequestStack $request_stack,
    SkuManager $skuManager,
    SkuInfoHelper $skuInfo,
    SkuPriceHelper $skuPrice,
    RendererInterface $renderer,
    EntityRepositoryInterface $entity_repository
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->mobileAppUtility = $mobile_app_utility;
    $this->requestStack = $request_stack->getCurrentRequest();
    $this->sku_manager = $skuManager;
    $this->sku_info = $skuInfo;
    $this->sku_price = $skuPrice;
    $this->renderer = $renderer;
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('alshaya_mobile_app'),
      $container->get('alshaya_mobile_app.utility'),
      $container->get('request_stack'),
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('alshaya_acm_product.sku_info'),
      $container->get('alshaya_acm_product.price_helper'),
      $container->get('renderer'),
      $container->get('entity.repository')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response returns the deeplink.
   */
  public function get() {
    // Path alias of magazine article.
    $alias = $this->requestStack->query->get('url');
    $node = $this->mobileAppUtility->getNodeFromAlias($alias, self::NODE_TYPE);

    if (!$node instanceof NodeInterface) {
      $this->mobileAppUtility->throwException();
    }

    if (!$node->isPublished()) {
      $this->mobileAppUtility->throwException();
    }
    $node = $this->entityRepository->getTranslationFromContext($node);
    // Get bubbleable metadata for CacheableDependency to avoid fatal error.
    $node_url_obj = Url::fromRoute('entity.node.canonical', ['node' => $node->id()]);
    $node_url = $node_url_obj->toString(TRUE);

    $response_data = [
      'id' => (int) $node->id(),
      'name' => $node->label(),
      'path' => $node_url->getGeneratedUrl(),
    ];
    if ($node->get('field_magazine_hero_image')->getValue()) {
      $response_data['image'] = $this->mobileAppUtility->getImages($node, 'field_magazine_hero_image');
    }
    if ($node->hasField('field_magazine_category') && $node->field_magazine_category) {
      $magazine_category = $node->get('field_magazine_category')->referencedEntities();
      foreach ($magazine_category as $magazine_category_value) {
        $magazine_category_entity = $this->entityRepository->getTranslationFromContext($magazine_category_value);
        $magazine_category_data['id'] = (int) $magazine_category_entity->id();
        $magazine_category_data['name'] = $magazine_category_entity->getName();
        $response_data['magazine_category'] = $magazine_category_data;
      }
    }
    if ($node->get('field_magazine_slogan')->getValue()) {
      $response_data['slogan'] = $node->field_magazine_slogan->getValue()[0]['value'];
    }
    if (!empty($node->field_magazine_date->getValue())) {
      $magazine_date = $node->field_magazine_date->getValue()[0]['value'];
      $response_data['date'] = format_date(strtotime($magazine_date), 'magazine_date');
    }
    if ($node->hasField('field_magazine_paragraphs') && !empty($node->field_magazine_paragraphs)) {
      $magazine_paragraphs = $node->field_magazine_paragraphs->referencedEntities();
      foreach ($magazine_paragraphs as $magazine_paragraphs_value) {
        $item = $this->entityRepository->getTranslationFromContext($magazine_paragraphs_value);
        if ($item->bundle() == 'title_textarea') {
          $res['type'] = $item->bundle();
          if ($item->get('field_body')->getValue()) {
            $res['body'] = $item->get('field_body')->first()->getValue()['value'];
          }
          if (!empty($item->get('field_html_heading')->getValue())) {
            $res['heading'] = $item->get('field_html_heading')->first()->getValue()['value'];
          }
        }
        elseif ($item->bundle() == 'image_title_subtitle') {
          $res['type'] = $item->bundle();
          if ($item->get('field_banner')->getValue()) {
            $res['image'] = $this->mobileAppUtility->getImages($item, 'field_banner');
          }
          if (!empty($item->get('field_link')->first())) {
            $url = $item->get('field_link')->first()->getUrl();
            $url_string = $url->toString(TRUE);
            $res['link'] = $url_string->getGeneratedUrl();
          }
          if (!empty($item->get('field_sub_title')->getValue())) {
            $res['sub_title'] = $item->get('field_sub_title')->getValue()[0]['value'];
          }
          if (!empty($item->get('field_title')->getValue())) {
            $res['title'] = $item->get('field_title')->getValue()[0]['value'];
          }
        }
        elseif ($item->bundle() == 'video' && $item->hasField('field_video') && !empty($item->field_video)) {
          if (!empty($video = $item->field_video->referencedEntities())) {
            $res['type'] = $item->bundle();
            foreach ($video as $items) {
              $res1['media_id'] = (int) $items->id();
              if (!empty($items->field_media_video_embed_field->getValue())) {
                $res1['embed_video_url'] = $items->field_media_video_embed_field->getValue()[0]['value'];
              }
              if (!empty($items->field_document->entity)) {
                $res1['video_url'] = file_create_url($items->field_document->entity->getFileUri());
              }
              if (!empty($items->field_media_in_library->getValue())) {
                $res1['media_library_flag'] = (int) $items->field_media_in_library->getValue()[0]['value'];
              }
              $res['media'] = $res1;
              unset($res1);
            }
          }
        }
        if (!empty($res)) {
          $response_data['Paragraphs'][] = $res;
          unset($res);
        }
      }
    }
    if ($node->hasField('field_magazine_shop_the_story') && !empty($magazine_shop = $node->get('field_magazine_shop_the_story')->getValue())) {
      foreach ($magazine_shop as $value) {
        $parent_sku = $this->sku_manager->getParentSkuBySku($value['value']);
        if (is_object($parent_sku)) {
          if ($parent_sku->get('name')->getValue()) {
            $res1['sku_name'] = $parent_sku->get('name')->getValue()[0]['value'];
          }
          $sku_media = $this->sku_info->getMedia($parent_sku, 'teaser');
          if (!empty($sku_media['images']) && is_array($sku_media['images'])) {
            $res1['image_url'] = $sku_media['images'][0]['url'];
          }
          $sku_price = $this->renderer->renderPlain($this->sku_price->getPriceBlockForSku($parent_sku));
          $price = strip_tags($sku_price->__toString());
          $res1['sku_final_price'] = trim(preg_replace('/\s\s+/', ' ', $price));
          $sku_node_id = $this->sku_manager->getDisplayNode($parent_sku, FALSE);
          $sku_node_url_obj = Url::fromRoute('entity.node.canonical', ['node' => $sku_node_id->id()]);
          $sku_node_url = $sku_node_url_obj->toString(TRUE);
          $res1['path'] = $sku_node_url->getGeneratedUrl();
          $response_data['sku_data']['items'][] = $res1;
        }
        unset($res1);
      }
      if (array_key_exists('sku_data', $response_data)) {
        $response_data['sku_data']['label'] = $node->field_magazine_shop_the_story->getFieldDefinition()->getLabel();
      }
    }
    $response = new ResourceResponse($response_data);
    $response->addCacheableDependency($node);
    foreach ($this->mobileAppUtility->getCacheableEntities() as $cacheable_entity) {
      $response->addCacheableDependency($cacheable_entity);
    }

    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'contexts' => [
          'url.query_args:url',
        ],
        'tags' => array_merge([
          ProductCategoryTree::CACHE_TAG,
          'node_view',
          'paragraph_view',
        ], $this->mobileAppUtility->getBlockCacheTags()),
      ],
    ]));
    return $response;
  }

}
