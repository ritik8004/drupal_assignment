<?php

/**
 * List all known Conductor environments keyed by environment machine name.
 */
function alshaya_get_conductor_host_data() {
  return [
    // Mothercare.
    'mc_dev' => [
      'url' => 'https://alshaya-dev.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'c37e4ed2d937425db29385d08491d53a',
      'hmac_secret' => 'dZWSbz_TyTbyaJoBmIyNcA',
    ],
    'mc_test' => [
      'url' => 'https://alshaya-test.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'edda8c2a78af42b9af1e42221145fd01',
      'hmac_secret' => 'hTVYIu3SDzLh3BwNI6ZEjw',
    ],
    'mc_uat' => [
      'url' => 'https://alshaya-uat.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'ec11fb2f54d34b2f9d35ec1d3575b89e',
      'hmac_secret' => 'gpW7PQFKKDU-qPrcIgaYNQ',
    ],
    'mc_pprod' => [
      'url' => 'https://alshaya-pprod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '676f2059d53d407791472c31783ae32c',
      'hmac_secret' => '-2Ok7ywndwcpsraYIIZ__w',
    ],
    'mc_prod' => [
      'url' => 'https://alshaya-prod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '3d136846d24040099a7eed6c1f4e80b9',
      'hmac_secret' => 'zUt1psyEWi5xO-glHlH_tw',
    ],
    // H&M.
    'hm_dev' => [
      'url' => 'https://alshaya-hm-dev.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '139fbcb466984b39aea5fd200984a2af',
      'hmac_secret' => 'oMSt6AXgn3TqlMVj5D8A3Q',
    ],
    'hm_test' => [
      'url' => 'https://alshaya-hm-test.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '0c1158d278c24ff586792de9bc01eaa7',
      'hmac_secret' => 'NMGIo_W2s4VA66_6UKM2pQ',
    ],
    'hm_uat' => [
      'url' => 'https://alshaya-hm-uat.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '9a1aa07089684d5ea67922ac932a6cdc',
      'hmac_secret' => 'utbbks1_nf1MFAtszCcCTw',
    ],
    'hm_pprod' => [
      'url' => 'https://alshaya-hm-pprod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'beada22aefef4538a7952691031b1f1d',
      'hmac_secret' => 'GzIsxmPOnDnqcado9ZQg8w',
    ],
    'hm_prod' => [
      'url' => 'https://alshaya-hm-prod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'f952574229334fd895b8ad5ace602414',
      'hmac_secret' => 'EqiVIs70Y_zLj6wSMD4c0g',
    ],
  ];
}

