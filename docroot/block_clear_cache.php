<?php

/**
 * @file
 * This is an PHP script to replicate CORE-6525.
 */

$discovery = \Drupal::cache('discovery');
$block_plugin_cache = $discovery->get('block_plugins');

$data = $block_plugin_cache->data;

// Unset corrupt cache data and set it empty.
unset($data['facets_summary_block:filter_bar']);
unset($data['facets_summary_block:filter_bar_plp']);
unset($data['facets_summary_block:filter_bar_promotions']);
unset($data['views_exposed_filter_block:alshaya_product_list-block_1']);
unset($data['views_exposed_filter_block:alshaya_product_list-block_2']);

$discovery->set('block_plugins', $data);
