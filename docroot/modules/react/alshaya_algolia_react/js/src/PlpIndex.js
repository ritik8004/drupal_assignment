import React from 'react';
import ReactDOM from 'react-dom';
import PlpApp from './plp/PlpApp';

// Proceed only if element is present.
const requiredData = jQuery('#alshaya-algolia-plp');
if (requiredData.length && !jQuery.isEmptyObject(requiredData.data())) {
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
