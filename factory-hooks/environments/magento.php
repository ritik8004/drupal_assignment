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
      ],
      'ae' => [
        'store_id' => [
          'en' => 19,
          'ar' => 22,
        ],
      ],
    ],
    // H&M.
    'hm_qa' => [
      'url' => 'https://qa-dc3i3ua-zbrr3sobrsb3o.eu.magentosite.cloud',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 2,
        ],
        'magento_lang_prefix' => [
          'en' => 'default',
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
    ],
    'hm_uat' => [
      'url' => 'https://hm-uat.store.alshaya.com',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 2,
        ],
        'magento_lang_prefix' => [
          'en' => 'default',
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
    ],
    'hm_prod' => [
      'url' => 'https://hm.store.alshaya.com',
      'kw' => [
        'store_id' => [
          'en' => 1,
          'ar' => 5,
        ],
        'magento_lang_prefix' => [
          'en' => 'default',
        ],
      ],
      'sa' => [
        'store_id' => [
          'en' => 8,
          'ar' => 14,
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 17,
          'ar' => 23,
        ],
      ],
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
        'magento_lang_prefix' => [
          'en' => 'sau_en',
          'ar' => 'sau_ar',
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 4,
          'ar' => 5,
        ],
        'magento_lang_prefix' => [
          'en' => 'are_en',
          'ar' => 'are_ar',
        ],
      ],
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
        'magento_lang_prefix' => [
          'en' => 'sau_en',
          'ar' => 'sau_ar',
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 4,
          'ar' => 5,
        ],
        'magento_lang_prefix' => [
          'en' => 'are_en',
          'ar' => 'are_ar',
        ],
      ],
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
        'magento_lang_prefix' => [
          'en' => 'sau_en',
          'ar' => 'sau_ar',
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 12,
          'ar' => 15,
        ],
        'magento_lang_prefix' => [
          'en' => 'are_en',
          'ar' => 'are_ar',
        ],
      ],
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
        'magento_lang_prefix' => [
          'en' => 'sau_en',
          'ar' => 'sau_ar',
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 5,
          'ar' => 6,
        ],
        'magento_lang_prefix' => [
          'en' => 'are_en',
          'ar' => 'are_ar',
        ],
      ],
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
        'magento_lang_prefix' => [
          'en' => 'sau_en',
          'ar' => 'sau_ar',
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 13,
          'ar' => 16,
        ],
        'magento_lang_prefix' => [
          'en' => 'are_en',
          'ar' => 'are_ar',
        ],
      ],
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
        'magento_lang_prefix' => [
          'en' => 'sau_en',
          'ar' => 'sau_ar',
        ],
      ],
      'ae' => [
        'store_id' => [
          'en' => 12,
          'ar' => 15,
        ],
        'magento_lang_prefix' => [
          'en' => 'are_en',
          'ar' => 'are_ar',
        ],
      ],
    ],
    'default' => [
      'stock_mode' => 'push',
      'kw' => [
        'magento_lang_prefix' => [
          'en' => 'kwt_en',
          'ar' => 'kwt_ar',
        ],
      ],
      'sa' => [
        'magento_lang_prefix' => [
          'en' => 'ksa_en',
          'ar' => 'ksa_ar',
        ],
      ],
      'ae' => [
        'magento_lang_prefix' => [
          'en' => 'uae_en',
          'ar' => 'uae_ar',
        ],
      ],
    ],
  ];
}
