<?php

namespace Drupal\alshaya_mobile_app\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\acq_commerce\SKUInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\taxonomy\TermInterface;
use Drupal\node\NodeInterface;
use Drupal\file\FileInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;

/**
 * Utilty Class.
 */
class MobileAppUtility {

  use StringTranslationTrait;

  /**
   * Prefix used for the endpoint.
   */
  const ENDPOINT_PREFIX = '/rest/v1/';

  /**
   * Cache Backend service for alshaya.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The serializer.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * SKU manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * SKU images manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImagesManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Utility constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   The serializer.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU images manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(CacheBackendInterface $cache,
                              LanguageManagerInterface $language_manager,
                              RequestStack $request_stack,
                              AliasManagerInterface $alias_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              SerializerInterface $serializer,
                              SkuManager $sku_manager,
                              SkuImagesManager $sku_images_manager,
                              RendererInterface $renderer) {
    $this->cache = $cache;
    $this->languageManager = $language_manager;
    $this->requestStack = $request_stack->getCurrentRequest();
    $this->aliasManager = $alias_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->serializer = $serializer;
    $this->skuManager = $sku_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->renderer = $renderer;
  }

  /**
   * Get Deep link based on give object.
   *
   * @param object $object
   *   Object of node or term or query containing node/term data.
   * @param string $type
   *   (optional) String containing info about data incase of query object.
   *
   * @return string
   *   Return deeplink url.
   */
  public function getDeepLink($object, $type = '') {
    $return = '';

    if ($object instanceof TermInterface) {
      switch ($object->bundle()) {
        case 'acq_product_category':
          $return = 'category/' . $object->id() . '/product-list';
          break;
      }
    }
    elseif (is_object($object) && !empty($object->tid)) {
      // In case of categories resource, we not getting full object.
      $return = 'category/' . $object->tid . '/product-list';
    }
    elseif ($object instanceof NodeInterface) {
      switch ($object->bundle()) {
        case 'acq_product':
          // Get SKU attached with node.
          $sku = $object->get('field_skus')->getString();
          $return = 'product/' . $sku;
          break;

        case 'acq_promotion':
          $return = 'promotion/' . $object->id() . '/product-list';
          break;
      }
    }

    return self::ENDPOINT_PREFIX . $return;
  }

  /**
   * Get Deep link based on given object and field name.
   *
   * @param \Drupal\Core\Url $url
   *   The url Object.
   *
   * @return string
   *   Return deeplink url.
   */
  public function getDeepLinkFromUrl(Url $url) {
    $return = '';

    return self::ENDPOINT_PREFIX . $return;
  }

  /**
   * Get the alias language.
   *
   * @param string $alias
   *   The alias string.
   *
   * @return string
   *   Return the string of language code.
   */
  private function getAliasLang($alias) {
    $alias_lang = NULL;
    if ($this->languageManager->getCurrentLanguage()->getId() == 'ar' && !preg_match("/\p{Arabic}/u", $alias)) {
      $alias_lang = $this->languageManager->getDefaultLanguage()->getId();
    }
    elseif ($this->languageManager->getCurrentLanguage()->getId() == 'en' && preg_match("/\p{Arabic}/u", $alias)) {
      // Get the correct language, based on user input.
      $languages = $this->languageManager->getLanguages();
      if (count($languages) > 1 && array_key_exists('ar', $languages)) {
        $alias_lang = $languages['ar']->getId();
      }
    }
    return $alias_lang;
  }

  /**
   * Get node entity object from given alias.
   *
   * @param string $alias
   *   The alias to use to get entity.
   * @param string $bundle
   *   (optional) The bundle to validate entity against.
   *
   * @return \Drupal\node\NodeInterface|bool
   *   The node object, or FALSE if nothing found.
   */
  public function getNodeFromAlias($alias, $bundle = '') {
    // Get the internal path of given alias and get route parameters.
    $internal_path = $this->aliasManager->getPathByAlias('/' . $alias, $this->getAliasLang($alias));
    // Throw page not found error if internal path doesn't contain node path.
    if (strpos($internal_path, 'node') === FALSE) {
      return FALSE;
    }
    // Get the parameters, to get node id from internal path.
    $params = Url::fromUri("internal:" . $internal_path)->getRouteParameters();

    if (!empty($params['node']) && $node = $this->entityTypeManager->getStorage('node')->load($params['node'])) {
      if ($node instanceof NodeInterface && $node->bundle() == $bundle) {
        $langcode = $this->languageManager->getCurrentLanguage()->getId();
        if ($langcode !== $this->languageManager->getDefaultLanguage()->getId()) {
          if ($node->hasTranslation($langcode)) {
            $node = $node->getTranslation($langcode);
          }
        }
        return $node;
      }
    }
    return FALSE;
  }

  /**
   * Prepare multiple images array for given entity on given fieldname.
   *
   * @param object $entity
   *   The entity object.
   * @param string $field_name
   *   The field name from which it needs to create images array.
   *
   * @return array
   *   The array containing information of images.
   */
  public function getImages($entity, $field_name) {
    $images = [];
    if (!empty($entity->get($field_name)->getValue())) {
      foreach ($entity->get($field_name)->getValue() as $key => $value) {
        if (($file = $entity->get($field_name)->get($key)->entity) && $file instanceof FileInterface) {
          $images[] = file_create_url($file->getFileUri());
        }
      }
    }
    return $images;
  }

  /**
   * Helper function to throw an error.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function throwException() {
    throw new NotFoundHttpException($this->t("page not found"));
  }

  /**
   * Associative array containing paragraph type as key and callback function.
   *
   * Callback function is used to collect and order the field's data as we
   * want to send back as rest api response.
   *
   * @return array
   *   An associative array of paragraph type and callback function name.
   */
  public static function getParagraphCallbacks() {
    return [
      '1_row_3_col_delivery_banner' => 'getDeliveryBanner',
    ];
  }

  /**
   * Get field data of given entity reference revisions field.
   *
   * Note: Manual caching of entity requires when this method used, use
   * 'cache' key to loop through to addCacheableDependency.
   *
   * @param object $entity
   *   The entity object.
   * @param string $field
   *   The field name.
   *
   * @return array
   *   Return array with field data and cacheable objects.
   */
  public function getFieldData($entity, string $field): array {
    $context = ['langcode' => $this->languageManager->getDefaultLanguage()->getId()];
    $field_output = [
      'field_data' => [],
      'cache' => [],
    ];

    // While using normalize, we can't able to catch bubbleable_metadata for
    // entity's canonical link and some entity don't have canonical link
    // ie. paragraph. In render method of renderer any early render that
    // happens throws fatal error. To avoid this fatal error thrown by
    // \Drupal\Core\Render\Renderer::render(), we have to handle it manually.
    // that's why Manual caching of Paragraph entity requires when this
    // method used.
    // @see Drupal\Core\Render\RendererInterface::executeInRenderContext
    // @see Drupal\serialization\Normalizer\EntityReferenceFieldItemNormalizer::normalize
    $items = $this->renderer->executeInRenderContext(new RenderContext(), function () use ($entity, $field, $context) {
      return $this->serializer->normalize($entity->get($field), 'json', $context);
    });

    $field_output = ['cache' => [], 'blocks' => []];
    foreach ($items as $item) {
      $entity = $this->entityTypeManager->getStorage($item['target_type'])->load($item['target_id']);
      $field_output['cache'][] = $entity;
      // Prepare paragraph data based on given paragraph entity type.
      $data = $this->prepareParagraphData($entity, $field_output);
      // Collect items if the field has no recursive paragraphs.
      if ($field != 'field_promo_blocks') {
        $field_output['blocks']['type'] = empty($field_output['blocks']['type']) ? $entity->bundle() : $field_output['blocks']['type'];
        if (!isset($field_output['blocks']['items'])) {
          $field_output['blocks']['items'] = [];
        }
        $field_output['blocks']['items'][] = $data;
      }
      else {
        $field_output['blocks'] = !isset($field_output['blocks']) ? $data : array_merge($field_output['blocks'], $data);
      }
    }

    return $field_output;
  }

  /**
   * Prepare paragraph data based on given paragraph entity object.
   *
   * @param object $entity
   *   The paragraph entity object.
   * @param array $field_output
   *   The field output array to store cached data.
   *
   * @return array
   *   Return array of data.
   */
  public function prepareParagraphData($entity, array &$field_output) {
    $context = ['langcode' => $this->languageManager->getDefaultLanguage()->getId()];
    // Call a callback function to prepare data if paragraph type is one of the
    // paragraph types listed in getParagraphCallbacks().
    if (array_key_exists($entity->bundle(), $this->getParagraphCallbacks())) {
      return call_user_func_array([$this, $this->getParagraphCallbacks()[$entity->bundle()]], [$entity, $context]);
    }
    else {
      // @see self::getFieldData()
      $entity_normalized = $this->renderer->executeInRenderContext(new RenderContext(), function () use ($entity, $context) {
        return $this->serializer->normalize($entity, 'json', $context);
      });

      $data = [];
      foreach ($entity_normalized as $field_name => $field_values) {
        if (strpos($field_name, 'field_') !== FALSE && strpos($field_name, 'parent_field_') === FALSE) {
          foreach ($field_values as $field_value) {
            $data[] = $this->getNormalizedEntityReferenceData($field_value, $context, $field_output);
          }
        }
      }
    }
    return $data;
  }

  /**
   * Get '1_row_3_col_delivery_banner' paragraph type's data.
   *
   * @param object $entity
   *   The paragraph entity object.
   * @param array $context
   *   Context array to process normalize.
   *
   * @return array
   *   The converted array with necessary fields.
   */
  public function getDeliveryBanner($entity, array $context) {
    // Convert field link value.
    $url = $entity->get('field_link')->first()->getUrl();
    $url_string = $url->toString(TRUE);

    $data = [
      'title' => $entity->get('field_title')->getString(),
      'subtitle' => $entity->get('field_sub_title')->getString(),
      'url' => $url_string->getGeneratedUrl(),
      'deeplink' => $this->getDeepLinkFromUrl($url),
    ];
    return $data;
  }

  /**
   * The function to process normalized entity reference revision field data.
   *
   * @param array $item
   *   Normalize array containing target_id and target_type.
   * @param array $context
   *   Context array to process normalize.
   * @param array $field_output
   *   The array to collect cacheable objects.
   *
   * @return array
   *   Return data array.
   */
  private function getNormalizedEntityReferenceData(array $item, array $context, array &$field_output): array {
    if (!empty($item['target_type'])) {
      $entity = $this->entityTypeManager->getStorage($item['target_type'])->load($item['target_id']);
      $field_output['cache'][] = $entity;
      // @see self::getFieldData()
      $entity_normalized = $this->renderer->executeInRenderContext(new RenderContext(), function () use ($entity, $context) {
        return $this->serializer->normalize($entity, 'json', $context);
      });
      $data = [
        'type' => ($entity->getEntityTypeId() == 'paragraph') ? $entity->bundle() : $entity->getEntityTypeId(),
      ];

      foreach ($entity_normalized as $field_name => $field_data) {
        if (strpos($field_name, 'field_') !== FALSE && strpos($field_name, 'parent_field_') === FALSE) {
          $data[$field_name] = [];
          $row = [];
          foreach ($field_data as $field_delta => $field_item) {
            if (!empty($field_item['target_type'])) {
              $row[$field_delta] = $this->getNormalizedEntityReferenceData($field_item, $context, $field_output);
            }
            else {
              $row[$field_delta] = $field_item;
            }
          }
          $data[$field_name] = $row;
        }
      }
    }
    else {
      return array_merge(['type' => 'block'], $item);
    }
    return $data;
  }

  /**
   * Wrapper function get promotions.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Promotions.
   */
  public function getPromotions(SKUInterface $sku) {
    $promotions = [];
    $promotions_data = $this->skuManager->getPromotionsFromSkuId($sku, '', ['cart'], 'full', FALSE);
    foreach ($promotions_data as $nid => $promotion) {
      $promotion_node = $this->entityTypeManager->getStorage('node')->load($nid);
      $promotions[] = [
        'text' => $promotion['text'],
        'deeplink' => $this->getDeepLink($promotion_node, 'promotion'),
      ];
    }
    return $promotions;
  }

  /**
   * Wrapper function get labels and make the urls absolute.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $context
   *   Context.
   *
   * @return array
   *   Labels data.
   */
  public function getLabels(SKUInterface $sku, string $context): array {
    $labels = $this->skuManager->getLabels($sku, $context);

    if (empty($labels)) {
      return [];
    }

    foreach ($labels as &$label) {
      $doc = new \DOMDocument();
      $doc->loadHTML((string) $label['image']);
      $xpath = new \DOMXPath($doc);
      $label['image'] = Url::fromUserInput($xpath->evaluate("string(//img/@src)"), ['absolute' => TRUE])->toString();
    }

    return $labels;
  }

  /**
   * Wrapper function to get media items for an SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $context
   *   Context.
   *
   * @return array
   *   Media Items.
   */
  public function getMedia(SKUInterface $sku, string $context): array {
    $media = $this->skuImagesManager->getProductMedia($sku, $context);

    if (!isset($media['images_with_type'])) {
      $media['images_with_type'] = array_map(function ($image) {
        return [
          'url' => $image,
          'image_type' => 'image',
        ];
      }, array_values($media['images']));
    }

    return [
      'images' => $media['images_with_type'],
      'videos' => array_values($media['videos']),
    ];
  }

}
