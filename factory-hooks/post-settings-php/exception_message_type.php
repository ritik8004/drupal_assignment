<?php
// phpcs:ignoreFile

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$message_types = [
  'This product is out of stock.' => 'OOS',
  'لقد نفدت كمية هذا المنتج.' => 'OOS',
  'Some of the products are out of stock.' => 'OOS',
  'Not all of your products are available in the requested quantity.' => 'OOS',
  "We don't have as many" => 'not_enough',
  "The requested qty is not available" => 'not_enough',
  'هذا المنتج غير متوفر في المخزن.' => 'OOS',
  'بعض المنتجات غير متوفرة بالمخزن.' => 'OOS',
  'بعض المنتجات غير متوفرة بالمخزن.' => 'OOS',
  'ليس لدينا العديد من' => 'not_enough',
  'The maximum quantity per item has been exceeded' => 'quantity_limit',
  'Fraud rule detected. Reauthorization is required' => 'FRAUD',
];

$settings['alshaya_spc.exception_message'] = $message_types;
