import React from 'react';
import ReactDOM from 'react-dom';
import DeliveryOptions from './expressdelivery/components/delivery-options';

let componentAttached = false;

// Note: This file is dynamically loaded in the library_info_alter hook of
// alshaya_rcs_product.module.
Drupal.behaviors.alshayaSpcPdpRcsExpressDeliveryBehavior = {
  attach: function alshayaSpcPdpRcsExpressDelivery() {
    const pageLoaded = document.querySelector('.rcs-page.rcs-loaded');
    const element = document.querySelector('#express-delivery-options');
    const skuBaseForm = document.querySelector('.sku-base-form');
    if (!componentAttached
        && pageLoaded
        && element
        && skuBaseForm
    ) {
      componentAttached = true;
      ReactDOM.render(
        <DeliveryOptions />,
        element,
      );
    }
  },
};
