<?php
// phpcs:ignoreFile

/**
 * @file
 * Update app id and secret here for other commands to work.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'custom.php';

global $source_app_id, $source_app_secret_admin, $source_index;
global $languages, $sorts, $facets, $query_facets, $query_generate;
global $searchable_attributes, $ranking;

$source_app_id = 'HGR051I5XN';
$source_index = '01live_hmkw';

// Please enter admin key below for HM.
$source_app_secret_admin = '';

$languages = [
  'en',
  'ar',
];

// Product List Index suffix.
$product_list_suffix = 'product_list';

$sorts = [
//  [
//    'field' => 'search_api_relevance',
//    'direction' => 'desc',
//  ],
  [
    'field' => 'title',
    'direction' => 'asc',
  ],
  [
    'field' => 'title',
    'direction' => 'desc',
  ],
  [
    'field' => 'final_price',
    'direction' => 'asc',
  ],
  [
    'field' => 'final_price',
    'direction' => 'desc',
  ],
  [
    'field' => 'created',
    'direction' => 'desc',
  ],
];

$facets = [
  'field_category_name',
  'lhn_category',
  'promotion_nid',
  'attr_collection_1',
  'attr_fragrance_name',
  'attr_fragrance_category',
  'field_category',
  'attr_selling_price',
  'attr_product_collection',
  'field_acq_promotion_label',
];

$searchable_attributes = [
  'title.en',
  'title.ar',
  'field_category_name.en.lvl0',
  'field_category_name.ar.lvl0',
  'field_category_name.en.lvl1',
  'field_category_name.ar.lvl1',
  'field_category_name.en.lvl2',
  'field_category_name.ar.lvl2',
  'sku',
];

$ranking = [
  'desc(stock)',
  'attribute',
  'typo',
  'geo',
  'words',
  'filters',
  'proximity',
  'exact',
  'custom',
];

// Add Attributes whose facet does not require language suffix.
$attributes_skip_lang_suffix = [
  'stock',
  'promotion_nid',
  'sku',
  'search_api_language',
  'gtm',
  'nid',
  'search_api_datasource',
  'search_api_id',
  'stock_quantity',
  'created',
];
