<?php

/**
 * List all known Conductor environments keyed by environment machine name.
 */
function alshaya_get_conductor_host_data() {
  return [
    // Mothercare.
    'mc_dev' => [
      'url' => 'https://alshaya-mckw-dev.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'c37e4ed2d937425db29385d08491d53a',
      'hmac_secret' => 'dZWSbz_TyTbyaJoBmIyNcA',
    ],
    'mc_test' => [
      'url' => 'https://alshaya-mckw-test.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'edda8c2a78af42b9af1e42221145fd01',
      'hmac_secret' => 'hTVYIu3SDzLh3BwNI6ZEjw',
    ],
    'mc_uat' => [
      'url' => 'https://alshaya-mckw-uat.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'ec11fb2f54d34b2f9d35ec1d3575b89e',
      'hmac_secret' => 'gpW7PQFKKDU-qPrcIgaYNQ',
    ],
    'mc_pprod' => [
      'url' => 'https://alshaya-mckw-pprod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '676f2059d53d407791472c31783ae32c',
      'hmac_secret' => '-2Ok7ywndwcpsraYIIZ__w',
    ],
    'mc_prod' => [
      'url' => 'https://alshaya-mckw-prod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '3d136846d24040099a7eed6c1f4e80b9',
      'hmac_secret' => 'zUt1psyEWi5xO-glHlH_tw',
    ],
    // H&M.
    'hm_dev' => [
      'url' => 'https://alshaya-hm-dev.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '0a38353bdb364be589fc56ac45084778',
      'hmac_secret' => '8xl_BQ1bDa8jAcYZ8smX-A',
    ],
    'hm_test' => [
      'url' => 'https://alshaya-hm-test.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'b01954179c164c0192676a4d4114f45d',
      'hmac_secret' => 'cEF-LqxkmIF3ousifm5pvA',
    ],
    'hm_uat' => [
      'url' => 'https://alshaya-hm-uat.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'b23acd31fa2543a0986d057194175312',
      'hmac_secret' => 'ADBateHrtvFQSrradB8CNg',
    ],
    'hm_pprod' => [
      'url' => 'https://alshaya-hm-pprod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'd57a1fdc153946afbc3b4b821f2029e1',
      'hmac_secret' => 'c0VSHfPxVXuJM0ioX_4thA',
    ],
    'hm_prod' => [
      'url' => 'https://alshaya-hm-prod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '3844f45d61d7467d9a671f3e1cf4bea8',
      'hmac_secret' => 'ngRQUE3QBP9zL-0-TC9XhQ',
    ],
  ];
}

