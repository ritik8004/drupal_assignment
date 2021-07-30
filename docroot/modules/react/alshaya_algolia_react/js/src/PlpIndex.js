import React from 'react';
import ReactDOM from 'react-dom';
import PlpApp from './plp/PlpApp';

// Proceed only if element is present.
const requiredData = jQuery('#alshaya-algolia-plp');
if (requiredData.length && Object.keys(requiredData[0].dataset).length) {
  ReactDOM.render(
    <PlpApp dataAttribute={requiredData[0].dataset} />,
    document.querySelector('#alshaya-algolia-plp'),
  );
}
