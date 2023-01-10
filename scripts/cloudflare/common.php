<?php
// @codingStandardsIgnoreFile

if (file_exists(__DIR__ . '/settings.php')) {
  require_once 'settings.php';
}
else {
  $home = isset($_ENV['AH_SITE_ENVIRONMENT']) ? $_SERVER['HOME'] : '/home/vagrant';
  require_once $home . '/alshaya_cloudflare_settings.php';
}

function invoke_api(string $api_url, string $method = 'GET', array $data = NULL) {
  if (getenv('debug')) {
    print_r([
      $api_url,
      $method,
      json_encode($data, JSON_THROW_ON_ERROR),
    ]);
  }

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $api_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  if ($method === 'PATCH') {
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));
  }
  elseif ($method !== 'GET' && !empty($data)) {
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));
  }
  elseif ($method === 'PUT') {
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  }
  elseif ($method === 'DELETE') {
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
  }


  $headers = [];
  $headers[] = 'Content-Type: application/json';
  $headers[] = 'Authorization: Bearer ' . $GLOBALS['api_key'];
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
    return NULL;
  }

  curl_close($ch);

  $response = json_decode($result, TRUE);
  if (!is_array($response)) {
    print 'Invalid JSON response.' . PHP_EOL;
    print_r($result);
    return NULL;
  }

  return $response;
}

function get_domains($page = 1) {
  $data = [
    'page' => $page,
    'per_page' => 100,
  ];

  $api_url = 'https://api.cloudflare.com/client/v4/zones';
  $api_url .= '?' . http_build_query($data);

  $result = invoke_api($api_url);
  $domains = [];
  foreach ($result['result'] ?? [] as $row) {
    $domains[$row['name']] = $row['id'];
  }

  if ($result['result_info']['total_pages'] > $page) {
    $page++;
    $domains = array_merge(
      $domains,
      get_domains($page),
    );
  }

  return $domains;
}

function get_zone_for_domain(string $domain) {
  // Remove the first level.
  $domain_to_check = explode('.', $domain);

  while (count($domain_to_check) > 2 && $domain_to_check[1] !== 'alshaya' && $domain_to_check[1] !== 'com') {
    array_shift($domain_to_check);
  }

  $domain_to_check = implode('.', $domain_to_check);

  $data = [
    'page' => 1,
    'per_page' => 20,
    'name' => $domain_to_check,
  ];

  $api_url = 'https://api.cloudflare.com/client/v4/zones';
  $api_url .= '?' . http_build_query($data);

  $result = invoke_api($api_url);
  foreach ($result['result'] ?? [] as $row) {
    return $row['id'];
  }

  return NULL;
}

function create_firewall_rule(string $domain, string $name, string $expression, string $action) {
  $zone_id = get_zone_for_domain($domain);

  if (empty($zone_id)) {
    return NULL;
  }

  // First create the filter.
  $filter_data = [
    [
      'expression' => $expression,
      'description' => $name,
    ],
  ];

  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone_id . '/filters';
  $filters = invoke_api($api_url, 'POST', $filter_data);

  $filter_id = $filters['result'][0]['id'] ?? '';
  if (empty($filter_id)) {
    $filter_id = $filters['errors'][0]['meta']['id'] ?? '';
  }

  if (empty($filter_id)) {
    print_r($filters);
    return NULL;
  }

  // Create the rule using the filter.
  $rule_data = [
    [
      'filter' => [
        'id' => $filter_id,
      ],
      'action' => $action,
      'description' => $name,
    ],
  ];

  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone_id . '/firewall/rules';
  return invoke_api($api_url, 'POST', $rule_data);
}

function get_firewall_rules_for_domain(string $domain, string $zone = NULL) {
  $zone ??= get_zone_for_domain($domain);

  if (empty($zone)) {
    return NULL;
  }

  $data = [
    'page' => 1,
    'per_page' => 25,
  ];

  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone . '/firewall/rules';
  $api_url .= '?' . http_build_query($data);

  return invoke_api($api_url);
}

function update_firewall_rules_for_domain(string $zone, array $rule) {
  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone . '/firewall/rules';
  return invoke_api($api_url, 'PUT', $rule);
}

function delete_firewall_rule_for_domain(string $zone, string $id) {
  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone . '/firewall/rules';

  $data = ['id' => $id];
  $api_url .= '?' . http_build_query($data);

  return invoke_api($api_url, 'DELETE');
}

function update_firewall_filter_for_domain(string $zone, array $filter) {
  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone . '/filters';
  return invoke_api($api_url, 'PUT', $filter);
}

function delete_firewall_filter_for_domain(string $zone, string $id) {
  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone . '/filters';

  $data = ['id' => $id];
  $api_url .= '?' . http_build_query($data);

  return invoke_api($api_url, 'DELETE');
}

function always_online_update(string $zone, string $value) {
  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone . '/settings/always_online';
  return invoke_api($api_url, 'PATCH', ['value' => $value]);
}

function clear_cache_for_domain(string $zone, string $domain) {
  $data = [
    'prefixes' => [
      $domain . '/en',
      $domain . '/ar',
    ],
  ];

  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone . '/purge_cache';

  return invoke_api($api_url, 'POST', $data);
}

function clear_cache_for_url(string $zone, string $url) {
  $data = [
    'files' => ['https://' . $url],
  ];

  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone . '/purge_cache';

  return invoke_api($api_url, 'POST', $data);
}

function clear_cache_for_prefix(string $zone, string $prefix) {
  $data = [
    'prefixes' => [$prefix],
  ];

  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone . '/purge_cache';

  return invoke_api($api_url, 'POST', $data);
}

function clear_cache_for_urls(string $zone, array $urls) {
  $data = [
    'files' => $urls,
  ];

  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone . '/purge_cache';

  return invoke_api($api_url, 'POST', $data);
}

function get_page_rules_for_zone(string $zone) {
  $data = [
    'page' => 1,
    'per_page' => 125,
  ];

  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone . '/pagerules';
  $api_url .= '?' . http_build_query($data);

  return invoke_api($api_url);
}

function create_page_rule_for_zone(string $zone, array $rule) {
  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone . '/pagerules';
  return invoke_api($api_url, 'POST', $rule);
}

function update_page_rule_for_zone(string $zone, array $rule) {
  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone . '/pagerules/' . $rule['id'];
  return invoke_api($api_url, 'PATCH', $rule);
}

function delete_page_rule_for_zone(string $zone, string $id) {
  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone . '/pagerules/' . $id;
  return invoke_api($api_url, 'DELETE');
}
