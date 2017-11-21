<?php

/**
 * List all known Conductor environments keyed by environment machine name.
 */
function alshaya_get_conductor_host_data() {
  return [
    // Mothercare.
    'mc_dev' => [
      'url' => 'https://alshaya-dev.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '139fbcb466984b39aea5fd200984a2af',
      'hmac_secret' => 'oMSt6AXgn3TqlMVj5D8A3Q',
    ],
    // H&M.
    'hm_dev' => [
        'url' => 'https://alshaya-hm-dev.eu-west-1.prod.acm.acquia.io/',
        'hmac_id' => '139fbcb466984b39aea5fd200984a2af',
        'hmac_secret' => 'oMSt6AXgn3TqlMVj5D8A3Q',
    ],
  ];
}

