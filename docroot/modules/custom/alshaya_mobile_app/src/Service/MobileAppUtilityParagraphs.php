<?php

namespace Drupal\alshaya_mobile_app\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;

/**
 * MobileAppUtilityParagraphs service decorators for MobileAppUtility .
 */
class MobileAppUtilityParagraphs extends MobileAppUtility {

  /**
   * The array of objects to cache.
   *
   * @var array
   */
  protected $cachedEntities = [];

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
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

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
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
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
   */
  public function __construct(MobileAppUtility $mobile_app_utility,
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
                              ProductCategoryTreeInterface $product_category_tree) {
    parent::__construct($cache, $language_manager, $request_stack, $alias_manager, $entity_type_manager, $entity_repository, $sku_manager, $sku_images_manager, $module_handler, $product_category_tree);
    $this->entityFieldManager = $entity_field_manager;
    $this->mobileAppUtility = $mobile_app_utility;
    $this->serializer = $serializer;
    $this->renderer = $renderer;
    $this->paragraphBaseFields = $this->entityFieldManager->getBaseFieldDefinitions('paragraph');
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
   *   Return array of just fields and callback, contains associative array
   *   of fields with (label, type, callback).
   *   - label: Field label to return output with.
   *   - type: Return output of a field with type and items.
   *      i.e. {"type": "banner", "items": []}
   *   - callback: Callback method to process field to get desired output.
   */
  public static function getEntityBundleInfo(string $entity_type, string $bundle): array {
    $items = [
      'node' => [
        'advanced_page' => [
          'fields' => [
            'field_banner' => ['type' => 'banner', 'callback' => 'getStraightParagraph'],
            'field_slider' => ['type' => 'slider', 'callback' => 'getStraightParagraph'],
            'body' => ['type' => 'body'],
            'field_delivery_banner' => ['type' => 'delivery_banner', 'callback' => 'getStraightParagraph'],
            'field_promo_blocks' => ['callback' => 'getRecursiveParagraphDataFromItems'],
          ],
        ],
      ],
      'paragraph' => [
        '1_row_3_col_delivery_banner' => [
          'fields' => [
            'field_title' => ['label' => 'title'],
            'field_sub_title' => ['label' => 'subtitle'],
            'field_link' => ['label' => 'url', 'callback' => 'getFieldLink'],
          ],
        ],
        'banner' => [
          'fields' => [
            'field_mobile_banner_image' => ['label' => 'image', 'callback' => 'getImages'],
            'field_link' => ['label' => 'url', 'callback' => 'getFieldLink'],
            'field_promo_block_button' => ['label' => 'buttons', 'callback' => 'getRecursiveParagraphDataFromItems'],
            'field_video' => ['label' => 'video'],
          ],
        ],
        'banner_full_width' => [
          'fields' => [
            'field_banner' => ['label' => 'image', 'callback' => 'getImages'],
          ],
        ],
        'product_carousel_category' => [
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
        ],
        'promo_block' => [
          'fields' => [
            'field_promotion_image_mobile' => ['label' => 'image', 'callback' => 'getImages'],
            'field_link' => ['label' => 'url', 'callback' => 'getFieldLink'],
            'field_promo_block_button' => ['label' => 'buttons', 'callback' => 'getRecursiveParagraphDataFromItems'],
            'field_margin_mobile' => ['label' => 'margin'],
          ],
        ],
        'promo_block_button' => [
          'fields' => [
            'field_button_position' => ['label' => 'position'],
            'field_button_link' => ['label' => 'url', 'callback' => 'getFieldLink'],
            'field_promo_text_1' => ['label' => 'text_1'],
            'field_promo_text_2' => ['label' => 'text_2'],
            'field_promo_theme' => ['label' => 'theme'],
          ],
        ],
      ],
    ];
    return $items[$entity_type][$bundle] ?: [];
  }

  /**
   * Get additional fields.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $entity
   *   The paragraph entity object.
   *
   * @return array
   *   Array of all created fields.
   */
  protected function getConfiguredFields(ParagraphInterface $entity):array {
    $all_fields = $this->entityFieldManager->getFieldDefinitions($entity->getEntityTypeId(), $entity->bundle());
    $config_fields = array_diff(array_keys($all_fields), array_keys($this->paragraphBaseFields));
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
  public function getEntityBundleProcessedData($entity) {
    $data = FALSE;
    $entity = $this->entityRepository->getTranslationFromContext($entity, $this->currentLanguage);
    if (!empty($bundle_info = $this->getEntityBundleInfo($entity->getEntityTypeId(), $entity->bundle()))) {
      $data = call_user_func_array(
        [$this, !empty($bundle_info['callback']) ? $bundle_info['callback'] : 'prepareParagraphData'],
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
   *   Return array with processed field data.
   */
  public function getFieldData($entity, string $field, $callback = NULL, $label = NULL, $type = NULL): array {
    if (empty($callback)) {
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
  protected function getStraightParagraph($entity, string $field, $label = NULL, $type = NULL): array {
    // Get normalized Paragraph entity of given field.
    $entities = $entity->get($field)->referencedEntities();
    $field_output = ['type' => $type, 'items' => []];
    foreach ($entities as $entity) {
      $entity = $this->entityRepository->getTranslationFromContext($entity, $this->currentLanguage);
      $this->cachedEntities[] = $entity;
      // Call a callback function to prepare data if paragraph type is one of
      // the paragraph types listed in getEntityBundleInfo().
      if ($result = $this->getEntityBundleProcessedData($entity)) {
        $field_output['items'][] = $result;
      }
    }
    return !empty($field_output['items']) ? $field_output : [];
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
  protected function getRecursiveParagraphDataFromItems($entity, string $field, $label = NULL, $type = NULL) {
    // Get normalized Paragraph entity of given field.
    $entities = $entity->get($field)->referencedEntities();
    $field_output = [];
    foreach ($entities as $entity) {
      $entity = $this->entityRepository->getTranslationFromContext($entity, $this->currentLanguage);
      // Call a callback function to prepare data if paragraph type is one of
      // the paragraph types listed in getEntityBundleInfo().
      if (!$data = $this->getEntityBundleProcessedData($entity)) {
        $data = [];
        // Get normalized Paragraph entity, as we don't need layout paragraph
        // item. we are interested in paragraph types that are stored inside
        // layout paragraph items.
        $paragraph_fields = $this->getConfiguredFields($entity);
        foreach ($paragraph_fields as $field_name) {
          if (empty($data = $this->processParagraphReferenceField($entity, $field_name))) {
            $field_values = $entity->get($field_name)->getValue();
            foreach ($field_values as $field_value) {
              $data[] = array_merge(['type' => 'block'], $field_value);
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
   * @param object $entity
   *   Normalize array containing target_id and target_type.
   *
   * @return array
   *   Return data array.
   */
  protected function getRecursiveParagraphData($entity): array {
    $entity = $this->entityRepository->getTranslationFromContext($entity, $this->currentLanguage);
    $this->cachedEntities[] = $entity;
    // Process data for given entity if callback exists.
    if ($result = $this->getEntityBundleProcessedData($entity)) {
      return array_merge(['type' => $entity->bundle()], $result);
    }

    // Collect each field's value, load paragraph content if it contains
    // another paragraph reference otherwise get the field's value as is.
    $data = ['type' => ($entity->getEntityTypeId() == 'paragraph') ? $entity->bundle() : $entity->getEntityTypeId()];
    // Get normalized Paragraph entity.
    $paragraph_fields = $this->getConfiguredFields($entity);
    foreach ($paragraph_fields as $field_name) {
      if (empty($row = $this->processParagraphReferenceField($entity, $field_name))) {
        $row = $entity->get($field_name)->getValue();
      }
      $data[$field_name] = $row;
    }
    return $data;
  }

  /**
   * Process paragraph entity reference revision field.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $entity
   *   The paragraph entity object.
   * @param string $field_name
   *   The entity reference revision field name.
   *
   * @return array
   *   Return array of processed paragraph data.
   */
  protected function processParagraphReferenceField(ParagraphInterface $entity, string $field_name): array {
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
        // Merge result with data as getFieldLink contains keyed array
        // with link and deeplink.
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
        if ($term instanceof TermInterface && $term->hasTranslation($this->currentLanguage)) {
          $term = $term->getTranslation($this->currentLanguage);
        }
        $data['title'] = $term->label();
      }
      $data['items'] = $this->getAllCategories($category_id, $this->currentLanguage);
    }
    else {
      // Get selected category's child so it can be passed as views argument.
      $terms = _alshaya_master_get_recursive_child_terms($category_id);
      $arguments = ['tid' => implode('+', $terms)];

      // Invoke views display in executeInRenderContext to avoid cached
      // metadata leak issue.
      // @See https://www.drupal.org/project/drupal/issues/2450993
      $results = $this->renderer->executeInRenderContext(new RenderContext(), function () use ($arguments) {
        return _alshaya_master_get_views_result('alshaya_product_list', 'block_1', $arguments);
      });
      // Create an array of nodes.
      $nodes = array_map(function ($result) {
        if (($node = $result->_object->getValue()) && $node instanceof NodeInterface) {
          return $node;
        }
      }, $results);

      $carousel_product_limit = (int) $entity->get('field_category_carousel_limit')->getString();
      $nodes = alshaya_acm_product_filter_out_of_stock_products($nodes, $carousel_product_limit);

      if (!empty($nodes)) {
        $data['items'] = array_map(function ($node) {
          return $this->getLightProductFromNid($node->id(), $this->currentLanguage);
        }, $nodes);
      }
    }
    return $data;
  }

}
