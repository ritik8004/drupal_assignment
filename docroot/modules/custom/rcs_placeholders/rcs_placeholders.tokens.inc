<?php

/**
 * @file
 * Include file for RCS Placeholders Tokens.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function rcs_placeholders_token_info() {
  $info = [];

  $info['types']['rcs'] = [
    'name' => t('RCS'),
    'description' => t('Tokens related to RCS.'),
  ];

  $info['types']['rcs_category'] = [
    'name' => t('RCS Category'),
    'description' => t('Tokens related to Category.'),
  ];

  $info['types']['rcs_product'] = [
    'name' => t('RCS Product'),
    'description' => t('Tokens related to Product.'),
  ];

  $info['tokens']['rcs']['product-path-prefix'] = [
    'name' => t('RCS product path prefix'),
    'description' => t('The configured path prefix for RCS products.'),
  ];

  $info['tokens']['rcs']['category-path-prefix'] = [
    'name' => t('RCS category path prefix'),
    'description' => t('The configured path prefix for RCS categories.'),
  ];

  $info['tokens']['rcs']['promotion-path-prefix'] = [
    'name' => t('RCS promotion path prefix'),
    'description' => t('The configured path prefix for RCS promotion.'),
  ];

  // Following token must be implemented by the commerce backend.
  // It could be altered further by custom modules.
  // #rcs.product.name#.
  $info['tokens']['rcs_product']['name'] = [
    'name' => t('Name'),
    'description' => t('Product name.'),
  ];

  // #rcs.product.slug#.
  $info['tokens']['rcs_product']['slug'] = [
    'name' => t('Slug'),
    'description' => t('Product slug to be used for URL.'),
  ];

  // [rcs:product-path-prefix]#rcs.product.slug#.
  $info['tokens']['rcs_product']['url'] = [
    'name' => t('URL'),
    'description' => t('Combination of product path prefix and slug.'),
  ];

  // #rcs.productItem.id#.
  $info['tokens']['rcs_product_item']['id'] = [
    'name' => t('ID'),
    'description' => t('ID of the product in list.'),
  ];

  // #rcs.productItem.slug#.
  $info['tokens']['rcs_product_item']['slug'] = [
    'name' => t('RCS category path prefix'),
    'description' => t('Slug of the product in list.'),
  ];

  // [rcs:product-path-prefix]#rcs.productItem.slug#.
  $info['tokens']['rcs_product_item']['url'] = [
    'name' => t('URL'),
    'description' => t('URL (combination of prefix and slug) of the product in list.'),
  ];

  // #rcs.productItem.name#.
  $info['tokens']['rcs_product_item']['name'] = [
    'name' => t('Name'),
    'description' => t('Name of the product in list.'),
  ];

  // #rcs.category.name#.
  $info['tokens']['rcs_category']['name'] = [
    'name' => t('Name'),
    'description' => t('Name of RCS categories.'),
  ];

  // #rcs.category.slug#.
  $info['tokens']['rcs_category']['slug'] = [
    'name' => t('Slug'),
    'description' => t('Slug of RCS Category.'),
  ];

  // [rcs:category-path-prefix]#rcs.category.slug#.
  $info['tokens']['rcs_category']['url'] = [
    'name' => t('URL'),
    'description' => t('URL (combination of prefix and slug) of RCS Category.'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function rcs_placeholders_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  // We define tokens for more types above in rcs_placeholders_token_info().
  // These must be implemented in the commerce backend module and further
  // altered in brand/site specific modules.
  $replacements = [];

  // Provide replacements for RCS tokens.
  if ($type === 'rcs') {
    $config = \Drupal::config('rcs_placeholders.settings');
    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'product-path-prefix':
          $replacements[$original] = $config->get('product.path_prefix');
          break;

        case 'category-path-prefix':
          $replacements[$original] = $config->get('category.path_prefix');
          break;

        case 'promotion-path-prefix':
          $replacements[$original] = $config->get('promotion.path_prefix');
          break;
      }
    }
  }

  return $replacements;
}
