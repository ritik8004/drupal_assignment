import React from 'react';
import ReactDOM from 'react-dom';
import SofaSectionalForm from './components/sofa-sectional';

// Check if the PDP form element is exist and
// data-sku is present, then render the react form.
const pdpFormEelement = document.querySelector('.has-sofa-sectional-full-form .sku-base-form');
if (pdpFormEelement) {
  const { sku } = pdpFormEelement.dataset;
  if (typeof sku !== 'undefined') {
    ReactDOM.render(
      <SofaSectionalForm sku={sku} />,
      pdpFormEelement,
    );
  }
}

/**
 * Method to handle the modal on load event and render component.
 */
const handleModalOnLoad = () => {
  const modalFormEelement = document.querySelector('.has-sofa-sectional-modal-form .sku-base-form');
  if (modalFormEelement) {
    const { sku } = modalFormEelement.dataset;
    if (typeof sku !== 'undefined') {
      ReactDOM.render(
        <SofaSectionalForm sku={sku} />,
        modalFormEelement,
      );
    }
  }
};

// Add modal load event listener to render new
// sofa and sectional form whenever modal opens.
document.addEventListener('onModalLoad', handleModalOnLoad);
