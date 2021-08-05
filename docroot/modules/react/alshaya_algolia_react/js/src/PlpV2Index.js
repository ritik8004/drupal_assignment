import React from 'react';
import ReactDOM from 'react-dom';
import PlpApp from './plp/PlpApp';

(function PlpV2($, Drupal, drupalSettings) {
  Drupal.behaviors.alshayaAlgoliaReactPLPV2Index = { // eslint-disable-line no-param-reassign
    attach: function process() {
      // Proceed only if RCS is processed.
      const listingElement = $('#rcs-ph-product_category_list > span');
      if (
        listingElement.length
        && !$.isEmptyObject(listingElement.data())
        && !listingElement.hasClass('processed')) {
        // Destructure the required values from the attributes.
        const {
          categoryField, ruleContext, level, hierarchy,
        } = listingElement.data();
        // Adding processed class to just execute the rendering once.
        listingElement.addClass('processed');
        ReactDOM.render(
          <PlpApp
            categoryField={categoryField}
            ruleContext={ruleContext}
            level={level}
            hierarchy={hierarchy}
          />,
          document.querySelector('#alshaya-algolia-plp'),
        );

        // Re-attach all behaviors after some delay.
        Drupal.attachBehaviors(document, drupalSettings);
      }
    },
  };
}(jQuery, Drupal));
