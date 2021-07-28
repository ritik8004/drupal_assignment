import React from 'react';
import ReactDOM from 'react-dom';
import PlpApp from './plp/PlpApp';

(function PlpV2($, Drupal) {
  Drupal.behaviors.alshayaAlgoliaReactPLPV2Index = { // eslint-disable-line no-param-reassign
    attach: function process() {
      // Proceed only if RCS is processed.
      const requiredData = $('#rcs-ph-product_category_list > span');
      if (requiredData.length) {
        ReactDOM.render(
          <PlpApp dataAttribute={requiredData[0].dataset} />,
          document.querySelector('#alshaya-algolia-plp'),
        );
      }
    },
  };
}(jQuery, Drupal));
