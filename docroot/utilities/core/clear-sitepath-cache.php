<?php

/**
 * @file
 * Looks for sitepath entries in the apcu cache and deletes them.
 */

$counter = 0;

foreach (new APCUIterator('/^sitepath:*/') as $key => $value) {
  apcu_delete($key);
  $counter++;
}

echo "Flushed {$counter} entries.";
header("Cache-Control: no-cache, must-revalidate");
