import React from 'react';
import ReactDOM from 'react-dom';
import DeliveryOptions from './expressdelivery/components/delivery-options';

let componentAttached = false;
const id = '#express-delivery-options';

// Note: This file is dynamically loaded in the library_info_alter hook of
// alshaya_rcs_product.module.
Drupal.behaviors.alshayaSpcPdpRcsEdBehavior = {
  attach: function alshayaSpcPdpRcsEd() {
    const pageLoaded = document.querySelector('.rcs-page.rcs-loaded');
    if (!componentAttached
        && pageLoaded
        && document.querySelector(id)
    ) {
      componentAttached = true;
      ReactDOM.render(
        <DeliveryOptions />,
        document.querySelector(id),
      );
    }
  },
};
