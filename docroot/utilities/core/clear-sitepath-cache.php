<?php

/**
 * @file
 * Looks for sitepath entries in the apcu cache and deletes them.
 *
 * @todo Add some protection around this, for example basic auth.
 */

$counter = 0;

foreach (new APCUIterator('/^sitepath:*/') as $key => $value) {
  apcu_delete($key);
  $counter++;
}

header("Cache-Control: no-cache, must-revalidate");
echo "Flushed {$counter} entries.";
