<?php

/**
 * List all known Magento environments keyed by environment machine name.
 */
function alshaya_get_magento_host_data() {
  return [
    // Mothercare.
    'mc_qa' => 'https://qa-h47ppbq-z3gmkbwmwrl4g.eu.magentosite.cloud',
    'mc_uat' => 'https://staging-api.mothercare.com.kw.c.z3gmkbwmwrl4g.ent.magento.cloud',
    'mc_prod' => 'https://mcmena.store.alshaya.com',
    // H&M.
    'hm_qa' => 'https://qa-dc3i3ua-zbrr3sobrsb3o.eu.magentosite.cloud',
    'hm_uat' => 'https://hm-uat.store.alshaya.com',
    'hm_prod' => 'http://hm.store.alshaya.com.c.zbrr3sobrsb3o.ent.magento.cloud',
    // Pottery Barn.
    'pb_qa' => 'https://qa-h47ppbq-rfuu4sicyisyw.eu.magentosite.cloud',
    // BathBodyWorks.
    'bbw_qa' => 'https://integration-5ojmyuq-bbk3lvknero4c.eu-3.magentosite.cloud',
  ];
}
