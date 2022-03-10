<?php

namespace Drupal\alshaya_rcs_product\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\rcs_placeholders\Service\RcsPhPathProcessor;

/**
 * Contains helper methods rcs product.
 */
class AlshayaRcsProductHelper {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * Config Factory service.
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
   * Class constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   Route match service..
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service.
   */
  public function __construct(
    RouteMatchInterface $current_route_match,
    ConfigFactoryInterface $config_factory,
    ModuleHandlerInterface $module_handler
  ) {
    $this->currentRouteMatch = $current_route_match;
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
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
        'reserve_and_collect',
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
              'reserve_and_collect',
              'attribute_set_id',
              'swatch_data' => [
                'swatch_type',
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
              'is_returnable',
              'stock_data' => [
                'qty',
                'max_sale_qty',
              ],
              'media_gallery' => [
                'url',
                'label',
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
            ],
            'attributes' => [
              'label',
              'code',
              'value_index',
            ],
          ],
        ],
        'category_ids_in_admin',
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

    // Add the recommended products fields to the main query body.
    $recommended_product_settings = $this->configFactory->get('alshaya_acm.settings');
    // Add query for upsell products if display setting is true.
    if ($recommended_product_settings->get('display_upsell')) {
      $fields['items']['upsell_products'] = _alshaya_rcs_product_get_recommended_product_field_query();
    }
    // Add query for related products if display setting is true.
    if ($recommended_product_settings->get('display_related')) {
      $fields['items']['related_products'] = _alshaya_rcs_product_get_recommended_product_field_query();
    }
    // Add query for crosssell products if display setting is true.
    if ($recommended_product_settings->get('display_crosssell')) {
      $fields['items']['crosssell_products'] = _alshaya_rcs_product_get_recommended_product_field_query();
    }

    $this->moduleHandler->alter('alshaya_rcs_product_query_fields', $fields);

    $static = $fields;

    return $static;
  }

  /**
   * Returns the main query and variables getting product options data.
   *
   * @return array
   *   The product options query and variables data.
   */
  public function getProductOptionsQuery() {
    return [
      'query' => [
        'customAttributeMetadata(attributes: $attributes)' => [
          'items' => [
            'attribute_code',
            'attribute_options' => [
              'label',
              'value',
            ],
          ],
        ],
      ],
      'variables' => [
        'attributes' => [
          ["attribute_code" => "color", "entity_type" => "4"],
          ["attribute_code" => "size", "entity_type" => "4"],
        ],
      ],
    ];
  }

}
