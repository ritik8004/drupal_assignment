import React from 'react';
import ReactDOM from 'react-dom';
import AttrNavigation from './components/attr_navigation';

/**
 * Function to render Attribute navigation in main menu.
 */
const renderAttributeNavigation = () => {
  const attributeNavSelector = document.querySelectorAll('[data-nav-attr="size_shoe_eu"]');
  const attributeNavSelectorList = [].slice.call(attributeNavSelector);

  attributeNavSelectorList.forEach((attrNavElement) => {
    ReactDOM.render(
      <AttrNavigation
        attr="size_shoe_eu"
        element={attrNavElement}
      />,
      attrNavElement,
    );
  });
};

/**
 * Method to handle the modal on load event and render component.
 */
const handleOnLoad = () => {
  renderAttributeNavigation();
};


// Add modal load event listener to render new
// sofa and sectional form whenever modal opens.
window.addEventListener('load', handleOnLoad);
