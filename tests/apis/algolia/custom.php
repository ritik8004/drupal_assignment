<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Custom code to add support for creating query suggestions.
 */

function algolia_get_query_suggestions($app_id, $app_secret_admin, $index) {
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL,
    'https://query-suggestions.fi.algolia.com/1/configs');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

  $headers = [];
  $headers[] = 'X-Algolia-Api-Key: ' . $app_secret_admin;
  $headers[] = 'X-Algolia-Application-Id: ' . $app_id;
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
  }

  curl_close($ch);

  $result = array_filter(json_decode($result, TRUE),
    function ($a) use ($index) {
      $sources = array_column($a['sourceIndices'], 'indexName');
      return in_array($index, $sources);
    }
  );

  return $result;
}

function algolia_add_query_suggestion($app_id, $app_secret_admin, $name, $data) {
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, 'https://query-suggestions.fi.algolia.com/1/configs/' . $name);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_TIMEOUT, 3000);

  curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

  $headers = [];
  $headers[] = 'X-Algolia-Api-Key: ' . $app_secret_admin;
  $headers[] = 'X-Algolia-Application-Id: ' . $app_id;
  $headers[] = 'Content-Type: application/x-www-form-urlencoded';
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch) . PHP_EOL . PHP_EOL;
  }
  else {
    print $result . PHP_EOL . PHP_EOL;
  }
  curl_close($ch);
}


function algolia_delete_query_suggestion($app_id, $app_secret_admin, $index) {
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, 'https://query-suggestions.fi.algolia.com/1/configs/' . $index);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
  curl_setopt($ch, CURLOPT_TIMEOUT, 3000);

  $headers = [];
  $headers[] = 'X-Algolia-Api-Key: ' . $app_secret_admin;
  $headers[] = 'X-Algolia-Application-Id: ' . $app_id;
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
  }

  curl_close($ch);
}
