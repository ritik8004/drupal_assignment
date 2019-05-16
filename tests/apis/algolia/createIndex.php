<?php

/**
 * @file
 * WIP: Create indexes with settings copied from source.
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use AlgoliaSearch\Client;

$languages = ['en', 'ar'];

$sorts = [
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
  [
    'field' => 'search_api_relevance',
    'direction' => 'desc',
  ],
];

$searchable_attributes = [
  'title',
  'field_category_name',
  'body',
  'sku',
  'attr_color',
  'attr_product_brand',
  'attr_size',
];

$facets = [
  'attr_color',
  'attr_product_brand',
  'attr_selling_price',
  'attr_size',
  'field_acq_promotion_label',
  'field_category',
  'final_price',
  'search_api_language',
];

$client = new Client('HGR051I5XN', '6fc229a5d5d0f0d9cc927184b2e4af3f');
foreach ($languages as $language) {
  $name = 'local_' . $language;
  $client->copyIndex('mckw_local', $name);

  foreach ($sorts as $sort) {
    $suffix = implode('_', $sort);
    $client->copyIndex('mckw_local_' . $suffix, $name . '_' . $suffix);
    print $name . '_' . $suffix . PHP_EOL;
  }
}
