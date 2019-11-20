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
  'mc_uat' => [
    'url' => 'https://mc-uat.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => '3ewl8lsult7l5mpp1ckv0hw1ftk0u2bc',
      'consumer_secret' => '84avnwtrinkpt2jmda6f61l8vy5cabb1',
      'access_token' => 'yw1bvvwqe1vrab9sqjioepclb044jja2',
      'access_token_secret' => 'bsmp4igrv2bgtn6pk5ojko32qvrrk798',
    ],
    'kw' => [
      'store_id' => ['en' => 1, 'ar' => 3],
    ],
    'sa' => [
      'store_id' => ['en' => 7, 'ar' => 10],
    ],
    'ae' => [
      'store_id' => ['en' => 16, 'ar' => 13],
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
  ],
  // H&M.
  'hm_qa' => [
    'url' => 'https://qa-dc3i3ua-zbrr3sobrsb3o.eu.magentosite.cloud',
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
  'hm_uat' => [
    'url' => 'https://hm-uat.store.alshaya.com',
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
    'url' => 'https://pb-uat.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => 'u59m0076qalrjodhiwoekpt2qpens7c4',
      'consumer_secret' => '521nybox1a70rjpwf1yxyfoqhiyrv7x1',
      'access_token' => 'gsw0sb6xy52kfww9yufgcu6dsixka3g9',
      'access_token_secret' => '6a38i2p1qaxa1hfkjrdmhjtwluy57itq',
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
    'url' => 'https://vs-uat.store.alshaya.com',
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
  ],
  'bbw_uat' => [
    'url' => 'https://staging-bbw.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => 'nilvx16kpllrdfevec15aer2u1ahusto',
      'consumer_secret' => '4rin9lodbf263w9sdc9hmei8i67p0mhc',
      'access_token' => '5yx9g0jnytt2maah8417cvi4ipe6l3cq',
      'access_token_secret' => 'm6ae5fwc1eowx7w6ap02ekl16f0p0ita',
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
  ],
  'bbw_prod' => [
    'url' => 'http://bbw.store.alshaya.com',
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
  ],
  'fl_uat' => [
    'url' => 'https://fl-uat.store.alshaya.com',
    'magento_secrets' => [
      'consumer_key' => 'bs3iqr7l0o0nu1m65gqj0i9g6khwrwlg',
      'consumer_secret' => 'd5y2b1wvvuwja4xjeaa08qgjpbr603ji',
      'access_token' => '3ag36xcjyj1u2dxrvu859gsnyqgthirb',
      'access_token_secret' => '4gx37p92dgm5tfd5i0f4po6ggab0lblu',
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
  ],
  // Westelm.
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
  ],
];
