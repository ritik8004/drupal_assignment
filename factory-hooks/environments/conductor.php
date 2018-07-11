<?php

/**
 * List all known Conductor environments keyed by environment machine name.
 */
function alshaya_get_conductor_host_data() {
  return [
    // V2 Sandbox.
    'mc_v2' => [
      'url' => 'https://davylamp.us-east-1.dev.acm.acquia.io/',
      'hmac_id' => '2d045a6b604c4f9b8493ffcff4733907',
      'hmac_secret' => 'MzMwNzJlYzYtYWMzYy00',
    ],
    // Mothercare KW.
    'mckw_dev' => [
      'url' => 'https://alshaya-mckw-dev.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'c37e4ed2d937425db29385d08491d53a',
      'hmac_secret' => 'dZWSbz_TyTbyaJoBmIyNcA',
    ],
    'mckw_test' => [
      'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'a4288d2bc3a14db08f790ce7842cb6b1',
      'hmac_secret' => 'NTA1NTcxM2UtMmY4Mi00',
      'api_version' => 'v2',
    ],
    'mckw_uat' => [
      'url' => 'https://alshaya-mckw-uat.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'ec11fb2f54d34b2f9d35ec1d3575b89e',
      'hmac_secret' => 'gpW7PQFKKDU-qPrcIgaYNQ',
    ],
    'mckw_pprod' => [
      'url' => 'https://alshaya-mckw-pprod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '676f2059d53d407791472c31783ae32c',
      'hmac_secret' => '-2Ok7ywndwcpsraYIIZ__w',
    ],
    'mckw_prod' => [
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
      'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '57ec2ca3b0d54828a264ab529b7fab39',
      'hmac_secret' => 'M2I1YjMwZjctYzgzNS00',
      'api_version' => 'v2',
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
      'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '66764a4f8f9146aaab1352fb66897fad',
      'hmac_secret' => 'YWY2ZmY0MjItZmQ2OS00',
      'api_version' => 'v2',
    ],
    'mcae_uat' => [
      'url' => 'https://alshaya-mcae-uat.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'c47e33167b1d4031a18acbf8c9fb9aa8',
      'hmac_secret' => 'QK1RFjIn1Subjk7m-1A2hw',
    ],
    'mcae_prod' => [
      'url' => 'https://alshaya-mcae-prod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'f755609b5006476e85acf942f54da3a0',
      'hmac_secret' => 'GOvi4TncWFS-kqU9H6p4vg',
    ],
    // H&M KW.
    'hmkw_dev' => [
      'url' => 'https://alshaya-hm-dev.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '0a38353bdb364be589fc56ac45084778',
      'hmac_secret' => '8xl_BQ1bDa8jAcYZ8smX-A',
    ],
    'hmkw_test' => [
      'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'deb9efe4753344059090b12fc10d387e',
      'hmac_secret' => 'NWE3ZDg1OGYtNmEwYi00',
      'api_version' => 'v2',
    ],
    'hmkw_uat' => [
      'url' => 'https://alshaya-hm-uat.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'b23acd31fa2543a0986d057194175312',
      'hmac_secret' => 'ADBateHrtvFQSrradB8CNg',
    ],
    'hmkw_pprod' => [
      'url' => 'https://alshaya-hm-pprod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'd57a1fdc153946afbc3b4b821f2029e1',
      'hmac_secret' => 'c0VSHfPxVXuJM0ioX_4thA',
    ],
    'hmkw_prod' => [
      'url' => 'https://alshaya-hm-prod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '3844f45d61d7467d9a671f3e1cf4bea8',
      'hmac_secret' => 'ngRQUE3QBP9zL-0-TC9XhQ',
    ],
    // H&M SA.
    'hmsa_test' => [
      'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'd6e8ad54d1b74a9d8153028b42da4e38',
      'hmac_secret' => 'NDNkNDYzOGQtZjEyZC00',
      'api_version' => 'v2',
    ],
    'hmsa_uat' => [
      'url' => 'https://alshaya-hmsa-uat.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'c0c16680158b4caa91a0756e39f98d2f',
      'hmac_secret' => 'HmPdJJdF_UAqNlWYiWsmdw',
    ],
    'hmsa_prod' => [
      'url' => 'https://alshaya-hmsa-prod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '046a4e9c6ef8467da0e29ca8f3b1793f',
      'hmac_secret' => 'hGeJbwVRoDK-H336mE3BFg',
    ],
    // H&M AE.
    'hmae_test' => [
      'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '48f2b6197df649f6bf5437312319ab5b',
      'hmac_secret' => 'ZDI1OTgyNDEtNzlkMS00',
      'api_version' => 'v2',
    ],
    'hmae_uat' => [
      'url' => 'https://alshaya-hmae-uat.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '01d9fafe1f5f4fc6b3d53e3badc61915',
      'hmac_secret' => 'iO1OtAiXLrxbDzLVEEJSrg',
    ],
    'hmae_prod' => [
      'url' => 'https://alshaya-hmae-prod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '7640a5cfcaef4efc815230419a0c5b06',
      'hmac_secret' => 'wOSnsIw9eRfSpkjOSaXTAw',
    ],
    // Pottery Barn AE.
    'pbae_test' => [
      'url' => 'https://alshaya-pbae-test.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'cbf73a1b03eb43369149662f41980491',
      'hmac_secret' => '_tcQBAc4nXGPNsSH1AAARw',
    ],
    // BathBodyWorks KW.
    'bbwkw_test' => [
      'url' => 'https://alshaya-bbwkw-test.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '4b7670dccafa42b18084c613863aa486',
      'hmac_secret' => 'qeDLTRfJKqqiyE0oOTTS9Q',
    ],
    'bbwkw_uat' => [
      'url' => 'https://alshaya-bbwkw-uat.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '73da80ec6fc04d179a3380ae12b16047',
      'hmac_secret' => 'XBnwM9YaG8HKXXUkYmG6bA',
    ],
    'bbwkw_prod' => [
      'url' => 'https://alshaya-bbwkw-prod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'd8a71262da2740b6b9f56638f4a7a3a8',
      'hmac_secret' => '3oj9IfqBOCauQSH6Ig86og',
    ],
    // BathBodyWorks SA.
    'bbwsa_test' => [
      'url' => 'https://alshaya-bbwsa-test.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'a07fe12b7a28450682a893de129dcf62',
      'hmac_secret' => 'HouU54QMztLm8rbTIPUHsA',
    ],
    'bbwsa_uat' => [
      'url' => 'https://alshaya-bbwsa-uat.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '4fb55a1e29254823b3a8236f8a454f3a',
      'hmac_secret' => 'bMZJXWBEYxO15bjrgg1Gmg',
    ],
    'bbwsa_prod' => [
      'url' => 'https://alshaya-bbwsa-prod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '4484e41f3b7c4783a41d9a0d36ad858e',
      'hmac_secret' => 'gHyLRfMO86zarwkP_nG1Hw',
    ],
    // BathBodyWorks AE.
    'bbwae_test' => [
      'url' => 'https://alshaya-bbwae-test.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '2c9c003acdf34ae29fb2c87a7401cf0a',
      'hmac_secret' => '8UUzR1z6OcFbnjhtl5U8Vg',
    ],
    'bbwae_uat' => [
      'url' => 'https://alshaya-bbwae-uat.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '358452cd036a4c2a9a782eeb1ee8fb25',
      'hmac_secret' => 'm9DwvS0XwtPVpeiNLNk6hQ',
    ],
    'bbwae_prod' => [
      'url' => 'https://alshaya-bbwae-prod.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '0272644d5be84efb9dd246478367e947',
      'hmac_secret' => 'a0EQYgy8zDRm6aNW7FyPrQ',
    ],
    // VictoriaSecret KW.
    'vskw_test' => [
      'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'd00662613f144789831ebfe3d5885e37',
      'hmac_secret' => 'ZjI0NzBhNGUtMjM2Zi00',
      'api_version' => 'v2',
    ],
    'vskw_uat' => [
      'url' => 'https://alshaya-vskw-uat.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'c1827b63783d4ea698d06c32d0ab9095',
      'hmac_secret' => 'Of6wIihr4XqR0zKIncywiw',
    ],
    // VictoriaSecret SA.
    'vssa_test' => [
      'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'b88bad1f106b439ea3a156bd2085e178',
      'hmac_secret' => 'Yjc1YTRjNTAtNDNkNi00',
      'api_version' => 'v2',
    ],
    'vssa_uat' => [
      'url' => 'https://alshaya-vssa-uat.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '399c5db05806456a9843cf604d979b8e',
      'hmac_secret' => 'vj9fxpaBVCSogIzZl7QumQ',
    ],
    // VictoriaSecret AE.
    'vsae_test' => [
      'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => 'c938cdcc017b44bfb7bc736a0ab8a150',
      'hmac_secret' => 'MjM5Y2Q0OTMtMjczNy00',
      'api_version' => 'v2',
    ],
    'vsae_uat' => [
      'url' => 'https://alshaya-vsae-uat.eu-west-1.prod.acm.acquia.io/',
      'hmac_id' => '3ac23a0b214f45a1a756db7092d315dd',
      'hmac_secret' => 'RpbFWVOiVjltkXrTbmv2Ng',
    ],
  ];
}
