<?php

/**
 * List all known Magento environments keyed by environment machine name.
 */
function alshaya_get_magento_host_data() {
  return [
    // Mothercare.
    'mc_dev' => [
      'url' => 'https://develop2-56rwroy-z3gmkbwmwrl4g.eu.magentosite.cloud',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 3,
        ],
      ],
      'sa' => [
        'store_id' => [
          'en' => 5,
          'ar' => 4,
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 7,
          'ar' => 6,
        ],
      ],
      'consumer_key' => '',
      'consumer_secret' => '',
      'access_token' => '',
      'access_token_secret' => '',
    ],
    'mc_qa' => [
      'url' => 'https://qa-h47ppbq-z3gmkbwmwrl4g.eu.magentosite.cloud',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 3,
        ],
      ],
      'sa' => [
        'store_id' => [
          'en' => 5,
          'ar' => 4,
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 7,
          'ar' => 6,
        ],
      ],
      'consumer_key' => '',
      'consumer_secret' => '',
      'access_token' => '',
      'access_token_secret' => '',
    ],
    'mc_uat' => [
      'url' => 'https://staging-api.mothercare.com.kw.c.z3gmkbwmwrl4g.ent.magento.cloud',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 3,
        ],
      ],
      'sa' => [
        'store_id' => [
          'en' => 7,
          'ar' => 10,
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 16,
          'ar' => 13,
        ],
      ],
      'consumer_key' => '',
      'consumer_secret' => '',
      'access_token' => '',
      'access_token_secret' => '',
    ],
    'mc_prod' => [
      'url' => 'https://mcmena.store.alshaya.com',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 4,
        ],
      ],
      'sa' => [
        'store_id' => [
          'en' => 7,
          'ar' => 13,
        ],
        'magento_lang_prefix' => [
          'en' => 'sau_en',
          'ar' => 'sau_ar',
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 19,
          'ar' => 22,
        ],
        'magento_lang_prefix' => [
          'en' => 'are_en',
          'ar' => 'are_ar',
        ],
      ],
      'consumer_key' => '',
      'consumer_secret' => '',
      'access_token' => '',
      'access_token_secret' => '',
    ],
    // H&M.
    'hm_qa' => [
      'url' => 'https://qa-dc3i3ua-zbrr3sobrsb3o.eu.magentosite.cloud',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 2,
        ],
      ],
      'sa' => [
        'store_id' => [
          'en' => 3,
          'ar' => 4,
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 6,
          'ar' => 5,
        ],
      ],
      'consumer_key' => '',
      'consumer_secret' => '',
      'access_token' => '',
      'access_token_secret' => '',
    ],
    'hm_uat' => [
      'url' => 'https://hm-uat.store.alshaya.com',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 2,
        ],
      ],
      'sa' => [
        'store_id' => [
          'en' => 8,
          'ar' => 5,
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 14,
          'ar' => 11,
        ],
      ],
      'consumer_key' => '',
      'consumer_secret' => '',
      'access_token' => '',
      'access_token_secret' => '',
    ],
    'hm_prod' => [
      'url' => 'https://hm.store.alshaya.com',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 5,
        ],
      ],
      'sa' => [
        'store_id' => [
          'en' => 8,
          'ar' => 14,
        ],
        'magento_lang_prefix' => [
          'en' => 'sau_en',
          'ar' => 'sau_ar',
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 17,
          'ar' => 23,
        ],
        'magento_lang_prefix' => [
          'en' => 'are_en',
          'ar' => 'are_ar',
        ],
      ],
      'consumer_key' => '',
      'consumer_secret' => '',
      'access_token' => '',
      'access_token_secret' => '',
    ],
    // Pottery Barn.
    'pb_qa' => [
      'url' => 'https://integration-5ojmyuq-rfuu4sicyisyw.eu.magentosite.cloud',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 6,
        ],
      ],
      'sa' => [
        'store_id' => [
          'en' => 2,
          'ar' => 3,
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 4,
          'ar' => 5,
        ],
      ],
      'consumer_key' => 'auf96nt6b1loar4yc2qm55pluqd5sgrn',
      'consumer_secret' => 'i1lhnoafn6a1ggjou2juj366cpcnnhel',
      'access_token' => 's4rfv318v1gxmrnq8mjdn01uhejd8760',
      'access_token_secret' => '4x5otnn378pjr1v3acnmoe934niwjlw7',
    ],
    'pb_uat' => [
      'url' => 'https://pb-uat.store.alshaya.com',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 18,
        ],
      ],
      'sa' => [
        'store_id' => [
          'en' => 6,
          'ar' => 9,
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 12,
          'ar' => 15,
        ],
      ],
      'consumer_key' => '',
      'consumer_secret' => '',
      'access_token' => '',
      'access_token_secret' => '',
    ],
    // Victoria Secret.
    'vs_qa' => [
      'url' => 'https://integration-5ojmyuq-kpwgmbven7d7y.eu-3.magentosite.cloud',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 6,
        ],
      ],
      'sa' => [
        'store_id' => [
          'en' => 2,
          'ar' => 3,
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 4,
          'ar' => 5,
        ],
      ],
      'consumer_key' => '',
      'consumer_secret' => '',
      'access_token' => '',
      'access_token_secret' => '',
    ],
    'vs_uat' => [
      'url' => 'https://vs-uat.store.alshaya.com',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 18,
        ],
      ],
      'sa' => [
        'store_id' => [
          'en' => 6,
          'ar' => 9,
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 12,
          'ar' => 15,
        ],
      ],
      'consumer_key' => '',
      'consumer_secret' => '',
      'access_token' => '',
      'access_token_secret' => '',
    ],
    'vs_prod' => [
      'url' => 'https://vs.store.alshaya.com',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 18,
        ],
      ],
      'sa' => [
        'store_id' => [
          'en' => 6,
          'ar' => 9,
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 12,
          'ar' => 15,
        ],
      ],
      'consumer_key' => '',
      'consumer_secret' => '',
      'access_token' => '',
      'access_token_secret' => '',
    ],
    // BathBodyWorks.
    'bbw_qa' => [
      'url' => 'https://integration-5ojmyuq-bbk3lvknero4c.eu-3.magentosite.cloud',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 2,
        ],
      ],
      'sa' => [
        'store_id' => [
          'en' => 3,
          'ar' => 4,
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 5,
          'ar' => 6,
        ],
      ],
      'consumer_key' => '',
      'consumer_secret' => '',
      'access_token' => '',
      'access_token_secret' => '',
    ],
    'bbw_uat' => [
      'url' => 'https://staging-bbw.store.alshaya.com.c.bbk3lvknero4c.ent.magento.cloud',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 19,
        ],
      ],
      'sa' => [
        'store_id' => [
          'en' => 7,
          'ar' => 10,
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 13,
          'ar' => 16,
        ],
      ],
      'consumer_key' => '',
      'consumer_secret' => '',
      'access_token' => '',
      'access_token_secret' => '',
    ],
    'bbw_prod' => [
      'url' => 'http://bbw.store.alshaya.com',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 18,
        ],
      ],
      'sa' => [
        'store_id' => [
          'en' => 6,
          'ar' => 9,
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 12,
          'ar' => 15,
        ],
      ],
      'consumer_key' => '',
      'consumer_secret' => '',
      'access_token' => '',
      'access_token_secret' => '',
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
    ],
  ];
}
