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
use Drupal\Core\Datetime\DateFormatterInterface;

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
  public const NODE_TYPE = 'magazine_article';
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
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

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
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   Current time service.
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
    EntityRepositoryInterface $entity_repository,
    DateFormatterInterface $date_formatter
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->mobileAppUtility = $mobile_app_utility;
    $this->requestStack = $request_stack->getCurrentRequest();
    $this->sku_manager = $skuManager;
    $this->sku_info = $skuInfo;
    $this->sku_price = $skuPrice;
    $this->renderer = $renderer;
    $this->entityRepository = $entity_repository;
    $this->dateFormatter = $date_formatter;
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
      $container->get('entity.repository'),
      $container->get('date.formatter')
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
    $res = NULL;

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
      $response_data['image'] = $this->mobileAppUtility->getImages($node, 'field_magazine_hero_image', 'magazine_article_hero');
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
      $response_data['date'] = $this->dateFormatter->format(strtotime($magazine_date), 'magazine_date');
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
              if (!empty($items->field_media_oembed_video->getValue())) {
                $res1['embed_video_url'] = $items->field_media_oembed_video->getValue()[0]['value'];
              }
              if (!empty($items->field_media_document->entity)) {
                $res1['video_url'] = file_create_url($items->field_media_document->entity->getFileUri());
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
          $response_data['magazine_paragraphs'][] = $res;
          unset($res);
        }
      }
    }
    // Get shop the story.
    if ($node->hasField('field_magazine_shop_the_story') && !empty($node->get('field_magazine_shop_the_story')->getValue())) {
      $response_data['shop_the_story'] = $this->getShopTheStory($node);
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

  /**
   * Get shop the story product details.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Magazine detail node.
   *
   * @return array
   *   Returns product details for shop the story.
   */
  protected function getShopTheStory(NodeInterface $node) {
    $shop_the_story = [];
    $skus = $node->get('field_magazine_shop_the_story')->getValue();
    foreach ($skus as $value) {
      $sku_data = [];
      $node_data = $this->sku_manager->getDisplayNode($value['value']);
      if (is_object($node_data)) {
        $sku_data = $this->mobileAppUtility->getLightProductFromNid($node_data->get('nid')->getValue()[0]['value'], $this->mobileAppUtility->currentLanguage());
        $shop_the_story['items'][] = $sku_data;
      }
    }
    if (array_key_exists('shop_the_story', $shop_the_story)) {
      $shop_the_story['label'] = $node->field_magazine_shop_the_story->getFieldDefinition()->getLabel();
    }

    return $shop_the_story;
  }

}
