import React from 'react';
import ReactDOM from 'react-dom';
import SofaSectionalForm from './components/sofa-sectional';

/**
 * Function to render Sofa and Section form
 * for the given element selector.
 *
 * @param {string} elementSelector
 *  Element selector for rendering form.
 */
const renderSofaSectionalForm = (elementSelector) => {
  const selectedFormEelement = document.querySelector(elementSelector);
  if (selectedFormEelement) {
    // Get sku from forms data-sku attribute.
    const { sku } = selectedFormEelement.dataset;

    // Render Sofa and Sectional form for selected form element.
    if (typeof sku !== 'undefined') {
      ReactDOM.render(
        <SofaSectionalForm sku={sku} elementSelector={elementSelector} />,
        selectedFormEelement,
      );
    }
  }
};

/**
 * Method to handle the modal on load event and render component.
 */
const handleModalOnLoad = () => {
  renderSofaSectionalForm('.has-sofa-sectional-modal-form .sku-base-form');
};

// Check if the PDP form element is exist and
// data-sku is present, then render the react form.
renderSofaSectionalForm('.has-sofa-sectional-full-form .sku-base-form');

// Add modal load event listener to render new
// sofa and sectional form whenever modal opens.
document.addEventListener('onModalLoad', handleModalOnLoad);
