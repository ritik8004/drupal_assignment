<?php
// @codingStandardsIgnoreFile

/**
 * List all known Magento environments keyed by environment machine name.
 */

global $magentos;

$magentos = [
  // Mothercare.
  'mc_qa' => [
    'url' => 'https://qa-h47ppbq-z3gmkbwmwrl4g.eu.magentosite.cloud',
    'magento_secrets' => [
      'consumer_key' => '0dx3ftegdr4us9aklfhcr66nu43l75ob',
      'consumer_secret' => 'dtr2rqe8cnbx0rt6npv5pilukkcrkwt7',
      'access_token' => '5um6y5nxl3oqms9qw0jai36qkryrrocg',
      'access_token_secret' => '4cfruica5gbgdn2eq269ndl5rccubslc',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 3],
    ],
    'sa' => [
      'store_id' => ['en' => 5, 'ar' => 4],
    ],
    'ae' => [
      'store_id' => ['en' => 7, 'ar' => 6],
    ],
    'eg' => [
      'store_id' => ['en' => 9, 'ar' => 8],
    ],
    'bh' => [
      'store_id' => ['en' => 11, 'ar' => 10],
    ],
    'qa' => [
      'store_id' => ['en' => 13, 'ar' => 12],
    ],
  ],
  'mc_upgrade' => [
    'url' => 'http://magento-upgrade-kb5pcqa-z3gmkbwmwrl4g.eu.magentosite.cloud',
    'magento_secrets' => [
      'consumer_key' => '0dx3ftegdr4us9aklfhcr66nu43l75ob',
      'consumer_secret' => 'dtr2rqe8cnbx0rt6npv5pilukkcrkwt7',
      'access_token' => '5um6y5nxl3oqms9qw0jai36qkryrrocg',
      'access_token_secret' => '4cfruica5gbgdn2eq269ndl5rccubslc',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 3],
    ],
    'sa' => [
      'store_id' => ['en' => 5, 'ar' => 4],
    ],
    'ae' => [
      'store_id' => ['en' => 7, 'ar' => 6],
    ],
  ],
  'mc_sit' => [
    'url' => 'https://mc-sit.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => '0dx3ftegdr4us9aklfhcr66nu43l75ob',
      'consumer_secret' => 'dtr2rqe8cnbx0rt6npv5pilukkcrkwt7',
      'access_token' => '5um6y5nxl3oqms9qw0jai36qkryrrocg',
      'access_token_secret' => '4cfruica5gbgdn2eq269ndl5rccubslc',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 3],
    ],
    'sa' => [
      'store_id' => ['en' => 5, 'ar' => 4],
    ],
    'ae' => [
      'store_id' => ['en' => 7, 'ar' => 6],
    ],
  ],
  'mc_training' => [
    'url' => 'https://mc-training.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => '0dx3ftegdr4us9aklfhcr66nu43l75ob',
      'consumer_secret' => 'dtr2rqe8cnbx0rt6npv5pilukkcrkwt7',
      'access_token' => '5um6y5nxl3oqms9qw0jai36qkryrrocg',
      'access_token_secret' => '4cfruica5gbgdn2eq269ndl5rccubslc',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 3],
    ],
    'sa' => [
      'store_id' => ['en' => 5, 'ar' => 4],
    ],
    'ae' => [
      'store_id' => ['en' => 7, 'ar' => 6],
    ],
  ],
  'mc_uat' => [
    'url' => 'https://mcmena-uat2.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => 'up2nmvc7y2c9k7o1kkk3iruywidhges4',
      'consumer_secret' => 'l3qcpfpjvb8hiy5dnbu4ln63ahr16haj',
      'access_token' => 'ic28d0o0od00yrbd38kln1hrdvnx8bb5',
      'access_token_secret' => 'kuec3ssvt14wwo5ojhbmg78xqq1vswgg',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 4],
    ],
    'sa' => [
      'store_id' => ['en' => 7, 'ar' => 13],
    ],
    'ae' => [
      'store_id' => ['en' => 19, 'ar' => 22],
    ],
    'eg' => [
      'store_id' => ['en' => 27, 'ar' => 24],
    ],
    'bh' => [
      'store_id' => ['en' => 33, 'ar' => 30],
    ],
    'qa' => [
      'store_id' => ['en' => 39, 'ar' => 36],
    ],
  ],
  'mc_oms_uat' => [
    'url' => 'https://mc-uat2.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => '0dx3ftegdr4us9aklfhcr66nu43l75ob',
      'consumer_secret' => 'dtr2rqe8cnbx0rt6npv5pilukkcrkwt7',
      'access_token' => '5um6y5nxl3oqms9qw0jai36qkryrrocg',
      'access_token_secret' => '4cfruica5gbgdn2eq269ndl5rccubslc',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 3],
    ],
    'sa' => [
      'store_id' => ['en' => 5, 'ar' => 4],
    ],
    'ae' => [
      'store_id' => ['en' => 7, 'ar' => 6],
    ],
  ],
  'mc_oms_pprod' => [
    'url' => 'https://mc-pprod.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => '0dx3ftegdr4us9aklfhcr66nu43l75ob',
      'consumer_secret' => 'dtr2rqe8cnbx0rt6npv5pilukkcrkwt7',
      'access_token' => '5um6y5nxl3oqms9qw0jai36qkryrrocg',
      'access_token_secret' => '4cfruica5gbgdn2eq269ndl5rccubslc',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 3],
    ],
    'sa' => [
      'store_id' => ['en' => 5, 'ar' => 4],
    ],
    'ae' => [
      'store_id' => ['en' => 7, 'ar' => 6],
    ],
    'eg' => [
      'store_id' => ['en' => 9, 'ar' => 8],
    ],
    'bh' => [
      'store_id' => ['en' => 11, 'ar' => 10],
    ],
    'qa' => [
      'store_id' => ['en' => 13, 'ar' => 12],
    ],
  ],
  'mc_prod' => [
    'url' => 'https://mcmena.store.alshaya.com',
    'magento_secrets' => [],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 4],
    ],
    'sa' => [
      'store_id' => ['en' => 7, 'ar' => 13],
    ],
    'ae' => [
      'store_id' => ['en' => 19, 'ar' => 22],
    ],
    'eg' => [
      'store_id' => ['en' => 27, 'ar' => 24],
    ],
    'bh' => [
      'store_id' => ['en' => 32, 'ar' => 29],
    ],
    'qa' => [
      'store_id' => ['en' => 38, 'ar' => 35],
    ],
  ],
  // H&M.
  'hm_lpn' => [
    'url' => 'https://lpn-new-tbwdxni-zbrr3sobrsb3o.eu.magentosite.cloud',
    'pims_base_url' => 'http://34.249.182.88:3020',
    'magento_secrets' => [
      'consumer_key' => '5ud3vh5cqkc2k3uxyfpkuehi2eik11xg',
      'consumer_secret' => '7krmuncsf3c1rabxqhy5rfnpdvubocmi',
      'access_token' => 'wuxd59ghyt7qwprhd86gs6cd3t1y6cyn',
      'access_token_secret' => 'agpwx3guekmh6843nh5oaxonfjxy00ls',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 2],
    ],
    'sa' => [
      'store_id' => ['en' => 3, 'ar' => 4],
    ],
    'ae' => [
      'store_id' => ['en' => 6, 'ar' => 5],
    ],
    'eg' => [
      'store_id' => ['en' => 8, 'ar' => 7],
    ],
  ],
  'hm_qa' => [
    'url' => 'https://qa-dc3i3ua-zbrr3sobrsb3o.eu.magentosite.cloud',
    'pims_base_url' => 'http://34.249.182.88:3010',
    'magento_secrets' => [
      'consumer_key' => '5ud3vh5cqkc2k3uxyfpkuehi2eik11xg',
      'consumer_secret' => '7krmuncsf3c1rabxqhy5rfnpdvubocmi',
      'access_token' => 'wuxd59ghyt7qwprhd86gs6cd3t1y6cyn',
      'access_token_secret' => 'agpwx3guekmh6843nh5oaxonfjxy00ls',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 2],
    ],
    'sa' => [
      'store_id' => ['en' => 3, 'ar' => 4],
    ],
    'ae' => [
      'store_id' => ['en' => 6, 'ar' => 5],
    ],
    'eg' => [
      'store_id' => ['en' => 8, 'ar' => 7],
    ],
  ],
  'hm_apc' => [
    'url' => 'https://apc-7vc7xgy-zbrr3sobrsb3o.eu.magentosite.cloud',
    'pims_base_url' => 'http://34.249.182.88:3010',
    'magento_secrets' => [
      'consumer_key' => '5ud3vh5cqkc2k3uxyfpkuehi2eik11xg',
      'consumer_secret' => '7krmuncsf3c1rabxqhy5rfnpdvubocmi',
      'access_token' => 'wuxd59ghyt7qwprhd86gs6cd3t1y6cyn',
      'access_token_secret' => 'agpwx3guekmh6843nh5oaxonfjxy00ls',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 2],
    ],
    'sa' => [
      'store_id' => ['en' => 3, 'ar' => 4],
    ],
    'ae' => [
      'store_id' => ['en' => 6, 'ar' => 5],
    ],
    'eg' => [
      'store_id' => ['en' => 8, 'ar' => 7],
    ],
  ],
  'hm_uat' => [
    'url' => 'https://hm-uat2.store.alshaya.com',
    'pims_base_url' => 'http://34.249.182.88:3030',
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 5],
    ],
    'sa' => [
      'store_id' => ['en' => 8, 'ar' => 14],
    ],
    'ae' => [
      'store_id' => ['en' => 17, 'ar' => 23],
    ],
    'eg' => [
      'store_id' => ['en' => 30, 'ar' => 27],
    ],
    'bh' => [
      'store_id' => ['en' => 35, 'ar' => 32],
    ],
    'qa' => [
      'store_id' => ['en' => 41, 'ar' => 38],
    ],
    'magento_secrets' => [
      'consumer_key' => 'ld4h0pms530qrlwkpn99o52nnmvlha2x',
      'consumer_secret' => 'w9fadch6mnekfcwp4c6y46avsubhy0pi',
      'access_token' => 'ncevhsnmxu35fln4fq3c009lo09frfgm',
      'access_token_secret' => 'dm4h99kjd387rv29ivthpbatx7dn5g1b',
    ],
  ],
  'hm_prod' => [
    'url' => 'https://hm.store.alshaya.com',
    'pims_base_url' => 'http://34.248.5.79:2080',
    'magento_secrets' => [],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 5],
    ],
    'sa' => [
      'store_id' => ['en' => 8, 'ar' => 14],
    ],
    'ae' => [
      'store_id' => ['en' => 17, 'ar' => 23],
    ],
    'eg' => [
      'store_id' => ['en' => 30, 'ar' => 27],
    ],
    'bh' => [
      'store_id' => ['en' => 36, 'ar' => 33],
    ],
    'qa' => [
      'store_id' => ['en' => 42, 'ar' => 39],
    ],
  ],
  'hm_mapp' => [
    'url' => 'https://mapp-hjuuq7a-zbrr3sobrsb3o.eu.magentosite.cloud',
    'magento_secrets' => [
      'consumer_key' => '5ud3vh5cqkc2k3uxyfpkuehi2eik11xg',
      'consumer_secret' => '7krmuncsf3c1rabxqhy5rfnpdvubocmi',
      'access_token' => 'wuxd59ghyt7qwprhd86gs6cd3t1y6cyn',
      'access_token_secret' => 'agpwx3guekmh6843nh5oaxonfjxy00ls',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 2],
    ],
    'sa' => [
      'store_id' => ['en' => 3, 'ar' => 4],
    ],
    'ae' => [
      'store_id' => ['en' => 6, 'ar' => 5],
    ],
    'eg' => [
      'store_id' => ['en' => 8, 'ar' => 7],
    ],
  ],
  // Pottery Barn.
  'pb_qa' => [
    'url' => 'https://integration-5ojmyuq-rfuu4sicyisyw.eu.magentosite.cloud',
    'magento_secrets' => [
      'consumer_key' => 'auf96nt6b1loar4yc2qm55pluqd5sgrn',
      'consumer_secret' => 'i1lhnoafn6a1ggjou2juj366cpcnnhel',
      'access_token' => 's4rfv318v1gxmrnq8mjdn01uhejd8760',
      'access_token_secret' => '4x5otnn378pjr1v3acnmoe934niwjlw7',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 6],
    ],
    'sa' => [
      'store_id' => ['en' => 2, 'ar' => 3],
    ],
    'ae' => [
      'store_id' => ['en' => 4, 'ar' => 5],
    ],
  ],
  'pb_uat' => [
    'url' => 'https://pb-uat2.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => 'npg1gjry1gvxoptojwdw4j3qjgegfm8l',
      'consumer_secret' => 'v70lrfp9v21t7cr81j56ejy3pme7ckl4',
      'access_token' => 't6h4uetr4aajx4ra6ordxip4r0xsvu5i',
      'access_token_secret' => 'lvcfb0e868q99jv6kb55r3513oxxs82p',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 18],
    ],
    'sa' => [
      'store_id' => ['en' => 6, 'ar' => 9],
    ],
    'ae' => [
      'store_id' => ['en' => 12, 'ar' => 15],
    ],
  ],
  'pb_prod' => [
    'url' => 'https://pb.store.alshaya.com',
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 18],
    ],
    'sa' => [
      'store_id' => ['en' => 6, 'ar' => 9],
    ],
    'ae' => [
      'store_id' => ['en' => 12, 'ar' => 15],
    ],
  ],
  // Pottery Barn Kids.
  'pbk_qa' => [
    'url' => 'https://integration-5ojmyuq-pyudokdb2kumk.eu-3.magentosite.cloud',
    'magento_secrets' => [
      'consumer_key' => '8spkmhz5rst8etwcc7w5qg92dabku51w',
      'consumer_secret' => 'jp5ml3y30lqqxxfxnqj19vcsf5apywkh',
      'access_token' => '9px4kesgmj6vnjkujhnk8nyxa7m667i4',
      'access_token_secret' => 'b8dkngbd1uami6kep2a2yafirkdrr6jp',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 6],
    ],
    'sa' => [
      'store_id' => ['en' => 2, 'ar' => 3],
    ],
    'ae' => [
      'store_id' => ['en' => 4, 'ar' => 5],
    ],
  ],
  'pbk_uat' => [
    'url' => 'https://pbk-uat.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => 'ywgq9ebmjec2ctq9q9wl2ygocab9tzwz',
      'consumer_secret' => 'ml3j38568na78y1os6byg94pb1aq96dv',
      'access_token' => 'ye5cargyshiyfu63nmvmgy7auqeupc0r',
      'access_token_secret' => '460ockhivjys2m8ngf1ynd17xs7jc872',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 16],
    ],
    'sa' => [
      'store_id' => ['en' => 4, 'ar' => 7],
    ],
    'ae' => [
      'store_id' => ['en' => 10, 'ar' => 13],
    ],
  ],
  'pbk_prod' => [
    'url' => 'https://pbk.store.alshaya.com',
    'magento_secrets' => [],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 15],
    ],
    'sa' => [
      'store_id' => ['en' => 3, 'ar' => 6],
    ],
    'ae' => [
      'store_id' => ['en' => 9, 'ar' => 12],
    ],
    'eg' => [
      'store_id' => ['en' => 21, 'ar' => 18],
    ],
    'bh' => [
      'store_id' => ['en' => 27, 'ar' => 24],
    ],
    'qa' => [
      'store_id' => ['en' => 33, 'ar' => 30],
    ],
  ],
  // Victoria Secret.
  'vs_qa' => [
    'url' => 'https://integration-5ojmyuq-kpwgmbven7d7y.eu-3.magentosite.cloud',
    'magento_secrets' => [
      'consumer_key' => 'ykhx46sjfr2wp7682ph7hvwivt9gf92p',
      'consumer_secret' => 'ws58leuqt6746q0cwlh6m9hplh84dkm9',
      'access_token' => 'aissfukfwphot1i3d11na24jcyqb2od8',
      'access_token_secret' => 'lvc4hhwak1nei7bul20tg3umjmed0nh0',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 6],
    ],
    'sa' => [
      'store_id' => ['en' => 2, 'ar' => 3],
    ],
    'ae' => [
      'store_id' => ['en' => 4, 'ar' => 5],
    ],
  ],
  'vs_uat' => [
    'url' => 'https://vs-uat2.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => 'x7perq2khr9t52qeg2rv6ly5uakgv5lw',
      'consumer_secret' => 'l65u29tw675glfm3rmfps1gliqlwr6m1',
      'access_token' => 'km7olqq1wx37sveeepwcmpwvem2t6jar',
      'access_token_secret' => 'si8olbegg9i4y3bywk2gnb4o2vpommiw',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 18],
    ],
    'sa' => [
      'store_id' => ['en' => 6, 'ar' => 9],
    ],
    'ae' => [
      'store_id' => ['en' => 12, 'ar' => 15],
    ],
    'eg' => [
      'store_id' => ['en' => 25, 'ar' => 22],
    ],
    'bh' => [
      'store_id' => ['en' => 31, 'ar' => 28],
    ],
    'qa' => [
      'store_id' => ['en' => 37, 'ar' => 34],
    ],
  ],
  'vs_prod' => [
    'url' => 'https://vs.store.alshaya.com',
    'magento_secrets' => [],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 18],
    ],
    'sa' => [
      'store_id' => ['en' => 6, 'ar' => 9],
    ],
    'ae' => [
      'store_id' => ['en' => 12, 'ar' => 15],
    ],
    'eg' => [
      'store_id' => ['en' => 25, 'ar' => 22],
    ],
    'bh' => [
      'store_id' => ['en' => 29, 'ar' => 26],
    ],
    'qa' => [
      'store_id' => ['en' => 35, 'ar' => 32],
    ],
  ],
  // BathBodyWorks.
  'bbw_qa' => [
    'url' => 'https://integration-5ojmyuq-bbk3lvknero4c.eu-3.magentosite.cloud',
    'magento_secrets' => [
      'consumer_key' => 'evay531jglmeinqwsfg6sis05smhrucy',
      'consumer_secret' => '09p2et416nf7dj44iudswsf7vf4tl682',
      'access_token' => 'oiy66ouu8mpeorbu47cke2xsdhfku3jg',
      'access_token_secret' => '2ovq0ccau2cd60a2xfterr2y2fi711w4',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 2],
    ],
    'sa' => [
      'store_id' => ['en' => 3, 'ar' => 4],
    ],
    'ae' => [
      'store_id' => ['en' => 5, 'ar' => 6],
    ],
    'eg' => [
      'store_id' => ['en' => 8, 'ar' => 7],
    ],
    'bh' => [
      'store_id' => ['en' => 10, 'ar' => 9],
    ],
    'qa' => [
      'store_id' => ['en' => 12, 'ar' => 11],
    ],
  ],
  'bbw_apc' => [
    'url' => 'https://apc-7vc7xgy-bbk3lvknero4c.eu-3.magentosite.cloud',
    'magento_secrets' => [
      'consumer_key' => 'evay531jglmeinqwsfg6sis05smhrucy',
      'consumer_secret' => '09p2et416nf7dj44iudswsf7vf4tl682',
      'access_token' => 'oiy66ouu8mpeorbu47cke2xsdhfku3jg',
      'access_token_secret' => '2ovq0ccau2cd60a2xfterr2y2fi711w4',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 2],
    ],
    'sa' => [
      'store_id' => ['en' => 3, 'ar' => 4],
    ],
    'ae' => [
      'store_id' => ['en' => 5, 'ar' => 6],
    ],
    'eg' => [
      'store_id' => ['en' => 8, 'ar' => 7],
    ],
    'bh' => [
      'store_id' => ['en' => 10, 'ar' => 9],
    ],
    'qa' => [
      'store_id' => ['en' => 12, 'ar' => 11],
    ],
  ],
  'bbw_uat' => [
    'url' => 'https://staging-bbw2.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => 'mue8te8h9fpimh3ssmk2gqg3p37fkrbk',
      'consumer_secret' => '3n11u3dqiv3dnwr6lnhrf0gm3hso8932',
      'access_token' => 'noi823gl5hgclkbf7zau2qtyo6td0nnh',
      'access_token_secret' => 'h8yhtn87yx83wslo9regfguxynx646b2',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 18],
    ],
    'sa' => [
      'store_id' => ['en' => 6, 'ar' => 9],
    ],
    'ae' => [
      'store_id' => ['en' => 12, 'ar' => 15],
    ],
    'eg' => [
      'store_id' => ['en' => 25, 'ar' => 22],
    ],
    'bh' => [
      'store_id' => ['en' => 30, 'ar' => 27],
    ],
    'qa' => [
      'store_id' => ['en' => 36, 'ar' => 33],
    ],
  ],
  'bbw_prod' => [
    'url' => 'https://bbw.store.alshaya.com',
    'magento_secrets' => [],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 18],
    ],
    'sa' => [
      'store_id' => ['en' => 6, 'ar' => 9],
    ],
    'ae' => [
      'store_id' => ['en' => 12, 'ar' => 15],
    ],
    'eg' => [
      'store_id' => ['en' => 25, 'ar' => 22],
    ],
    'bh' => [
      'store_id' => ['en' => 32, 'ar' => 29],
    ],
    'qa' => [
      'store_id' => ['en' => 38, 'ar' => 35],
    ],
  ],
  // Foot Locker.
  'fl_qa' => [
    'url' => 'https://integration-5ojmyuq-z2fi6fmoo7n4a.eu-3.magentosite.cloud',
    'magento_secrets' => [
      'consumer_key' => 'f0er5posi7oc9e2p96195mb20e2hh05q',
      'consumer_secret' => 'f0h2pwyren0ooppm6ix50705l5iouekj',
      'access_token' => 'tkkes5bu2l9qn8y3hbaex7x6xbcsfvxr',
      'access_token_secret' => 'p8e71whrsnx4pdim61wyush1qp5tvqqs',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 6],
    ],
    'sa' => [
      'store_id' => ['en' => 2, 'ar' => 3],
    ],
    'ae' => [
      'store_id' => ['en' => 4, 'ar' => 5],
    ],
    'eg' => [
      'store_id' => ['en' => 8, 'ar' => 7],
    ],
    'bh' => [
      'store_id' => ['en' => 10, 'ar' => 9],
    ],
    'qa' => [
      'store_id' => ['en' => 12, 'ar' => 11],
    ],
  ],
  'fl_apc' => [
    'url' => 'https://apc-7vc7xgy-z2fi6fmoo7n4a.eu-3.magentosite.cloud',
    'magento_secrets' => [
      'consumer_key' => 'f0er5posi7oc9e2p96195mb20e2hh05q',
      'consumer_secret' => 'f0h2pwyren0ooppm6ix50705l5iouekj',
      'access_token' => 'tkkes5bu2l9qn8y3hbaex7x6xbcsfvxr',
      'access_token_secret' => 'p8e71whrsnx4pdim61wyush1qp5tvqqs',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 6],
    ],
    'sa' => [
      'store_id' => ['en' => 2, 'ar' => 3],
    ],
    'ae' => [
      'store_id' => ['en' => 4, 'ar' => 5],
    ],
    'eg' => [
      'store_id' => ['en' => 8, 'ar' => 7],
    ],
    'bh' => [
      'store_id' => ['en' => 10, 'ar' => 9],
    ],
    'qa' => [
      'store_id' => ['en' => 12, 'ar' => 11],
    ],
  ],
  'fl_uat' => [
    'url' => 'https://fl-uat2.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => '7odnwfhqxgemmfg8cmmo7iqipbb74hav',
      'consumer_secret' => 'mqf2sk1s87h9hmqmeyu2wdtetj73hyqf',
      'access_token' => '6r64msbxkga3jrf5bb8nl0q9q3jya5nh',
      'access_token_secret' => 'qmkvb8ekhknu2uiy3cvclarxdcfcpvvb',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 19],
    ],
    'sa' => [
      'store_id' => ['en' => 7, 'ar' => 10],
    ],
    'ae' => [
      'store_id' => ['en' => 13, 'ar' => 16],
    ],
    'eg' => [
      'store_id' => ['en' => 25, 'ar' => 22],
    ],
    'bh' => [
      'store_id' => ['en' => 31, 'ar' => 28],
    ],
    'qa' => [
      'store_id' => ['en' => 37, 'ar' => 34],
    ],
  ],
  'fl_prod' => [
    'url' => 'https://fl.store.alshaya.com',
    'magento_secrets' => [],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 19],
    ],
    'sa' => [
      'store_id' => ['en' => 7, 'ar' => 10],
    ],
    'ae' => [
      'store_id' => ['en' => 13, 'ar' => 16],
    ],
    'eg' => [
      'store_id' => ['en' => 25, 'ar' => 22],
    ],
    'bh' => [
      'store_id' => ['en' => 33, 'ar' => 30],
    ],
    'qa' => [
      'store_id' => ['en' => 39, 'ar' => 36],
    ],
  ],
  // Westelm.
  'we_sit' => [
    'url' => 'https://wes-sit.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => '4xtw4osc8e7fv45cy187xhhobr6xt59e',
      'consumer_secret' => '3b5nydivhlbwm6v29ekyhv3v8v2s2pl4',
      'access_token' => 'm8cgs2h1dj4zqvx1qy8293aygeu7zcar',
      'access_token_secret' => '4mocc81s1pnejkm57fimdjw49mqcz4rl',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 15],
    ],
    'sa' => [
      'store_id' => ['en' => 3, 'ar' => 6],
    ],
    'ae' => [
      'store_id' => ['en' => 9, 'ar' => 12],
    ],
  ],
  'we_qa' => [
    'url' => 'https://integration-5ojmyuq-xj72gv64n7kci.eu-3.magentosite.cloud',
    'magento_secrets' => [
      'consumer_key' => '2dobu86iimp9x3oc2n0qitc03ypcyud3',
      'consumer_secret' => 'nqnmmvijrx3gmwpag73hbws67sfil94s',
      'access_token' => 'j06xgfwz2p99scbwsc6eiil20l3ser2f',
      'access_token_secret' => '8efr0c8hev5tkas1s5yj555o5in1vf4p',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 6],
    ],
    'sa' => [
      'store_id' => ['en' => 2, 'ar' => 3],
    ],
    'ae' => [
      'store_id' => ['en' => 4, 'ar' => 5],
    ],
    'eg' => [
      'store_id' => ['en' => 8, 'ar' => 7],
    ],
    'bh' => [
      'store_id' => ['en' => 10, 'ar' => 9],
    ],
    'qa' => [
      'store_id' => ['en' => 12, 'ar' => 11],
    ],
  ],
  'we_uat' => [
    'url' => 'https://wes-uat.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => '4xtw4osc8e7fv45cy187xhhobr6xt59e',
      'consumer_secret' => '3b5nydivhlbwm6v29ekyhv3v8v2s2pl4',
      'access_token' => 'm8cgs2h1dj4zqvx1qy8293aygeu7zcar',
      'access_token_secret' => '4mocc81s1pnejkm57fimdjw49mqcz4rl',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 15],
    ],
    'sa' => [
      'store_id' => ['en' => 3, 'ar' => 6],
    ],
    'ae' => [
      'store_id' => ['en' => 9, 'ar' => 12],
    ],
    'eg' => [
      'store_id' => ['en' => 21, 'ar' => 18],
    ],
    'bh' => [
      'store_id' => ['en' => 25, 'ar' => 22],
    ],
    'qa' => [
      'store_id' => ['en' => 31, 'ar' => 28],
    ],
  ],
  'we_oms_uat' => [
    'url' => 'https://wes-uatoms.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => '4xtw4osc8e7fv45cy187xhhobr6xt59e',
      'consumer_secret' => '3b5nydivhlbwm6v29ekyhv3v8v2s2pl4',
      'access_token' => 'm8cgs2h1dj4zqvx1qy8293aygeu7zcar',
      'access_token_secret' => '4mocc81s1pnejkm57fimdjw49mqcz4rl',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 15],
    ],
    'sa' => [
      'store_id' => ['en' => 3, 'ar' => 6],
    ],
    'ae' => [
      'store_id' => ['en' => 9, 'ar' => 12],
    ],
  ],
  'we_pprod' => [
    'url' => 'https://wes-pprod.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => 'nw8u6lhjx5o3kcd2hf96k0z0dzkkogf0',
      'consumer_secret' => 'i3ajcsvtwqad0i1li6c0udeou6rqfh9w',
      'access_token' => 'd3sq5sh6uhwo9gqn3ewfkl08926pro1t',
      'access_token_secret' => '04xwg426blb0b9z5ijuld4vb5nruqtzm',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 14],
    ],
    'sa' => [
      'store_id' => ['en' => 2, 'ar' => 5],
    ],
    'ae' => [
      'store_id' => ['en' => 8, 'ar' => 11],
    ],
  ],
  'we_prod' => [
    'url' => 'https://wes.store.alshaya.com',
    'magento_secrets' => [],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 14],
    ],
    'sa' => [
      'store_id' => ['en' => 2, 'ar' => 5],
    ],
    'ae' => [
      'store_id' => ['en' => 8, 'ar' => 11],
    ],
    'eg' => [
      'store_id' => ['en' => 20, 'ar' => 17],
    ],
    'bh' => [
      'store_id' => ['en' => 26, 'ar' => 23],
    ],
    'qa' => [
      'store_id' => ['en' => 32, 'ar' => 29],
    ],
  ],
  'aeo_qa' => [
    'url' => 'https://integration-5ojmyuq-tw5uijob6hir2.eu-3.magentosite.cloud',
    'magento_secrets' => [
      'consumer_key' => 'yyt9xtlfaaxw55c1h9xbw0mlff8j8ovt',
      'consumer_secret' => 'k63ke42x62x1hssrtxy1e33c5vmyqgwy',
      'access_token' => 'o0kvxvg1hge9j51twpyztl9uxvvq78y2',
      'access_token_secret' => '05m2gsyg0sxrs87aehwghc2ct0ttmtuo',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 6],
    ],
    'sa' => [
      'store_id' => ['en' => 2, 'ar' => 3],
    ],
    'ae' => [
      'store_id' => ['en' => 4, 'ar' => 5],
    ],
    'eg' => [
      'store_id' => ['en' => 8, 'ar' => 7],
    ],
    'bh' => [
      'store_id' => ['en' => 10, 'ar' => 9],
    ],
    'qa' => [
      'store_id' => ['en' => 12, 'ar' => 11],
    ],
  ],
  'aeo_training' => [
    'url' => 'https://aeo-training.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => 'ydzr7xpvfjlzbwvce9tza471n7juuwhe',
      'consumer_secret' => 'tqseptjwftwhrua330kjem7jnurclhbx',
      'access_token' => 'ofsq77eipss5gshgvj931q603ij7eiqw',
      'access_token_secret' => '24phqz567kp2fkt86tocb6exkwcittmq',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 16],
    ],
    'sa' => [
      'store_id' => ['en' => 4, 'ar' => 7],
    ],
    'ae' => [
      'store_id' => ['en' => 10, 'ar' => 13],
    ],
    'eg' => [
      'store_id' => ['en' => 22, 'ar' => 19],
    ],
  ],
  'aeo_uat' => [
    'url' => 'https://aeo-uat.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => 'ydzr7xpvfjlzbwvce9tza471n7juuwhe',
      'consumer_secret' => 'tqseptjwftwhrua330kjem7jnurclhbx',
      'access_token' => 'ofsq77eipss5gshgvj931q603ij7eiqw',
      'access_token_secret' => '24phqz567kp2fkt86tocb6exkwcittmq',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 16],
    ],
    'sa' => [
      'store_id' => ['en' => 4, 'ar' => 7],
    ],
    'ae' => [
      'store_id' => ['en' => 10, 'ar' => 13],
    ],
    'eg' => [
      'store_id' => ['en' => 22, 'ar' => 19],
    ],
    'bh' => [
      'store_id' => ['en' => 26, 'ar' => 23],
    ],
    'qa' => [
      'store_id' => ['en' => 32, 'ar' => 29],
    ],
  ],
  'aeo_pprod' => [
    'url' => 'https://aeo-pprod.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => 'wzxqdk7wkl91r8hjjko0d2vskzta3hdf',
      'consumer_secret' => 'fngmon058tw3yjv7smnohrfa51hl7mv3',
      'access_token' => '5f6jbmpff4l5szyskugiwc6zus2sn8ug',
      'access_token_secret' => '4c3x9c4ycqekddwm1xe4f2z0yh2a38vy',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 16],
    ],
    'sa' => [
      'store_id' => ['en' => 4, 'ar' => 7],
    ],
    'ae' => [
      'store_id' => ['en' => 10, 'ar' => 13],
    ],
    'eg' => [
      'store_id' => ['en' => 22, 'ar' => 19],
    ],
    'bh' => [
      'store_id' => ['en' => 28, 'ar' => 25],
    ],
    'qa' => [
      'store_id' => ['en' => 34, 'ar' => 31],
    ],
  ],
  'aeo_prod' => [
    'url' => 'https://aeo.store.alshaya.com',
    'magento_secrets' => [],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 16],
    ],
    'sa' => [
      'store_id' => ['en' => 4, 'ar' => 7],
    ],
    'ae' => [
      'store_id' => ['en' => 10, 'ar' => 13],
    ],
    'eg' => [
      'store_id' => ['en' => 22, 'ar' => 19],
    ],
    'bh' => [
      'store_id' => ['en' => 28, 'ar' => 25],
    ],
    'qa' => [
      'store_id' => ['en' => 34, 'ar' => 31],
    ],
  ],
  'mu_qa' => [
    'url' => 'https://integration-5ojmyuq-szaftnexsfo4k.eu-3.magentosite.cloud',
    'magento_secrets' => [
      'consumer_key' => 'd8gfbetgjq5ivnqoqas85hdpi1zdplf1',
      'consumer_secret' => 'gaxphnn61qgg5js9dsy2xrwlc70dehn5',
      'access_token' => '214yfy8hauwdqu4b25ci573iefokdp85',
      'access_token_secret' => 'z6jfn1akazvc86cpq5v1ymwi5ikcouhr',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 6],
    ],
    'sa' => [
      'store_id' => ['en' => 2, 'ar' => 3],
    ],
    'ae' => [
      'store_id' => ['en' => 4, 'ar' => 5],
    ],
    'eg' => [
      'store_id' => ['en' => 8, 'ar' => 7],
    ],
  ],
  'mu_uat' => [
    'url' => 'https://muji-uat.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => 'cd2hqmtyqht62jka3w54jm1bdd9zdlhq',
      'consumer_secret' => 'x00pjn155x2aoah9e782rokxdc0v5hc1',
      'access_token' => 'c4vbucn0mw17usii8locm564lmhzq53f',
      'access_token_secret' => 'l6ngd0xnrssi8z918egboaklgobofj69',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 16],
    ],
    'sa' => [
      'store_id' => ['en' => 4, 'ar' => 7],
    ],
    'ae' => [
      'store_id' => ['en' => 10, 'ar' => 13],
    ],
    'eg' => [
      'store_id' => ['en' => 22, 'ar' => 19],
    ],
    'bh' => [
      'store_id' => ['en' => 28, 'ar' => 25],
    ],
    'qa' => [
      'store_id' => ['en' => 34, 'ar' => 31],
    ],
  ],
  'mu_prod' => [
    'url' => 'https://muji.store.alshaya.com',
    'magento_secrets' => [],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 15],
    ],
    'sa' => [
      'store_id' => ['en' => 3, 'ar' => 6],
    ],
    'ae' => [
      'store_id' => ['en' => 9, 'ar' => 12],
    ],
    'eg' => [
      'store_id' => ['en' => 21, 'ar' => 18],
    ],
    'bh' => [
      'store_id' => ['en' => 27, 'ar' => 24],
    ],
    'qa' => [
      'store_id' => ['en' => 33, 'ar' => 30],
    ],
  ],
  'bp_freegift' => [
    'url' => 'https://freegift-jpcewva-gdyoujibngne2.eu-3.magentosite.cloud',
    'magento_secrets' => [
      'consumer_key' => '10t6mj4t46m69exspxelmqna1t3fnz8u',
      'consumer_secret' => 'ozboevrqwoeogwohpeu7hlcvr2hbljkt',
      'access_token' => '31l664sdj4q1p4wu7k0wmzwt1h84hp5w',
      'access_token_secret' => 'p1habct6ztfqtbza0in9o68z464qr5fj',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 6],
    ],
    'sa' => [
      'store_id' => ['en' => 2, 'ar' => 3],
    ],
    'ae' => [
      'store_id' => ['en' => 4, 'ar' => 5],
    ],
    'eg' => [
      'store_id' => ['en' => 8, 'ar' => 7],
    ],
  ],
  'bp_qa' => [
    'url' => 'https://integration-5ojmyuq-gdyoujibngne2.eu-3.magentosite.cloud',
    'magento_secrets' => [
      'consumer_key' => '10t6mj4t46m69exspxelmqna1t3fnz8u',
      'consumer_secret' => 'ozboevrqwoeogwohpeu7hlcvr2hbljkt',
      'access_token' => '31l664sdj4q1p4wu7k0wmzwt1h84hp5w',
      'access_token_secret' => 'p1habct6ztfqtbza0in9o68z464qr5fj',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 6],
    ],
    'sa' => [
      'store_id' => ['en' => 2, 'ar' => 3],
    ],
    'ae' => [
      'store_id' => ['en' => 4, 'ar' => 5],
    ],
    'eg' => [
      'store_id' => ['en' => 8, 'ar' => 7],
    ],
    'bh' => [
      'store_id' => ['en' => 10, 'ar' => 9],
    ],
    'qa' => [
      'store_id' => ['en' => 12, 'ar' => 11],
    ],
  ],
  'bp_uat' => [
    'url' => 'https://boots-uat.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => 'nfjdttxqyqdaj81nilo4sab9wl7kkb0x',
      'consumer_secret' => '5szrlic4mwpoxyjl11mhrxux287lykdc',
      'access_token' => 'qwgohy4l3xnbcz63nhvb5u5soezfizka',
      'access_token_secret' => 'a5seetf8c166nnkwgk7cbunjxv8l9jdu',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 16],
    ],
    'sa' => [
      'store_id' => ['en' => 4, 'ar' => 7],
    ],
    'ae' => [
      'store_id' => ['en' => 10, 'ar' => 13],
    ],
    'eg' => [
      'store_id' => ['en' => 22, 'ar' => 19],
    ],
    'bh' => [
      'store_id' => ['en' => 28, 'ar' => 25],
    ],
    'qa' => [
      'store_id' => ['en' => 34, 'ar' => 31],
    ],
  ],
  'bp_prod' => [
    'url' => 'https://boots.store.alshaya.com',
    'magento_secrets' => [],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 16],
    ],
    'sa' => [
      'store_id' => ['en' => 4, 'ar' => 7],
    ],
    'ae' => [
      'store_id' => ['en' => 10, 'ar' => 13],
    ],
    'eg' => [
      'store_id' => ['en' => 22, 'ar' => 19],
    ],
    'bh' => [
      'store_id' => ['en' => 28, 'ar' => 25],
    ],
    'qa' => [
      'store_id' => ['en' => 34, 'ar' => 31],
    ],
  ],
  'default' => [
    'kw' => [
      'magento_lang_prefix' => [
        'en' => 'kwt_en',
        'ar' => 'kwt_ar',
      ],
    ],
    'sa' => [
      'magento_lang_prefix' => [
        'en' => 'sau_en',
        'ar' => 'sau_ar',
      ],
    ],
    'ae' => [
      'magento_lang_prefix' => [
        'en' => 'are_en',
        'ar' => 'are_ar',
      ],
    ],
    'eg' => [
      'magento_lang_prefix' => [
        'en' => 'egy_en',
        'ar' => 'egy_ar',
      ],
    ],
    'bh' => [
      'magento_lang_prefix' => [
        'en' => 'bhr_en',
        'ar' => 'bhr_ar',
      ],
    ],
    'qa' => [
      'magento_lang_prefix' => [
        'en' => 'qat_en',
        'ar' => 'qat_ar',
      ],
    ],
  ],
];
