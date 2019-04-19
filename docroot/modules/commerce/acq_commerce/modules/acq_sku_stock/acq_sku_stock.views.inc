<?php

/**
 * @file
 * Provide views data for acq_sku_stock.module.
 */

/**
 * Implements hook_views_data().
 */
function acq_sku_stock_views_data() {
  $data['acq_sku_stock']['table']['group'] = t('SKU stock');

  $data['acq_sku_stock']['table']['join'] = [
    'acq_sku_field_data' => [
      'left_field' => 'sku',
      'field' => 'sku',
    ],
  ];

  $data['acq_sku_stock']['quantity'] = [
    'title' => t('Total Quantity'),
    'help' => t('The total number of stock available for given sku.'),
    'field' => [
      'id' => 'numeric',
      'click sortable' => TRUE,
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['acq_sku_stock']['status'] = [
    'title' => t('Stock Status'),
    'help' => t('The explicit stock status in-stock or not.'),
    'field' => [
      'id' => 'boolean',
    ],
    'filter' => [
      'id' => 'boolean',
      'label' => t('In stock'),
      'type' => 'yes-no',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  return $data;
}
