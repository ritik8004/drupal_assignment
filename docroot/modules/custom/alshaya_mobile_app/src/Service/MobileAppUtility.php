<?php

namespace Drupal\alshaya_mobile_app\Service;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\acq_commerce\SKUInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
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
use Drupal\paragraphs\ParagraphInterface;
use Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface;

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
   * The array of objects to cache.
   *
   * @var array
   */
  protected $cachedEntities = [];

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Product category tree.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface
   */
  protected $productCategoryTree;

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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface $product_category_tree
   *   Product category tree.
   */
  public function __construct(CacheBackendInterface $cache,
                              LanguageManagerInterface $language_manager,
                              RequestStack $request_stack,
                              AliasManagerInterface $alias_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              SerializerInterface $serializer,
                              SkuManager $sku_manager,
                              SkuImagesManager $sku_images_manager,
                              RendererInterface $renderer,
                              ModuleHandlerInterface $module_handler,
                              ProductCategoryTreeInterface $product_category_tree) {
    $this->cache = $cache;
    $this->languageManager = $language_manager;
    $this->requestStack = $request_stack->getCurrentRequest();
    $this->aliasManager = $alias_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->serializer = $serializer;
    $this->skuManager = $sku_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->renderer = $renderer;
    $this->moduleHandler = $module_handler;
    $this->productCategoryTree = $product_category_tree;
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
          $department_node = alshaya_advanced_page_is_department_page($object->id());
          // If department page node.
          if ($department_node) {
            $return = 'rest/v1/page/advanced?url=node/' . $department_node;
          }
          else {
            $return = 'category/' . $object->id() . '/product-list';
          }
          break;
      }
    }
    elseif (is_object($object) && !empty($object->tid)) {
      // In case of categories resource, we not getting full object.
      // If category is department page node.
      $department_node = alshaya_advanced_page_is_department_page($object->tid);
      if ($department_node) {
        $return = 'rest/v1/page/advanced?url=node/' . $department_node;
      }
      else {
        $return = 'category/' . $object->tid . '/product-list';
      }
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
   * Get Deep link based on given url object.
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
   * @param string $label
   *   (optional) The label.
   * @param string $type
   *   (optional) The type of the field.
   *
   * @return array
   *   The array containing information of images.
   */
  public function getImages($entity, $field_name, $label = NULL, $type = NULL) {
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
   * Return array of cached entities.
   *
   * @return array
   *   Return array of cached entities.
   */
  public function getCachedEntities() {
    return $this->cachedEntities;
  }

  /**
   * Get fields required for given entity bundle.
   *
   * Returns fields that requires for given entity bundle, with optional
   * information like type, label and callback. To process field to get value
   * with required label and type, processed by given callback method.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle of entity.
   *
   * @return array
   *   Return array of just fields or contains associative array with (label,
   *   type, callback).
   *   - label: Field label to return output with.
   *   - type: Return output of a field with type and items.
   *      i.e. {"type": "banner", "items": []}
   *   - callback: Callback method to process field to get desired output.
   *
   *   Examples:
   *
   *   An array with just fields.
   *
   * @code
   *   return [
   *     'field_promo_blocks',
   *     'field_delivery_banner',
   *     'field_promo_banner_full_width',
   *     'field_related_info',
   *     'field_slider',
   *   ];
   *
   * Array with label, type and callback.
   *   return [
   *     'field_title' => ['label' => 'title'],
   *     'field_sub_title' => ['label' => 'subtitle'],
   *     'field_link' => ['label' => 'url', 'type' => 'url'],
   *     'field_promo_block_button' => ['label' => 'buttons', 'callback' => 'paragraph'],
   *   ];
   * @endcode
   */
  public static function getEntityBundleInfo(string $entity_type, string $bundle): array {
    $items = [];
    if ($entity_type == 'node') {
      switch ($bundle) {
        case 'advanced_page':
          $items = [
            'fields' => [
              'field_banner' => ['type' => 'banner', 'callback' => 'getStraightParagraph'],
              'field_slider' => ['type' => 'slider', 'callback' => 'getStraightParagraph'],
              'body' => ['type' => 'body'],
              'field_delivery_banner' => ['type' => 'delivery_banner', 'callback' => 'getStraightParagraph'],
              'field_promo_blocks' => ['callback' => 'getRecursiveParagraphDataFromItems'],
            ],
          ];
          break;
      }
    }
    elseif ($entity_type == 'paragraph') {
      switch ($bundle) {
        case '1_row_3_col_delivery_banner':
          $items = [
            'fields' => [
              'field_title' => ['label' => 'title'],
              'field_sub_title' => ['label' => 'subtitle'],
              'field_link' => ['label' => 'url', 'callback' => 'getFieldLink'],
            ],
          ];
          break;

        case 'banner':
          $items = [
            'fields' => [
              'field_mobile_banner_image' => ['label' => 'image', 'callback' => 'getImages'],
              'field_link' => ['label' => 'url', 'callback' => 'getFieldLink'],
              'field_promo_block_button' => ['label' => 'buttons', 'callback' => 'getRecursiveParagraphDataFromItems'],
              'field_video' => ['label' => 'video'],
            ],
          ];
          break;

        case 'banner_full_width':
          $items = [
            'fields' => [
              'field_banner' => ['label' => 'image', 'callback' => 'getImages'],
            ],
          ];
          break;

        case 'promo_block':
          $items = [
            'fields' => [
              'field_promotion_image_mobile' => ['label' => 'image', 'callback' => 'getImages'],
              'field_link' => ['label' => 'url', 'callback' => 'getFieldLink'],
              'field_promo_block_button' => ['label' => 'buttons', 'callback' => 'getRecursiveParagraphDataFromItems'],
              'field_margin_mobile' => ['label' => 'margin'],
            ],
          ];
          break;

        case 'promo_block_button':
          $items = [
            'fields' => [
              'field_button_position' => ['label' => 'position'],
              'field_button_link' => ['label' => 'url', 'callback' => 'getFieldLink'],
              'field_promo_text_1' => ['label' => 'text_1'],
              'field_promo_text_2' => ['label' => 'text_2'],
              'field_promo_theme' => ['label' => 'theme'],
            ],
          ];
          break;

        case 'product_carousel_category':
          $items = [
            'callback' => 'getProductCarouselCategory',
            'fields' => [
              'field_category_carousel_title' => ['label' => 'title'],
              'field_category_carousel_limit' => ['label' => 'limit'],
              'field_use_as_accordion' => ['label' => 'accordion', 'type' => 'boolean'],
              'field_view_all_text' => ['label' => 'view_all'],
              'field_category_carousel' => [
                'label' => '',
              ],
            ],
          ];
          break;
      }
    }
    return $items;
  }

  /**
   * Get processed data for given entity.
   *
   * @param object $entity
   *   The entity object to be processed.
   *
   * @return array|bool
   *   Return array of result or false if no callback found.
   */
  public function getEntityBundleProcessedData($entity) {
    $data = FALSE;
    if (($bundle_info = $this->getEntityBundleInfo($entity->getEntityTypeId(), $entity->bundle())) && !empty($bundle_info)) {
      $data = call_user_func_array(
        [$this, !empty($bundle_info['callback']) ? $bundle_info['callback'] : 'prepareParagraphData'],
        [$entity, $bundle_info['fields']]
      );
    }
    return $data;
  }

  /**
   * Normalized data for the given entity for given field.
   *
   * @param object $entity
   *   The entity which needs to be normalized.
   * @param string $field
   *   (optional) The field of entity which requires to be normalized.
   *
   * @return array
   *   Return the array of containing normalized data.
   */
  private function getNormalizedData($entity, $field = "") {
    $context = ['langcode' => $this->languageManager->getDefaultLanguage()->getId()];
    // While using normalize, we can't able to catch bubbleable_metadata for
    // entity's canonical link and some entity don't have canonical link
    // ie. paragraph. In render method of renderer any early render that
    // happens throws fatal error. To avoid this fatal error thrown by
    // \Drupal\Core\Render\Renderer::render(), we have to handle it manually.
    // that's why Manual caching of Paragraph entity requires when this
    // method used.
    // @see Drupal\Core\Render\RendererInterface::executeInRenderContext
    // @see Drupal\serialization\Normalizer\EntityReferenceFieldItemNormalizer::normalize
    return $this->renderer->executeInRenderContext(new RenderContext(), function () use ($entity, $context, $field) {
      return $this->serializer->normalize(!empty($field) ? $entity->get($field) : $entity, 'json', $context);
    });
  }

  /**
   * Get field data of given field.
   *
   * @param object $entity
   *   The entity object.
   * @param string $field
   *   The field name.
   * @param string $callback
   *   (optional) Callback function, which processes the field data.
   * @param string $label
   *   (optional) The label of the field requires in response.
   * @param string $type
   *   (optional) The type of the field to return with response and optionally
   *   process data according to given type.
   *
   * @return array
   *   Return array with processed field data.
   */
  public function getFieldData($entity, string $field, $callback = NULL, $label = NULL, $type = NULL): array {
    if (empty($callback)) {
      unset($callback);
      $data = array_merge(['type' => $type], ['item' => $entity->get($field)->getString()]);
    }
    else {
      $data = call_user_func_array(
        [$this, $callback],
        [$entity, $field, $label, $type]
      );
    }
    return $data;
  }

  /**
   * Function to get paragraph items.
   *
   * @param object $entity
   *   The entity object.
   * @param string $field
   *   The field name.
   * @param string $label
   *   (optional) The label.
   * @param string $type
   *   (optional) The type of the field.
   *
   * @return array
   *   Return array of data.
   */
  protected function getStraightParagraph($entity, string $field, $label = NULL, $type = NULL): array {
    // Get normalized Paragraph entity of given field.
    $items = $this->getNormalizedData($entity, $field);
    $field_output = ['type' => $type, 'items' => []];
    foreach ($items as $item) {
      $entity = $this->entityTypeManager->getStorage($item['target_type'])->load($item['target_id']);
      $this->cachedEntities[] = $entity;
      // Call a callback function to prepare data if paragraph type is one of
      // the paragraph types listed in getEntityBundleInfo().
      if ($result = $this->getEntityBundleProcessedData($entity)) {
        $field_output['items'][] = $result;
      }
    }
    return empty($field_output['items']) ? [] : $field_output;
  }

  /**
   * Function to get inside paragraphs and avoid layout paragraph items.
   *
   * @param object $entity
   *   The entity object.
   * @param string $field
   *   The field name.
   * @param string $label
   *   (optional) The label.
   * @param string $type
   *   (optional) The type of the field.
   *
   * @return array
   *   Return array of data.
   */
  protected function getRecursiveParagraphDataFromItems($entity, string $field, $label = NULL, $type = NULL) {
    // Get normalized Paragraph entity of given field.
    $items = $this->getNormalizedData($entity, $field);
    $field_output = [];
    foreach ($items as $item) {
      $entity = $this->entityTypeManager->getStorage($item['target_type'])->load($item['target_id']);
      $this->cachedEntities[] = $entity;
      // Call a callback function to prepare data if paragraph type is one of
      // the paragraph types listed in getEntityBundleInfo().
      if (!$data = $this->getEntityBundleProcessedData($entity)) {
        // Get normalized Paragraph entity, as we don't need layout paragraph
        // item. we are interested in paragraph types that are stored inside
        // layout paragraph items.
        $entity_normalized = $this->getNormalizedData($entity);

        $data = [];
        foreach ($entity_normalized as $field_name => $field_values) {
          if (strpos($field_name, 'field_') !== FALSE && strpos($field_name, 'parent_field_') === FALSE) {
            foreach ($field_values as $field_value) {
              $data[] = $this->getRecursiveParagraphData($field_value);
            }
          }
        }
      }
      $field_output = !isset($field_output) ? $data : array_merge($field_output, $data);
    }
    return $field_output;
  }

  /**
   * The function to process normalized entity reference revision field data.
   *
   * @param array $item
   *   Normalize array containing target_id and target_type.
   *
   * @return array
   *   Return data array.
   */
  protected function getRecursiveParagraphData(array $item): array {
    // If current item is not paragraph type return value as a block.
    if (empty($item['target_type'])) {
      return array_merge(['type' => 'block'], $item);
    }

    // Load the paragraph entity and process it through paragraph callbacks
    // if exists.
    $entity = $this->entityTypeManager->getStorage($item['target_type'])->load($item['target_id']);
    $this->cachedEntities[] = $entity;

    // Process data for given entity if callback exists.
    if ($result = $this->getEntityBundleProcessedData($entity)) {
      return array_merge(['type' => $entity->bundle()], $result);
    }

    // Collect each field's value, load paragraph content if it contains
    // another paragraph reference otherwise get the field's value as is.
    $data = ['type' => ($entity->getEntityTypeId() == 'paragraph') ? $entity->bundle() : $entity->getEntityTypeId()];
    // Get normalized Paragraph entity.
    $entity_normalized = $this->getNormalizedData($entity);
    foreach ($entity_normalized as $field_name => $field_values) {
      if (strpos($field_name, 'field_') !== FALSE && strpos($field_name, 'parent_field_') === FALSE) {
        $row = [];
        foreach ($field_values as $field_value) {
          $row[] = empty($field_value['target_type'])
            ? $field_value
            : $this->getRecursiveParagraphData($field_value);
        }
        $data[$field_name] = $row;
      }
    }
    return $data;
  }

  /**
   * Prepare paragraph data based on given fields for given entity.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $entity
   *   The paragraph entity object.
   * @param array $fields
   *   The array of fields to return for given entity.
   *
   * @return array
   *   The converted array with necessary fields.
   */
  protected function prepareParagraphData(ParagraphInterface $entity, array $fields) {
    $data = [];
    foreach ($fields as $field => $field_info) {
      if (!empty($field_info['callback'])) {
        $result = call_user_func_array(
          [$this, $field_info['callback']],
          [
            $entity,
            $field,
            !empty($field_info['label']) ? $field_info['label'] : NULL,
            !empty($field_info['type']) ? $field_info['type'] : NULL,
          ]
        );

        if ($field_info['callback'] == 'getFieldLink') {
          $data = array_merge($data, $result);
        }
        else {
          $data[$field_info['label']] = $result;
        }
      }
      elseif ($field_info['type'] == 'boolean') {
        $data[$field_info['label']] = (bool) $entity->get($field)->first()->getValue()['value'];
      }
      else {
        $data[$field_info['label']] = $entity->get($field)->getString();
      }
    }
    return $data;
  }

  /**
   * Prepare paragraph data based on given fields for given entity.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $entity
   *   The paragraph entity object.
   * @param array $fields
   *   The array of fields to return for given entity.
   *
   * @return array
   *   The converted array with necessary fields.
   */
  protected function getProductCarouselCategory(ParagraphInterface $entity, array $fields) {
    unset($fields['field_category_carousel']);
    $data = call_user_func_array([$this, 'prepareParagraphData'], [$entity, $fields]);
    // Fetch values from the paragraph.
    $category_id = $entity->get('field_category_carousel')->getValue()[0]['target_id'] ?? NULL;

    // Generate view all link with text.
    $url = Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $category_id]);
    $url_string = $url->toString(TRUE);

    $data['view_all'] = [
      'text' => $data['view_all'],
      'url' => $url_string->getGeneratedUrl(),
      'deeplink' => $this->getDeepLinkFromUrl($url),
    ];

    // Get list of categories when category set to display as accordion else
    // Get list of products of configured category.
    if ($data['accordion']) {
      if (empty($data['title'])) {
        $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($category_id);
        $data['title'] = $term->label();
      }
      $data['items'] = $this->categoryChildTerms($category_id);
    }
    else {
      // Prepare argument for the views.
      $terms = _alshaya_master_get_recursive_child_terms($category_id);
      $arguments = ['tid' => implode('+', $terms)];

      // Get views result.
      $results = $this->renderer->executeInRenderContext(new RenderContext(), function () use ($arguments) {
        return _alshaya_master_get_views_result('alshaya_product_list', 'block_1', $arguments);
      });

      // Prepare argument for the views.
      $product_in_carousel = $entity->get('field_category_carousel_limit')->getString();
      $nodes = alshaya_acm_product_filter_out_of_stock_products($results, $product_in_carousel);
      // If there are results.
      if (!empty($nodes)) {
        $langcode = $this->languageManager->getCurrentLanguage()->getId();
        $data['items'] = array_map(function ($node) use ($langcode) {
          return $this->getLightProductFromNid($node->id(), $langcode);
        }, $nodes);
      }
    }
    return $data;
  }

  /**
   * Function to get all the child terms of given term.
   *
   * Created duplicate method of existing function to avoid fatal
   * error of early rendering and create deeplink for each term.
   *
   * @param int $tid
   *   The term id.
   *
   * @return array
   *   Return array with term data.
   *
   * @see alshaya_acm_product_category_child_terms()
   */
  private function categoryChildTerms($tid) {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $terms = $this->productCategoryTree->allChildTerms($langcode, $tid, FALSE, TRUE);

    $data = [];
    foreach ($terms as $term) {
      $url = Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->tid])->toString(TRUE);
      $data[] = [
        'label' => $term->name,
        'description'  => [
          '#markup' => $term->description__value,
        ],
        'id' => $term->tid,
        'url' => $url->getGeneratedUrl(),
        'deeplink' => $this->getDeeplink($term),
        'active_class' => '',
      ];
    }
    return $data;
  }

  /**
   * Get the link parameters for link field type.
   *
   * @param object $entity
   *   The entity object.
   * @param string $field
   *   The link field name.
   * @param string $label
   *   (optional) The label.
   * @param string $type
   *   (optional) The type of the field.
   *
   * @return array
   *   Return the associative array with url and deeplink.
   */
  protected function getFieldLink($entity, string $field, $label = 'url', $type = NULL) {
    // Convert field link value.
    $url = $entity->get($field)->first()->getUrl();
    $url_string = $url->toString(TRUE);

    return [
      $label => $url_string->getGeneratedUrl(),
      'deeplink' => $this->getDeepLinkFromUrl($url),
    ];
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

  /**
   * Get light product data using give nid.
   *
   * @param int $nid
   *   Node id.
   * @param string $langcode
   *   Language of node.
   *
   * @return array
   *   Product data.
   */
  public function getLightProductFromNid(int $nid, string $langcode = 'en') {
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    // If node exists in system.
    if ($node instanceof Node) {
      // Get translated node.
      $node = $this->entityRepository->getTranslationFromContext($node, $langcode);
      // Get SKU attached with node.
      $sku = $node->get('field_skus')->getString();
      $sku_entity = SKU::loadFromSku($sku);
      // If SKU exists in system.
      if ($sku_entity instanceof SKU) {
        return $this->mobileAppUtility->getLightProduct($sku_entity);
      }
    }

    return [];
  }

  /**
   * Get Light Product.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Light Product.
   */
  public function getLightProduct(SKUInterface $sku): array {
    // Get the prices.
    $prices = $this->skuManager->getMinPrices($sku);

    // Get the promotion data.
    $promotions = $this->getPromotions($sku);

    // Get promo labels.
    $promo_label = $this->skuManager->getDiscountedPriceMarkup($prices['price'], $prices['final_price']);
    if ($promo_label) {
      $promotions[] = [
        'text' => $promo_label,
      ];
    }

    // Get label for the SKU.
    $labels = $this->getLabels($sku, 'plp');

    // Get media (images/video) for the SKU.
    $images = $this->getMedia($sku, 'search');

    $data = [
      'id' => (int) $sku->id(),
      'title' => $sku->label(),
      'sku' => $sku->getSku(),
      'deeplink' => $this->getDeepLink($sku),
      'original_price' => $prices['price'],
      'final_price' => $prices['final_price'],
      'in_stock' => (bool) alshaya_acm_get_stock_from_sku($sku),
      'promo' => $promotions,
      'medias' => $images,
      'labels' => $labels,
    ];

    // Allow other modules to alter light product data.
    $this->moduleHandler->alter('alshaya_mobile_app_light_product_data', $sku, $data);

    return $data;
  }

  /**
   * Wrapper function get fully loaded linked skus.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $linked_type
   *   Linked type.
   *
   * @return array
   *   Linked SKUs.
   */
  public function getLinkedSkus(SKUInterface $sku, string $linked_type) {
    $return = [];
    $linkedSkus = $this->skuManager->getLinkedSkus($sku, $linked_type);

    foreach ($linkedSkus as $linkedSku) {
      $linkedSkuEntity = SKU::loadFromSku($linkedSku);

      if ($linkedSkuEntity instanceof SKUInterface) {
        $return[] = $this->getLightProduct($linkedSkuEntity);
      }
    }

    return $return;
  }

}
