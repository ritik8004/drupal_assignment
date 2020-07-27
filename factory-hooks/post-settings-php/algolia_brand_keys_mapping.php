<?php

/**
 * @file
 * Mapping file for Algola keys for brands sandbox application.
 */

/**
 * Return the Algolia keys for the specified brand.
 *
 * To note: These keys are for the Sanbox(dev) app for each brand.
 * For the test/uat/pprod/live envs, we use the prod Application and we add
 * the settings for those applications on ~/settings/settings-brandcode.php
 * on the server for each brand.
 *
 * @param string $site_code
 *   The site code. Eg: pbk, hm, mc, etc.
 *
 * @return array
 *   The Algolia app keys for the brand.
 */
function get_algolia_sandbox_brand_key_mapping(string $site_code) {
  // @todo Remove the 'default' once when sandbox app keys have been added for
  // all the brands.
  $mapping = [
    'default' => [
      'app_id' => 'testing24192T8KHZ',
      'write_api_key' => '1a3473b08a7e58f0b808fe4266e08187',
      'search_api_key' => '950ad607b0d79914702c82849af9a63f',
    ],
    'pbk' => [
      'app_id' => 'KBYTOTQY6T',
      'write_api_key' => 'bc6a377733b1f8812c094d709580faa6',
      'search_api_key' => '3f0b012a52119eb8e95b7ec359d3e881',
    ],
  ];

  return $mapping[$site_code] ?? $mapping['default'];
}
