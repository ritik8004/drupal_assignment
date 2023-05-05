<?php

namespace Drupal\alshaya_rcs_product\Services;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\rcs_placeholders\Service\RcsPhPathProcessor;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Contains helper methods rcs product.
 *
 * @package Drupal\alshaya_rcs_product\Services
 */
class AlshayaRcsProductHelper {

  /**
   * RCS Content type id.
   */
  public const RCS_CONTENT_TYPE_ID = 'rcs_product';

  /**
   * Source Content type.
   */
  public const SOURCE_CONTENT_TYPE_ID = 'acq_product';

  /**
   * Route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * Node Storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   Route match service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(
    RouteMatchInterface $current_route_match,
    ConfigFactoryInterface $config_factory,
    ModuleHandlerInterface $module_handler,
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    AliasManagerInterface $alias_manager,
    Connection $connection,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->currentRouteMatch = $current_route_match;
    $this->moduleHandler = $module_handler;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->aliasManager = $alias_manager;
    $this->connection = $connection;
    $this->logger = $logger_factory->get('alshaya_rcs_product');
  }

  /**
   * Returns the URL key for the product for use in graphql requests.
   *
   * @return string
   *   The product url key.
   */
  public function getProductUrlKey() {
    $url_key = RcsPhPathProcessor::getFullPath(TRUE);
    return str_replace('.html', '', $url_key);
  }

  /**
   * Returns if ppage is RCS PDP or not.
   *
   * @return bool
   *   If page is RCS PDP or not.
   */
  public function isRcsPdp() {
    foreach ($this->currentRouteMatch->getParameters() as $route_parameter) {
      if ($route_parameter instanceof NodeInterface) {
        if ($route_parameter->bundle() === 'rcs_product') {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  /**
   * Get the query for recommended products.
   *
   * @return array
   *   Recommended product query fields array.
   */
  public function getRecommendedProductQuery(string $type) {
    $query_fields = [
      'sku',
      'id',
      'name',
      'type_id',
      'url_key',
      'is_buyable',
      'stock_status',
      'price_range' => [
        'maximum_price' => [
          'regular_price' => [
            'value',
          ],
          'final_price' => [
            'value',
          ],
          'discount' => [
            'percent_off',
          ],
        ],
      ],
      'media_gallery' => [
        'url',
        'label',
        'styles',
        '... on ProductImage' => [
          'url',
          'label',
        ],
      ],
      '... on ConfigurableProduct' => [
        'variants' => [
          'product' => [
            'id',
            'sku',
            'stock_status',
            'price_range' => [
              'maximum_price' => [
                'regular_price' => [
                  'value',
                ],
                'final_price' => [
                  'value',
                ],
                'discount' => [
                  'percent_off',
                ],
              ],
            ],
            'stock_data' => [
              'qty',
              'max_sale_qty',
            ],
            'media_gallery' => [
              'url',
              'label',
              'styles',
              '... on ProductImage' => [
                'url',
                'label',
              ],
            ],
          ],
        ],
      ],
      'gtm_attributes' => [
        'id',
        'name',
        'variant',
        'price',
        'brand',
        'category',
        'dimension2',
        'dimension3',
        'dimension4',
      ],
    ];

    $this->moduleHandler->alter('alshaya_rcs_recommended_product_query_fields', $query_fields);

    return [
      'query' => [
        'query ($sku: String)' => [
          'products(filter: {sku: {eq: $sku}})' => [
            'items' => [
              "$type" => $query_fields,
            ],
          ],
        ],
      ],
      'variables' => [
        'sku' => NULL,
      ],
    ];
  }

  /**
   * Gets the configurable attributes for the products in the site.
   *
   * @return array
   *   The configurable attributes for the products in the site.
   */
  public function getConfigurableAttributes() {
    $attributes = &drupal_static(__METHOD__, []);
    if (!empty($attributes)) {
      return $attributes;
    }

    $attribute_weights = $this->configFactory->get('acq_sku.configurable_form_settings')->get('attribute_weights');
    foreach ($attribute_weights as $group) {
      asort($group);

      foreach (array_keys($group) as $attribute) {
        $attributes[] = $attribute;
      }
    }

    // Return unique attributes in a non-associative array.
    return array_values(array_unique($attributes));
  }

  /**
   * Returns the basic fields needed to be added to the product graphql query.
   *
   * @return array
   *   The product query fields.
   */
  public function getProductQueryFields() {
    $static = &drupal_static(__METHOD__, []);
    if (!empty($static)) {
      return $static;
    }

    // The main query fields for the product query.
    $fields = [
      'total_count',
      'items' => [
        'sku',
        'id',
        'type_id',
        'name',
        'description' => [
          'html',
        ],
        'url_key',
        'is_buyable',
        'stock_status',
        'express_delivery',
        'same_day_delivery',
        'ship_to_store',
        'is_returnable',
        'reserve_and_collect',
        'free_gift_promotion' => [
          'rule_id',
          'rule_type',
          'rule_web_url',
          'rule_name',
          'rule_description',
          'auto_add',
          'max_gift',
          'coupon_code',
          'total_items',
          'gifts' => [
            'id',
            'sku',
            'name',
          ],
        ],
        'price_range' => [
          'maximum_price' => [
            'regular_price' => [
              'value',
            ],
            'final_price' => [
              'value',
            ],
            'discount' => [
              'percent_off',
            ],
          ],
        ],
        'brand_logo_data' => [
          'url',
          'alt',
          'title',
        ],
        'media_gallery' => [
          'url',
          'label',
          'styles',
          '... on ProductVideo' => [
            'video_content' => [
              'media_type',
              'video_provider',
              'video_url',
              'video_title',
              'video_description',
              'video_metadata',
            ],
          ],
        ],
        'gtm_attributes' => [
          'id',
          'name',
          'variant',
          'price',
          'brand',
          'category',
          'dimension2',
          'dimension3',
          'dimension4',
        ],
        'meta_title',
        'meta_description',
        'meta_keyword',
        'og_meta_title',
        'og_meta_description',
        'stock_data' => [
          'max_sale_qty',
          'qty',
        ],
        'promotions' => [
          'context',
          'url',
          'label',
          'type',
        ],
        '... on ConfigurableProduct' => [
          'configurable_options' => [
            'attribute_uid',
            'label',
            'position',
            'attribute_code',
            'values' => [
              'value_index',
              'store_label',
            ],
          ],
          'variants' => [
            'product' => [
              'id',
              'sku',
              'meta_title',
              'stock_status',
              'express_delivery',
              'same_day_delivery',
              'ship_to_store',
              'is_returnable',
              'reserve_and_collect',
              'attribute_set_id',
              'swatch_data' => [
                'swatch_type',
              ],
              'free_gift_promotion' => [
                'rule_id',
                'rule_type',
                'rule_web_url',
                'rule_name',
                'rule_description',
                'auto_add',
                'max_gift',
                'coupon_code',
                'total_items',
                'gifts' => [
                  'id',
                  'sku',
                  'name',
                ],
              ],
              'price_range' => [
                'maximum_price' => [
                  'regular_price' => [
                    'value',
                  ],
                  'final_price' => [
                    'value',
                  ],
                  'discount' => [
                    'percent_off',
                  ],
                ],
              ],
              'stock_data' => [
                'qty',
                'max_sale_qty',
              ],
              'media_gallery' => [
                'url',
                'label',
                'styles',
                '... on ProductImage' => [
                  'url',
                  'label',
                ],
                '... on ProductVideo' => [
                  'video_content' => [
                    'media_type',
                    'video_provider',
                    'video_url',
                    'video_title',
                    'video_description',
                    'video_metadata',
                  ],
                ],
              ],
              'promotions' => [
                'context',
                'url',
                'label',
                'type',
              ],
            ],
            'attributes' => [
              'label',
              'code',
              'value_index',
            ],
          ],
        ],
        'category_ids_in_admin',
        'breadcrumb_category_id',
        'categories' => [
          'id',
          'name',
          'level',
          'url_path',
          'include_in_menu',
          'breadcrumbs' => [
            'category_name',
            'category_id',
            'category_level',
            'category_url_key',
            'category_url_path',
          ],
        ],
      ],
    ];

    // Add attributes to be displayed on PDP title to the query.
    $title_attributes = $this->configFactory->get('alshaya_acm_product.display_settings')->get('pdp_title_attributes');
    if ($title_attributes) {
      foreach (explode(',', $title_attributes) as $attribute) {
        array_push(
          $fields['items'],
          $attribute
        );
      }
    }

    $this->moduleHandler->alter('alshaya_rcs_product_query_fields', $fields);

    // Add the configurable product attributes dynamically.
    $attributes = $this->getConfigurableAttributes();
    foreach ($attributes as $attribute) {
      // Prevent duplicate entry.
      if (array_search($attribute, $fields['items']['... on ConfigurableProduct']['variants']['product']) === FALSE) {
        $fields['items']['... on ConfigurableProduct']['variants']['product'][] = $attribute;
      }
    }

    $static = $fields;

    return $static;
  }

  /**
   * Returns the main query and variables getting product options data.
   *
   * @return array
   *   The product options query and variables data.
   */
  public function getProductOptionsQueryFields() {
    return [
      'items' => [
        'attribute_code',
        'attribute_options' => [
          'label',
          'value',
        ],
      ],
    ];
  }

  /**
   * Returns product additional attributes query fields.
   *
   * @return array
   *   Product additional attributes query.
   */
  public function getProductAdditionalAttributesQueryFields() {
    $query = [];
    $attributes = [];
    $attributes = $this->moduleHandler->invokeAll('alshaya_rcs_product_additional_attributes_query_fields', [$attributes]);
    $query['items'] = $attributes;
    return $query;
  }

  /**
   * Returns the main query and variables getting product options data.
   *
   * @return array
   *   The product options query and variables data.
   */
  public function getProductOptionsQueryVariables() {
    $options = &drupal_static(__METHOD__, []);
    if (!empty($options)) {
      return $options;
    }

    // Fetch the attributes provided by other modules.
    $options = $this->moduleHandler->invokeAll('alshaya_rcs_product_product_options_to_query');

    $title_attributes_options = $this->configFactory->get('alshaya_acm_product.display_settings')->get('pdp_title_attributes');
    if (!empty($title_attributes_options)) {
      $options = array_merge($options, explode(',', $title_attributes_options));
    }

    $attributes = $this->getConfigurableAttributes();
    // Add the configurable attributes.
    $options = array_merge($options, $attributes);
    if (count($options) > 0) {
      // Remove duplicate elements from the array.
      // Same attributes may be added by hook, so this is to prevent from
      // querying the same attribute multiple times.
      $options = array_unique($options);
      // Reindex the array.
      $options = array_values($options);
      // Process data to required format.
      $options = array_map(fn($option) => [
        'attribute_code' => $option,
        'entity_type' => 4,
      ], $options);
    }

    return $options;
  }

  /**
   * Process node data migration to RCS content type.
   */
  public function processProductMigrationToRcsCt() {
    $langcode = $this->languageManager->getDefaultLanguage()->getId();

    $query = $this->connection->select('node_field_data', 'nfd');
    $query->fields('nfd', ['nid']);

    // Join pdp layout field table to select only those nodes
    // that have value in select pdp layout field.
    $query->innerJoin('node__field_select_pdp_layout', 'nfspl', 'nfspl.entity_id = nfd.nid AND nfspl.langcode = nfd.langcode');

    $query->condition('nfd.langcode', $langcode);
    $query->condition('nfd.status', NodeInterface::PUBLISHED);
    $query->condition('nfd.type', self::SOURCE_CONTENT_TYPE_ID);

    $pdp_layout = $this->configFactory->get('alshaya_acm_product.settings')->get('pdp_layout');
    if (!empty($pdp_layout)) {
      // Ignore products with brand level pdp layout.
      $query->condition('nfspl.field_select_pdp_layout_value', $pdp_layout, '!=');
    }

    $nodes = $query->distinct()->execute()->fetchAll();

    // Do not process if no nodes are found.
    if (empty($nodes)) {
      return;
    }

    // Migrate rcs content type.
    foreach ($nodes as $node) {
      try {
        /** @var \Drupal\node\Entity\Node $node_data */
        $node_data = $this->nodeStorage->load($node->nid);

        // Create a new rcs_product node object.
        /** @var \Drupal\node\Entity\Node $rcs_node */
        $rcs_node = $this->nodeStorage->create([
          'type' => self::RCS_CONTENT_TYPE_ID,
          'title' => $node_data->getTitle(),
          'status' => NodeInterface::PUBLISHED,
          'langcode' => $langcode,
        ]);

        $rcs_node->get('field_select_pdp_layout')
          ->setValue($node_data->get('field_select_pdp_layout')->getValue());

        // Get slug field value from old node alias.
        $slug = $this->aliasManager->getAliasByPath('/node/' . $node_data->id());
        // Trimout the front and back slashes.
        $slug = trim($slug, '/');

        $rcs_node->get('field_product_slug')->setValue($slug);

        // Check if the translations exists for arabic language.
        $languages = $node_data->getTranslationLanguages(FALSE);
        foreach ($languages as $language) {
          if (!$node_data->hasTranslation($language->getId())) {
            continue;
          }

          // Get node translation.
          $node_translation_data = $node_data->getTranslation($language->getId());

          // Add translation to the new node.
          $rcs_node = $rcs_node->addTranslation($language->getId(), [
            'title' => $node_translation_data->getTitle(),
            'field_select_pdp_layout' => $node_translation_data->get('field_select_pdp_layout')->getValue(),
          ]);
        }

        // Delete product node.
        $node_data->delete();

        // Save the new node object in rcs content type.
        $rcs_node->save();
      }
      catch (\Exception $exception) {
        $this->logger->error('Error while migrating nodes to RCS content type. message:@message', [
          '@message' => $exception->getMessage(),
        ]);
      }
    }
  }

  /**
   * Rollback node data from RCS content type.
   */
  public function rollbackProductMigration() {
    // Get the placeholder node from config.
    $entity_id = $this->configFactory->get('rcs_placeholders.settings')->get('product.placeholder_nid');

    // Get all the nodes from rcs content type, except placeholder node.
    try {
      $query = $this->nodeStorage->getQuery();
      $query->condition('type', self::RCS_CONTENT_TYPE_ID);
      $query->condition('nid', $entity_id, '<>');
      $nodes = $query->execute();
    }
    catch (\Exception $exception) {
      $this->logger->error('Error while fetching RCS nodes for deletion. message:@message', [
        '@message' => $exception->getMessage(),
      ]);
    }

    // Return if none available.
    if (empty($nodes)) {
      return;
    }

    // Delete nodes from RCS content type.
    foreach ($nodes as $node) {
      try {
        $this->nodeStorage->load($node)->delete();
      }
      catch (\Exception $exception) {
        $this->logger->error('Error while deleting nodes from RCS content type. message:@message', [
          '@message' => $exception->getMessage(),
        ]);
      }
    }
  }

  /**
   * Get recent orders fields.
   *
   * @return array
   *   Returns the fields for recent orders query.
   */
  public function getRecentOrderFields() {
    static $fields = NULL;
    if ($fields) {
      return $fields;
    }

    $fields = [
      'total_count',
      'items' => [
        'type_id',
        'sku',
        'name',
        'media_gallery' => [
          '... on ProductImage' => [
            'url',
            'label',
            'styles',
          ],
        ],
        '... on ConfigurableProduct' => [
          'variants' => [
            'product' => [
              'sku',
              'name',
              'media_gallery' => [
                '... on ProductImage' => [
                  'url',
                  'label',
                  'styles',
                ],
              ],
            ],
          ],
        ],
      ],
    ];

    $this->moduleHandler->alter('alshaya_rcs_product_recent_orders_fields', $fields);
    return $fields;
  }

  /**
   * Get order details fields.
   *
   * @return array
   *   Returns the fields for order details query.
   */
  public function getOrderDetailsFields() {
    $fields = $this->getRecentOrderFields();
    $fields['items']['... on ConfigurableProduct']['configurable_options'] = [
      'label',
      'attribute_code',
      'attribute_uid',
    ];
    $fields['items']['... on ConfigurableProduct']['variants']['attributes'] = [
      'label',
      'code',
      'value_index',
    ];

    $this->moduleHandler->alter('alshaya_rcs_product_order_details_fields', $fields);
    return $fields;
  }

  /**
   * Adds common drupal settings variables to page attachment.
   *
   * @param array $attachments
   *   Page attachment/build variable.
   */
  public function setCommonPdpSettings(array &$attachments) {
    $product_settings = $this->configFactory->get('alshaya_acm_product.settings');
    $attachments['#attached']['drupalSettings']['alshayaRcs']['pdpGalleryLimit'] = [
      'modal' => $product_settings->get('pdp_slider_items_settings.pdp_slider_items_number_cs_us'),
      'others' => $product_settings->get('pdp_gallery_pager_limit'),
    ];
    $attachments['#attached']['drupalSettings']['alshayaRcs']['pdpGalleryType'] = $product_settings->get('pdp_gallery_type');
    $alshaya_master_settings = $this->configFactory->get('alshaya_master.settings');
    $attachments['#attached']['drupalSettings']['alshayaRcs']['lazyLoadPlaceholder'] = $alshaya_master_settings->get('lazy_load_placeholder');

    $product_display_settings = $this->configFactory->get('alshaya_acm_product.display_settings');
    $attachments['#attached']['drupalSettings']['alshayaRcs']['pdpSwatchAttributes'] = $product_display_settings->get('swatches.pdp');
    $attachments['#attached']['drupalSettings']['alshayaRcs']['pdpSizeGroupAttribute'] = $product_display_settings->get('size_group.pdp');
    $attachments['#attached']['drupalSettings']['alshayaRcs']['pdpSizeGroupAlternates'] = $product_display_settings->get('size_group.alternates');
    $attachments['#attached']['drupalSettings']['alshayaRcs']['shortDescLimit'] = $product_display_settings->get('short_desc_characters');
    $attachments['#attached']['drupalSettings']['show_configurable_boxes_after'] = $product_display_settings->get('show_configurable_boxes_after');
    $attachments['#attached']['drupalSettings']['alshayaRcs']['useParentImages'] = $product_display_settings->get('configurable_use_parent_images');
    $attachments['#attached']['drupalSettings']['alshayaRcs']['colorAttributeConfig'] = $product_display_settings->get('color_attribute_config');
    $attachments['#attached']['drupalSettings']['alshayaRcs']['priceDisplayMode'] = $product_display_settings->get('price_display_mode') ?? 'simple';
    $attachments['#attached']['drupalSettings']['showPreSelectedVariantOnPdp'] = $product_display_settings->get('show_pre_selected_variant_on_pdp');
    $attachments['#attached']['drupalSettings']['alshayaRcs']['pdpTitleAttributes'] = !empty($product_display_settings->get('pdp_title_attributes'))
      ? explode(',', $product_display_settings->get('pdp_title_attributes'))
      : [];

    $attachments['#cache']['tags'] = Cache::mergeTags(
      $attachments['#cache']['tags'],
      $product_settings->getCacheTags(),
      $product_display_settings->getCacheTags(),
      $alshaya_master_settings->getCacheTags(),
    );
  }

}
