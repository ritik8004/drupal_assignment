<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Check CORE-13815.
 */

/** @var \Drupal\alshaya_api\AlshayaApiWrapper $apiWrapper */
$result = \Drupal::database()->query('SELECT id, sku from acq_sku_field_data WHERE default_langcode = 1 AND CHAR_LENGTH(sku) < 10')->fetchAll();

$to_update = [];
$skus = [];
foreach ($result as $row) {
  $skus[$row->id] = $row->sku;
}

if (empty($skus)) {
  print 'No faulty sku found.' . PHP_EOL;
  die();
}

/** @var \Drupal\alshaya_api\AlshayaApiWrapper $apiWrapper */
$apiWrapper = \Drupal::service('alshaya_api.api');
$mskus = $apiWrapper->getSkusData();

$magento_data = [];
foreach ($mskus as $chunk) {
  $magento_data = array_merge($magento_data, $chunk);
}

$available_skus = [];
foreach ($skus as $id => $sku) {
  if (isset($magento_data[$sku])) {
    $available_skus[$id] = $sku;
    continue;
  }

  $impacted_skus = [];

  $query = "SELECT DISTINCT(sku) as impacted_sku FROM acq_sku_field_data WHERE type = 'configurable' AND attr_style_code like '%$sku%'";
  $result = \Drupal::database()->query($query)->fetchAll();

  print "$id => $sku" . PHP_EOL;
  $to_update[$id] = $sku;
  foreach ($result as $row) {
    $impacted_skus[] = $row->impacted_sku;
  }

  if (!empty($impacted_skus)) {
    print 'Impacted skus: ' . implode(',', $impacted_skus);
    print PHP_EOL;
  }
}

print PHP_EOL . PHP_EOL;
print '====================================';
print PHP_EOL . PHP_EOL;

if (!empty($available_skus)) {
  print 'SKUs available in Magento too: ' . implode(',', $available_skus);
  print PHP_EOL . PHP_EOL;
  print '====================================';
  print PHP_EOL . PHP_EOL;
}

// Remove die below to update those SKUs
$affected = 0;
foreach ($to_update as $id => $sku) {
  print "Updating data for $id : $sku" . PHP_EOL;
  $query = "UPDATE acq_sku__field_configured_skus SET field_configured_skus_value = CONCAT(field_configured_skus_value, '-bfcmhotfix') WHERE entity_id = $id";
  $affected += \Drupal::database()->query($query, [], ['return' => 2]);
}

print PHP_EOL . PHP_EOL;
print '====================================';
print PHP_EOL . PHP_EOL;

print 'Total products updated: ' . $affected . PHP_EOL;
print 'Total old parents involved: ' . count($to_update) . PHP_EOL . PHP_EOL;
