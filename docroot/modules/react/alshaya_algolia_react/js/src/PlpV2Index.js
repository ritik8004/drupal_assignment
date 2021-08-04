import React from 'react';
import ReactDOM from 'react-dom';
import PlpApp from './plp/PlpApp';

(function PlpV2($, Drupal, drupalSettings) {
  Drupal.behaviors.alshayaAlgoliaReactPLPV2Index = { // eslint-disable-line no-param-reassign
    attach: function process() {
      // Proceed only if RCS is processed.
      const requiredData = $('#rcs-ph-product_category_list > span');
      if (requiredData.length && !$.isEmptyObject(requiredData.data())) {
        // Destructure the required values from the attributes.
        const {
          categoryField, ruleContext, level, hierarchy,
        } = requiredData.data();

        ReactDOM.render(
          <PlpApp
            categoryField={categoryField}
            ruleContext={ruleContext}
            level={level}
            hierarchy={hierarchy}
          />,
          document.querySelector('#alshaya-algolia-plp'),
        );
      }
      // Re-attach all behaviors after some delay.
      // @todo To check for a proper way to Re-attach the behaviors.
      setTimeout(() => {
        Drupal.attachBehaviors(document, drupalSettings);
      }, 5000);
    },
  };
}(jQuery, Drupal));
