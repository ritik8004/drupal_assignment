import React from 'react';
import ReactDOM from 'react-dom';
import SofaSectionalForm from './components/sofa-sectional';

const { productInfo } = drupalSettings;
const sku = Object.keys(productInfo);

ReactDOM.render(
  <SofaSectionalForm sku={sku} />,
  document.querySelector('.sku-base-form'),
);
