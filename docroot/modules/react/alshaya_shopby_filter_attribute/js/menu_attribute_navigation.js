import React from 'react';
import ReactDOM from 'react-dom';
import AttrNavigation from './components/attr_navigation';

/**
 * Function to render Attribute navigation in main menu.
 */
const renderAttributeNavigation = () => {
  // Return if filter attribute is undefined or empty.
  if (typeof drupalSettings.shopByFilterAttribute.menuFilterAttributes === 'undefined'
    || (typeof drupalSettings.shopByFilterAttribute.menuFilterAttributes !== 'undefined'
    && drupalSettings.shopByFilterAttribute.menuFilterAttributes === '')) {
    return;
  }

  const attributeNavSelector = document.querySelectorAll(`[data-nav-attr="${drupalSettings.shopByFilterAttribute.menuFilterAttributes}"]`);
  const attributeNavSelectorList = [].slice.call(attributeNavSelector);

  attributeNavSelectorList.forEach((attrNavElement) => {
    ReactDOM.render(
      <AttrNavigation
        attr={drupalSettings.shopByFilterAttribute.menuFilterAttributes}
        element={attrNavElement}
      />,
      attrNavElement,
    );
  });
};

// Check if config exist and feature is enabled and bind the page load event
// to render the attribute navigation component.
if (typeof drupalSettings.shopByFilterAttribute !== 'undefined'
  && typeof drupalSettings.shopByFilterAttribute.enabled !== 'undefined'
  && drupalSettings.shopByFilterAttribute.enabled) {
  // Add modal load event listener to render shop by filters menu item.
  window.addEventListener('load', renderAttributeNavigation);
}
