<?php

/**
 * List all known Magento environments keyed by environment machine name.
 */
function alshaya_get_magento_host_data() {
  return [
    // Mothercare.
    'mc_dev' => 'http://develop2-56rwroy-z3gmkbwmwrl4g.eu.magentosite.cloud',
    'mc_qa' => 'http://qa-h47ppbq-z3gmkbwmwrl4g.eu.magentosite.cloud',
    'mc_test' => 'https://master-7rqtwti-z3gmkbwmwrl4g.eu.magentosite.cloud',
    'mc_uat' => 'https://staging-api.mothercare.com.kw.c.z3gmkbwmwrl4g.ent.magento.cloud',
    'mc_prod' => 'https://mcmena.store.alshaya.com',
    // H&M.
    'hm_vat' => 'https://vat-sm7i7qa-zbrr3sobrsb3o.eu.magentosite.cloud',
    'hm_dmv2' => 'https://acr2-27-o7wcoxy-zbrr3sobrsb3o.eu.magentosite.cloud',
    'hm_dev' => 'https://dev-tqspiwq-zbrr3sobrsb3o.eu.magentosite.cloud',
    'hm_qa' => 'https://qa-dc3i3ua-zbrr3sobrsb3o.eu.magentosite.cloud',
    'hm_uat' => 'https://hm-uat.store.alshaya.com',
    'hm_prod' => 'http://hm.store.alshaya.com.c.zbrr3sobrsb3o.ent.magento.cloud',
    // Mothercare SA.
    'mcsa_dev' => 'https://acr2-27-o7wcoxy-zbrr3sobrsb3o.eu.magentosite.cloud',
    'mcsa_qa' => 'https://acr2-27-o7wcoxy-zbrr3sobrsb3o.eu.magentosite.cloud',
    'mcsa_uat' => 'https://acr2-27-o7wcoxy-zbrr3sobrsb3o.eu.magentosite.cloud',
    'mcsa_prod' => 'https://acr2-27-o7wcoxy-zbrr3sobrsb3o.eu.magentosite.cloud',
  ];
}
