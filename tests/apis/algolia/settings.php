<?php
// @codingStandardsIgnoreFile

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
  'attr_color',
  'attr_product_brand',
  'attr_selling_price',
  'attr_size',
  'field_acq_promotion_label',
  'field_category',
  'final_price',
  'attr_product_collection',
  'attr_concept',
];

$query_facets = [
  [
    'attribute' => 'field_category_name',
    'amount' => 1,
  ],
];

$query_generate = [
  ['field_category_name'],
];

$searchable_attributes = [
  'title',
  'field_category_name.lvl0',
  'field_category_name.lvl2',
  'field_category_name.lvl1',
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
