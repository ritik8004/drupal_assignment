import React from 'react';
import ReactDOM from 'react-dom';
import PlpApp from './plp/PlpApp';

// Proceed only if element is present.
const listingElement = jQuery('#alshaya-algolia-plp');
if (listingElement.length && !jQuery.isEmptyObject(listingElement.data())) {
  // Destructure the required values from the attributes.
  const {
    categoryField, ruleContext, level, hierarchy, promotionId,
  } = listingElement.data();

  ReactDOM.render(
    <PlpApp
      categoryField={categoryField}
      ruleContext={ruleContext}
      level={level}
      hierarchy={hierarchy}
      promotionNodeId={promotionId}
    />,
    document.querySelector('#alshaya-algolia-plp'),
  );
}
