import React from 'react';
import ReactDOM from 'react-dom';
import PdpSddEd from './pdp-layout/components/pdp-sdd-ed';

ReactDOM.render(
  <PdpSddEd deliveryOptions={drupalSettings.deliveryOptions} />,
  document.getElementById('sdd-ed-labels'),
);
