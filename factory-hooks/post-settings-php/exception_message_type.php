<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$message_types = [
  'This product is out of stock.' => 'OOS',
  'Some of the products are out of stock.' => 'OOS',
  'Not all of your products are available in the requested quantity.' => 'OOS',
  "We don't have as many" => 'OOS',
  'هذا المنتج غير متوفر في المخزن.' => 'OOS',
  'بعض المنتجات غير متوفرة بالمخزن.' => 'OOS',
  'بعض المنتجات غير متوفرة بالمخزن.' => 'OOS',
  'ليس لدينا العديد من' => 'OOS',
  'The maximum quantity per item has been exceeded' => 'quantity_limit',
  'Fraud rule detected. Reauthorization is required' => 'FRAUD',
  'Product that you are trying to add is not available.' => 'OOS',
];

$settings['alshaya_spc.exception_message'] = $message_types;
