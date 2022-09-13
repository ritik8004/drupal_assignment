<?php

/**
 * @file
 * Include file for RCS Commercetools Placeholders Tokens.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_tokens().
 */
function rcs_magento_placeholders_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  // We only deal with RCS tokens.
  $rcs_types = [
    'rcs_category',
    'rcs_product',
    'rcs_product_item',
    'rcs_cart_item',
    'rcs_cart',
    'rcs_shipping',
  ];

  if (!in_array($type, $rcs_types)) {
    return [];
  }

  $replacements = [];

  foreach ($tokens as $name => $original) {
    [$field, $switch] = strpos($name, '.') > -1
      ? explode('.', $name)
      : [$name, ''];

    switch ($type) {
      case 'rcs_category':
        switch ($field) {
          case 'name':
            $replacements[$original] = '#rcs.category.name#';
            break;

          case 'slug':
            $replacements[$original] = '#rcs.category.url_path#';
            break;

          case 'url':
            $replacements[$original] = '#rcs.category.url_path#';
            break;

        }
        break;

      case 'rcs_product':
      case 'rcs_product_item':
        switch ($field) {
          case 'id':
            $replacements[$original] = '#rcs.product.id#';
            break;

          case 'name':
            $replacements[$original] = '#rcs.product.name#';
            break;

          case 'slug':
            $replacements[$original] = '#rcs.product.url_key#';
            break;

          case 'url':
            $replacements[$original] = '#rcs.product.url_key#.html';
            break;

        }
        break;

    }

  }

  return $replacements;
}
