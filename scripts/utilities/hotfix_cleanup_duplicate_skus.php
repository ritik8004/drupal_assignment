<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Check CORE-14577.
 */

$connection = \Drupal::database();
$logger = \Drupal::logger('cleanDuplicateSkus');

$query = 'SELECT sku FROM {acq_sku_field_data} fd1 GROUP BY sku HAVING count(*) > 2';
$result = $connection->query($query)->fetchAllKeyed(0, 0);
if (empty($result)) {
  return;
}

$message = dt('Duplicate SKUs found for: @entries', [
  '@entries' => print_r($result, TRUE),
]);

$logger->notice($message);

foreach (array_reverse($result) as $sku) {
  $sku_records = $connection->query('SELECT id FROM {acq_sku_field_data} WHERE sku=:sku', [
    ':sku' => $sku,
  ])->fetchAllKeyed(0, 0);

  // Remove the first ID we use in Development.
  array_shift($sku_records);

  foreach ($sku_records as $id) {
    $connection->query("UPDATE acq_sku_field_data SET sku = CONCAT(sku, '-hotfix') WHERE id = $id");
    $connection->query("UPDATE acq_sku__field_configured_skus SET field_configured_skus_value = CONCAT(field_configured_skus_value, '-hotfix') WHERE entity_id = $id");

    $logger->notice(dt('Updated SKU with ID @id for SKU @sku', [
      '@id' => $id,
      '@sku' => $sku,
    ]));
  }
}
