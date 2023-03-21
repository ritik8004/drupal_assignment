<?php

namespace Drupal\alshaya_mobile_app\Service;

use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\alshaya_acm_product_category\Service\ProductCategoryPage;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\redirect\RedirectRepository;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Database\Connection;
use Drupal\alshaya_super_category\AlshayaSuperCategoryManager;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Routing\RequestContext;

/**
 * MobileAppUtilityParagraphs service decorators for MobileAppUtility .
 */
class MobileAppUtilityParagraphs extends MobileAppUtility {

  /**
   * The array of objects to cache.
   *
   * @var array
   */
  protected $cacheableEntities = [];

  /**
   * The cache tags.
   *
   * @var array
   */
  protected $cacheTags = [];

  /**
   * The paragraph Base Fields.
   *
   * @var string
   */
  protected $paragraphBaseFields = [];

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * The serializer.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Advanced page node object.
   *
   * @var \Drupal\node\NodeInterface|null
   */
  protected $advancedPageNode = NULL;

  /**
   * Block plugin manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockPluginManager;

  /**
   * Utility constructor.
   *
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   The serializer.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repository.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU images manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface $product_category_tree
   *   Product category tree.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   The renderer.
   * @param \Drupal\redirect\RedirectRepository $redirect_repository
   *   Redirect repository.
   * @param \Drupal\alshaya_acm_product\Service\SkuInfoHelper $sku_info_helper
   *   Sku info helper object.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_plugin_manager
   *   Block plugin manager.
   * @param \Drupal\Core\Database\Connection $database
   *   Database service.
   * @param Drupal\alshaya_super_category\AlshayaSuperCategoryManager $super_category_manager
   *   Super Category Manager.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   Path Validator service object.
   * @param \Drupal\alshaya_acm_product_category\Service\ProductCategoryPage $product_category_page
   *   Product Category Page service.
   * @param \Drupal\Core\Routing\RequestContext $request_context
   *   The request context.
   */
  public function __construct(
    MobileAppUtility $mobile_app_utility,
    SerializerInterface $serializer,
    RendererInterface $renderer,
    EntityFieldManagerInterface $entity_field_manager,
    CacheBackendInterface $cache,
    LanguageManagerInterface $language_manager,
    RequestStack $request_stack,
    AliasManagerInterface $alias_manager,
    EntityTypeManagerInterface $entity_type_manager,
    EntityRepositoryInterface $entity_repository,
    SkuManager $sku_manager,
    SkuImagesManager $sku_images_manager,
    ModuleHandlerInterface $module_handler,
    ProductCategoryTreeInterface $product_category_tree,
    ConfigFactoryInterface $config_factory,
    AlshayaApiWrapper $api_wrapper,
    RedirectRepository $redirect_repository,
    SkuInfoHelper $sku_info_helper,
    BlockManagerInterface $block_plugin_manager,
    Connection $database,
    AlshayaSuperCategoryManager $super_category_manager,
    PathValidatorInterface $path_validator,
    ProductCategoryPage $product_category_page,
    RequestContext $request_context
  ) {
    parent::__construct($cache, $language_manager, $request_stack, $alias_manager, $entity_type_manager, $entity_repository, $sku_manager, $sku_images_manager, $module_handler, $product_category_tree, $config_factory, $api_wrapper, $renderer, $redirect_repository, $sku_info_helper, $database, $super_category_manager, $path_validator, $product_category_page, $request_context);
    $this->entityFieldManager = $entity_field_manager;
    $this->mobileAppUtility = $mobile_app_utility;
    $this->serializer = $serializer;
    $this->paragraphBaseFields = $this->entityFieldManager->getBaseFieldDefinitions('paragraph');
    $this->blockPluginManager = $block_plugin_manager;
    $this->database = $database;
  }

  /**
   * Return array of cacheable entities.
   *
   * @return array
   *   Return array of cacheable entities.
   */
  public function getCacheableEntities() {
    return $this->cacheableEntities;
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
   *   Return array of just fields and callback, contains associative array
   *   of fields with (label, type, callback).
   *   - label: Field label to return output with.
   *   - type: Return output of a field with type and items.
   *      i.e. {"type": "banner", "items": []}
   *   - callback: Callback method to process field to get desired output.
   */
  public static function getEntityBundleInfo(string $entity_type, string $bundle): array {
    $items = &drupal_static(__METHOD__, []);
    if (empty($items)) {
      $default_values = [
        'callback' => NULL,
        'label' => NULL,
        'type' => NULL,
      ];

      $items = [
        'node' => [
          'advanced_page' => [
            'fields' => [
              'field_banner' => [
                'callback' => 'getFieldParagraphItems',
                'type' => 'banner',
              ] + $default_values,
              'field_slider' => [
                'callback' => 'getFieldMultipleParagraphItems',
                'type' => 'slider',
              ] + $default_values,
              'field_delivery_banner' => [
                'callback' => 'getFieldParagraphItems',
                'label' => 'items',
                'type' => 'delivery_banner',
              ] + $default_values,
              'body' => [
                'type' => 'body',
              ] + $default_values,
              'field_promo_blocks' => [
                'callback' => 'getFieldRecursiveParagraphItems',
              ] + $default_values,
            ],
          ],
        ],
        'paragraph' => [
          '1_row_3_col_delivery_banner' => [
            'fields' => [
              'field_title' => [
                'label' => 'title',
              ] + $default_values,
              'field_sub_title' => [
                'label' => 'subtitle',
              ] + $default_values,
              'field_link' => [
                'callback' => 'getFieldLink',
                'label' => 'url',
              ] + $default_values,
            ],
          ],
          'banner' => [
            'fields' => [
              'field_mobile_banner_image' => [
                'callback' => 'getImages',
                'label' => 'image',
              ] + $default_values,
              'field_link' => [
                'callback' => 'getFieldLink',
                'label' => 'url',
              ] + $default_values,
              'field_promo_block_button' => [
                'callback' => 'getFieldRecursiveParagraphItems',
                'label' => 'buttons',
              ] + $default_values,
              'field_video' => [
                'label' => 'video',
              ] + $default_values,
            ],
          ],
          'banner_full_width' => [
            'fields' => [
              'field_banner' => [
                'callback' => 'getImages',
                'label' => 'image',
              ] + $default_values,
            ],
          ],
          'block_reference' => [
            'callback' => 'paragraphBlockReference',
            'fields' => [
              'field_block_reference' => [
                'callback' => 'getFieldBlockReference',
                'label' => 'block',
              ] + $default_values,
            ],
          ],
          'delivery_usp_block' => [
            'callback' => 'paragraphDeliveryUspBlock',
            'fields' => [
              'field_arrow_color' => [
                'label' => 'arrow_color',
              ] + $default_values,
              'field_usp_text' => [
                'label' => 'text',
              ] + $default_values,
              'field_usp_text_background' => [
                'label' => 'text_background',
              ] + $default_values,
              'field_usp_text_font_color' => [
                'label' => 'text_font_color',
              ] + $default_values,
              'field_usp_timer' => [
                'label' => 'timer',
              ] + $default_values,
            ],
          ],
          'product_carousel_category' => [
            'callback' => 'paragraphProductCarouselCategory',
            'fields' => [
              'field_category_carousel_title' => [
                'label' => 'title',
              ] + $default_values,
              'field_category_carousel_limit' => [
                'label' => 'limit',
              ] + $default_values,
              'field_use_as_accordion' => [
                'callback' => 'getFieldBoolean',
                'label' => 'accordion',
              ],
              'field_view_all_text' => [
                'label' => 'view_all',
              ] + $default_values,
              'field_category_carousel' => $default_values,
            ],
          ],
          'promo_block' => [
            'fields' => [
              'field_banner' => [
                'callback' => 'getImages',
                'label' => 'field_banner',
              ] + $default_values,
              'field_promotion_image_mobile' => [
                'callback' => 'getImages',
                'label' => 'image',
              ] + $default_values,
              'field_link' => [
                'callback' => 'getFieldLink',
                'label' => 'url',
              ] + $default_values,
              'field_promo_block_button' => [
                'callback' => 'getFieldRecursiveParagraphItems',
                'label' => 'buttons',
              ] + $default_values,
              'field_margin_mobile' => [
                'label' => 'margin',
              ] + $default_values,
            ],
          ],
          'promo_block_button' => [
            'fields' => [
              'field_button_position' => [
                'label' => 'position',
              ] + $default_values,
              'field_button_link' => [
                'callback' => 'getFieldLink',
                'label' => 'url',
              ] + $default_values,
              'field_promo_text_1' => [
                'label' => 'text_1',
              ] + $default_values,
              'field_promo_text_2' => [
                'label' => 'text_2',
              ] + $default_values,
              'field_promo_theme' => [
                'label' => 'theme',
              ] + $default_values,
              'field_description' => [
                'label' => 'description',
              ] + $default_values,
            ],
          ],
          'image_title_subtitle_link' => [
            'callback' => 'prepareParagraphImageTitleSubtitleLink',
            'fields' => [
              'field_title' => [
                'label' => 'title',
              ] + $default_values,
              'field_sub_title' => [
                'label' => 'subtitle',
              ] + $default_values,
              'field_banner' => [
                'callback' => 'getImages',
                'label' => 'field_banner',
              ] + $default_values,
              'field_links' => [
                'callback' => 'getFieldLink',
                'label' => 'url',
              ] + $default_values,
            ],
          ],
        ],
      ];
    }
    return $items[$entity_type][$bundle] ?? [];
  }

  /**
   * Get additional fields.
   *
   * @param \Drupal\entity\EntityInterface $entity
   *   The paragraph entity object.
   *
   * @return array
   *   Array of all created fields.
   */
  protected function getConfiguredFields(EntityInterface $entity):array {
    $config_fields = [];
    // Adding a check for entity types throwing exceptions.
    if ($entity->getEntityTypeId() != 'user_role' && $entity->getEntityTypeId() != 'media_type') {
      $all_fields = $this->entityFieldManager->getFieldDefinitions($entity->getEntityTypeId(), $entity->bundle());
      $config_fields = array_diff(array_keys($all_fields), array_keys($this->paragraphBaseFields));
    }
    return $config_fields;
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
  public function processEntityBundleData($entity) {
    $data = FALSE;
    $entity = $this->skuInfoHelper->getEntityTranslation($entity, $this->currentLanguage);
    $this->cacheableEntities[] = $entity;
    if (!empty($bundle_info = static::getEntityBundleInfo($entity->getEntityTypeId(), $entity->bundle()))) {
      $data = call_user_func_array(
        [
          $this,
          !empty($bundle_info['callback'])
          ? $bundle_info['callback']
          : 'paragraphPrepareData',
        ],
        [$entity, $bundle_info['fields']]
      );
    }
    return $data;
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
   *   Return array with processed field data and string when callback is empty.
   */
  public function getFieldData($entity, string $field, $callback = NULL, $label = NULL, $type = NULL) {
    $data = [];
    if (empty($callback)) {
      if (!empty($entity->get($field)->first())) {
        $data = array_merge(
          ['type' => $type],
          ['item' => $this->convertRelativeUrlsToAbsolute($entity->get($field)->first()->getValue()['value'])]
        );
      }
    }
    else {
      $data = call_user_func_array(
        [$this, $callback],
        [$entity, $field, $label, $type]
      );
    }
    // Return empty array, if $data contains only 'type' key.
    return !empty($data['type'])
    ? (is_countable($data) ? count($data) : 0) > 1 ? $data : []
    : $data;
  }

  /**
   * Function to get paragraph items.
   *
   * @param object $entity
   *   The paragraph entity or node entity object.
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
  protected function getFieldParagraphItems($entity, string $field, $label = NULL, $type = NULL): array {
    if (!$entity->hasField($field)) {
      return [];
    }
    // Load entities associated with entity reference revision field.
    $entities = $entity->get($field)->referencedEntities();
    $field_output = ['type' => $type];
    foreach ($entities as $entity) {
      // Call a callback function to prepare data if paragraph type is one of
      // the paragraph types listed in getEntityBundleInfo().
      if ($result = $this->processEntityBundleData($entity)) {
        if ($label) {
          $field_output[$label][] = $result;
        }
        else {
          $field_output = array_merge($field_output, $result);
        }
      }
    }
    return !empty($field_output) ? $field_output : [];
  }

  /**
   * Function to get multiple paragraph items associated with the field.
   *
   * This kind of fields does not have layout paragraph types in-between.
   *
   * @param object $entity
   *   The paragraph entity or node entity object.
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
  protected function getFieldMultipleParagraphItems($entity, string $field, $label = NULL, $type = NULL): array {
    if (!$entity->hasField($field)) {
      return [];
    }
    // Load entities associated with entity reference revision field.
    $entities = $entity->get($field)->referencedEntities();
    $field_output = ['type' => $type, 'items' => []];
    foreach ($entities as $entity) {
      // Call a callback function to prepare data if paragraph type is one of
      // the paragraph types listed in getEntityBundleInfo().
      if ($result = $this->processEntityBundleData($entity)) {
        $field_output['items'][] = $entity->bundle() == 'delivery_usp_block'
        ? $result[0]
        : array_merge(['type' => $entity->bundle()], $result);
      }
    }
    return !empty($field_output['items']) ? $field_output : [];
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
  protected function paragraphDeliveryUspBlock(ParagraphInterface $entity, array $fields) {
    $data = call_user_func_array($this->paragraphPrepareData(...), [
      $entity,
      $fields,
    ]);
    return [array_merge(['type' => $entity->bundle()], $data)];
  }

  /**
   * Function to get inside paragraphs and avoid layout paragraph items.
   *
   * @param object $entity
   *   The paragraph entity or node entity object.
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
  protected function getFieldRecursiveParagraphItems($entity, string $field, $label = NULL, $type = NULL) {
    if (!$entity->hasField($field)) {
      return [];
    }
    // Load entities associated with entity reference revision field.
    $entities = $entity->get($field)->referencedEntities();
    $field_output = [];
    foreach ($entities as $entity) {
      // Call a callback function to prepare data if paragraph type is one of
      // the paragraph types listed in getEntityBundleInfo().
      if (!$data = $this->processEntityBundleData($entity)) {
        $data = [];
        // Get configured fields of entity, we don't require base fields.
        $entity = $this->skuInfoHelper->getEntityTranslation($entity, $this->currentLanguage);
        $paragraph_fields = $this->getConfiguredFields($entity);
        foreach ($paragraph_fields as $field_name) {
          // We are interested in paragraph types that are stored inside
          // layout paragraph items.
          if (!empty($paragraph_data = $this->processParagraphReferenceField($entity, $field_name))) {
            $data = array_merge($data, $paragraph_data);
            continue;
          }
          $field_values = $entity->get($field_name)->getValue();
          foreach ($field_values as $field_value) {
            $data[] = array_merge(['type' => 'block'], $field_value);
          }
        }
      }
      $field_output = array_merge($field_output, $data);
    }
    return $field_output;
  }

  /**
   * The function to process paragraph entity fields data.
   *
   * @param object $entity
   *   The entity object.
   *
   * @return array
   *   Return data array.
   */
  protected function getRecursiveParagraphData($entity): array {
    $entity = $this->skuInfoHelper->getEntityTranslation($entity, $this->currentLanguage);
    $this->cacheableEntities[] = $entity;
    // Process data for given entity if callback exists.
    if ($result = $this->processEntityBundleData($entity)) {
      return array_merge(['type' => $entity->bundle()], $result);
    }

    // Collect each field's value, load paragraph content if it contains
    // another paragraph reference otherwise get the field's value as is.
    $data = [
      'type' => ($entity->getEntityTypeId() == 'paragraph')
      ? $entity->bundle()
      : $entity->getEntityTypeId(),
    ];

    // Get parent entity types for paragraph entities.
    if ($entity->getEntityTypeId() == 'paragraph') {
      $data['parent_type'] = $entity->getParentEntity()->bundle();
    }

    // Get configured fields of entity, we don't require base fields.
    $paragraph_fields = $this->getConfiguredFields($entity);
    foreach ($paragraph_fields as $field_name) {
      if (empty($row = $this->processParagraphReferenceField($entity, $field_name))) {
        $row = $entity->get($field_name)->getValue();
        if ($field_name == 'field_banner' || $field_name == 'thumbnail') {
          if (!empty($row)) {
            $image_file = $this->fileStorage->load($row[0]['target_id']);
            if ($image_file instanceof File) {
              $row[0]['url'] = file_create_url($image_file->getFileUri());
            }
          }
        }
        if ($field_name == 'field_link' || $field_name == 'field_button_link') {
          if (!empty($row) && UrlHelper::isValid($row[0]['uri'])) {
            $url_object = Url::fromUri($row[0]['uri']);
            if (isset($url_object)) {
              $row[0]['deeplink'] = $this->getDeepLinkFromUrl($url_object);
            }
          }
        }
      }
      $data[$field_name] = $row;
    }
    return $data;
  }

  /**
   * Process paragraph entity reference revision field.
   *
   * @param \Drupal\entity\EntityInterface $entity
   *   The paragraph entity object.
   * @param string $field_name
   *   The entity reference revision field name.
   *
   * @return array
   *   Return array of processed paragraph data.
   */
  protected function processParagraphReferenceField(EntityInterface $entity, string $field_name): array {
    if (!$entity->hasField($field_name)) {
      return [];
    }

    $data = [];
    $field_type = $entity->get($field_name)->getFieldDefinition()->getType();
    if ($field_type == "entity_reference_revisions" || $field_type == "entity_reference") {
      $children = $entity->get($field_name)->referencedEntities();
      foreach ($children as $child) {
        $data[] = $this->getRecursiveParagraphData($child);
      }
    }
    return $data;
  }

  /**
   * Prepare paragraph data based on given fields for given entity.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $entity
   *   Paragrpah entity.
   * @param array $fields
   *   Paragraph fields.
   *
   * @return array
   *   Data array.
   */
  public function prepareParagraphImageTitleSubtitleLink(ParagraphInterface $entity, array $fields) {
    $data = [];
    foreach ($fields as $field_name => $field_array) {
      if (empty($row = $this->processParagraphReferenceField($entity, $field_name))) {
        $row = $entity->get($field_name)->getValue();
        if ($field_name == 'field_banner' || $field_name == 'thumbnail') {
          if (!empty($row)) {
            $image_file = $this->fileStorage->load($row[0]['target_id']);
            if ($image_file instanceof File) {
              $row[0]['url'] = file_create_url($image_file->getFileUri());
            }
          }
        }
        if ($field_name == 'field_link' || $field_name == 'field_button_link') {
          if (!empty($row) && UrlHelper::isValid($row[0]['uri'])) {
            $url_object = Url::fromUri($row[0]['uri']);
            if (isset($url_object)) {
              $row[0]['deeplink'] = $this->getDeepLinkFromUrl($url_object);
            }
          }
        }
      }
      $data[$field_name] = $row;
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
  protected function paragraphPrepareData(ParagraphInterface $entity, array $fields) {
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

        if (empty($result)) {
          continue;
        }
        // When result comes with label, merge it with the array,
        // as we don't have to create element.
        if ($field_info['callback'] == 'getFieldLink' || isset($result[$field_info['label']])) {
          $data = array_merge($data, $result);
        }
        else {
          $data[$field_info['label']] = $result;
        }
      }
      elseif ($entity->hasField($field) && !empty($entity->get($field)->getString())) {
        // Check cardinality of given field.
        $data[$field_info['label']] = $entity->get($field)->getFieldDefinition()->getFieldStorageDefinition()->isMultiple()
        ? array_map(fn($value) => $value['value'], $entity->get($field)->getValue())
        : $entity->get($field)->getString();
      }
    }
    if (!empty($data)
      && $entity->getEntityTypeId() == 'paragraph'
      && $parent = $entity->getParentEntity()->bundle()) {
      $data['parent_type'] = $parent;
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
  protected function paragraphBlockReference(ParagraphInterface $entity, array $fields) {
    $data = call_user_func_array($this->paragraphPrepareData(...), [
      $entity,
      $fields,
    ]);
    return [array_merge(['type' => 'block'], $data['block'] ?? [])];
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
  protected function paragraphProductCarouselCategory(ParagraphInterface $entity, array $fields) {
    unset($fields['field_category_carousel']);
    $this->cacheableEntities[] = $entity;

    // Fetch values from the paragraph.
    $category_id = $entity->get('field_category_carousel')->getValue()[0]['target_id'] ?? NULL;
    if ($category_id === NULL) {
      return FALSE;
    }
    $data = call_user_func_array($this->paragraphPrepareData(...), [
      $entity,
      $fields,
    ]);

    // Generate view all link when text is not empty.
    if (!empty($data['view_all'])) {
      $url = Url::fromRoute('entity.taxonomy_term.canonical', [
        'taxonomy_term' => $category_id,
      ]);
      $url_string = $url->toString(TRUE);

      $data['view_all'] = [
        'text' => $data['view_all'],
        'url' => $url_string->getGeneratedUrl(),
        'deeplink' => $this->getDeepLinkFromUrl($url),
      ];
    }

    if (isset($data['limit'])) {
      $data['limit'] = (int) $data['limit'];
    }

    // Get list of categories when category set to display as accordion else
    // Get list of products of configured category.
    if ($data['accordion']) {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($category_id);
      $term = $this->skuInfoHelper->getEntityTranslation($term, $this->currentLanguage);
      if (empty($data['title'])) {
        $data['title'] = $term->label();
      }
      $items = $this->getAllCategories($this->currentLanguage, $category_id, FALSE, TRUE);
      // Adding deeplink if there is no items.
      if (empty($items)) {
        $data['deeplink'] = $this->getDeepLink($term);
      }
      $data['items'] = $items;
    }
    else {
      // Invoke views display in executeInRenderContext to avoid cached
      // metadata leak issue.
      // @See https://www.drupal.org/project/drupal/issues/2450993
      // Execute this only if the config is set to get data from Algolia.
      $category_carousel_return_products = $this->configFactory->get('alshaya_mobile_app.settings')->get('category_carousel_return_products');
      if ($category_carousel_return_products) {
        $nodes = $this->renderer->executeInRenderContext(
          new RenderContext(),
          function () use ($entity, $category_id) {
            $carousel_product_limit = (int) $entity->get('field_category_carousel_limit')->getString();
            return _alshaya_acm_product_get_unique_in_stock_products_for_category($category_id, $carousel_product_limit);
          }
        );

        if (!empty($nodes)) {
          $data['items'] = array_map(function ($node) {
            $this->cacheableEntities[] = $node;
            return $this->skuManager->getSkuForNode($node);
          }, $nodes);
        }
      }
      // Add the required algolia data.
      $data['algolia_data'] = $this->getAlgoliaData($category_id, $this->currentLanguage);
    }

    return $data;
  }

  /**
   * Prepare block reference data based on given fields for given entity.
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
  protected function getFieldBlockReference($entity, string $field, $label = NULL, $type = NULL) {
    $items = $entity->get($field)->getValue();

    $this->cacheTags[] = 'block_view';
    $this->cacheTags[] = 'block';
    $this->cacheTags[] = 'block_content_view';
    $results = array_map(function ($item) {
      [$entity_type, $uuid] = strpos($item['plugin_id'], ':')
        ? explode(':', $item['plugin_id'])
        : [$item['plugin_id'], ''];
      if ($entity_type == 'block_content') {
        $block = $this->entityTypeManager->getStorage($entity_type)->loadByProperties(['uuid' => $uuid]);
        $block = reset($block);
        $block = $this->skuInfoHelper->getEntityTranslation($block, $this->currentLanguage);
        $this->cacheTags = array_merge($this->cacheTags, $block->getCacheTags());

        return [
          'label' => $item['settings']['label'],
          'label_display' => (bool) $item['settings']['label_display'],
          'body' => !empty($block->get('body')->first())
          ? $this->convertRelativeUrlsToAbsolute($block->get('body')->first()->getValue()['value'])
          : '',
          'image' => $this->getImages($block, 'field_image'),
        ];
      }
      elseif ($item['plugin_id'] == 'alshaya_dp_navigation_link' || $item['plugin_id'] == 'alshaya_rcs_dp_app_navigation') {
        return $this->prepareAppNavigationLinks($item);
      }
      elseif ($item['plugin_id'] == 'alshaya_check_balance') {
        // This is for a custom block created for eGift balance check.
        return $item['settings'];
      }
    }, $items);
    // Return only first result as Block reference has delta limit to 1.
    return $results[0] ?? [];
  }

  /**
   * Get the app navigation links.
   *
   * @param array $item
   *   Settings array.
   *
   * @return array
   *   Block data.
   */
  public function prepareAppNavigationLinks(array $item) {
    $data = [];

    if (($node = $this->getAdvancedPageNode()) instanceof NodeInterface) {
      $item['settings']['advanced_page_node'] = $node;

      /** @var \Drupal\Core\Block\BlockPluginInterface $block_instance */
      $block_instance = $this->blockPluginManager->createInstance($item['plugin_id'], $item['settings']);
      if (!empty($block_data = $block_instance->build())) {
        $data = [
          'id' => 'alshaya_dp_navigation_link',
          'label' => $item['settings']['label'],
          'label_display' => (bool) $item['settings']['label_display'],
          'l2' => $block_data['l2'] ?? [],
          'l3' => $block_data['l3'] ?? [],
        ];
      }
    }

    return $data;
  }

  /**
   * Return all cache tags.
   *
   * @return array
   *   Return array of all cache tags.
   */
  public function getBlockCacheTags(): array {
    return $this->cacheTags;
  }

  /**
   * Get advanced page node object.
   *
   * @return \Drupal\node\NodeInterface|null
   *   Advanced page node object.
   */
  public function getAdvancedPageNode() {
    return $this->advancedPageNode;
  }

  /**
   * Set advanced page node object.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Advanced page node object.
   *
   * @return $this
   *   Current object.
   */
  public function setAdvancedPageNode(NodeInterface $node) {
    $this->advancedPageNode = $node;
    return $this;
  }

}
