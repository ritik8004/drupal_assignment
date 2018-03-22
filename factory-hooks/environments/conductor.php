<?php

/**
 * List all known Conductor environments keyed by environment machine name.
 */
function alshaya_get_conductor_host_data() {
  return [
    // Mothercare KW.
    'mc_v2' => [
      'url' => 'https://canary.us-east-1.dev.acm.acquia.io/',
      'hmac_id' => '2d045a6b604c4f9b8493ffcff4733907',
      'hmac_secret' => 'MzMwNzJlYzYtYWMzYy00',
    ],
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
    // Mothercare SA.
    'mcsa_dev' => [
      'url' => 'https://alshaya-mcksa-dev.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '9575450fa913452ba46526463ad56edb',
      'hmac_secret' => '5pUmey7AhYgFhowkWpzEHA',
    ],
    'mcsa_test' => [
      'url' => 'https://alshaya-mcksa-test.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'b4d60c5665624a21b1d03f75060f52cb',
      'hmac_secret' => 'HMWH2Yc8AlWujYegPP96jA',
    ],
    'mcsa_uat' => [
      'url' => 'https://alshaya-mcksa-uat.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '6686b9362f9c48789c08782dc0f85b59',
      'hmac_secret' => 'QIMU6nVtVUVJ3NtSErE5PA',
    ],
    'mcsa_pprod' => [
      'url' => 'https://alshaya-mcksa-pprod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '08bf3c91aeba4b18b78354b38e0ef566',
      'hmac_secret' => 'FY6YF9sOQzj2sTBS9hWFKA',
    ],
    'mcsa_prod' => [
      'url' => 'https://alshaya-mcksa-prod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'd6b5bf8e0ea74347aaeb10dd5548c827',
      'hmac_secret' => 'nSAHBiZvuTiiJa-HC9xdnw',
    ],
    // Mothercare UAE.
    'mcae_test' => [
      'url' => 'https://alshaya-mcae-test.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'e72aa471bdb24cb481bf94c639ad2d59',
      'hmac_secret' => 'KhfeGmclXcqR87TQb2yQVg',
    ],
    // H&M KW.
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
    // H&M SA.
    'hmsa_dev' => [
      'url' => 'https://alshaya-mcksa-uat.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '6686b9362f9c48789c08782dc0f85b59',
      'hmac_secret' => 'QIMU6nVtVUVJ3NtSErE5PA',
    ],
    'hmsa_test' => [
      'url' => 'https://alshaya-hmsa-test.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '3fdf3a81b66e416da5d2b53448a3931e',
      'hmac_secret' => 'kfv0JI-8DcAoFU2JobkymQ',
    ],
    // H&M AE.
    'hmae_test' => [
      'url' => 'https://alshaya-hmae-test.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '932982860b7b4ec18e1b0d5979006fdd',
      'hmac_secret' => 'I7orNPTFOrHdpRAku4AHbQ',
    ],
  ];
}
