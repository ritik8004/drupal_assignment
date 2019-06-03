<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Update app id and secret here for other commands to work.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'custom.php';

$source_app_id = 'VP3QKEIIC5';
$source_app_secret_admin = 'a695124fb2716596ee47a1521e3fb2a0';
$source_index = 'mckw_01dev2';

$languages = [
  'en',
  'ar',
];

$sorts = [
  [
    'field' => 'search_api_relevance',
    'direction' => 'desc',
  ],
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

$searchable_attributes = [
  'title',
  'field_category_name',
  'attr_product_brand',
  'sku',
  'field_configured_skus',
  'attr_product_collection',
  'attr_concept',
  'attr_color',
  'attr_size',
  'body',
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
  ['field_acq_promotion_label'],
  ['attr_product_brand'],
  ['attr_product_collection'],
  ['attr_concept'],
  ['attr_color'],
];
