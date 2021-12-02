/**
 * Helper function to check if Add to Bag is enabled.
 */
const isAddToBagEnabled = () => {
  // Get global add to cart status.
  const { checkoutFeatureStatus } = drupalSettings;

  if (typeof drupalSettings.add_to_bag !== 'undefined'
    && typeof drupalSettings.add_to_bag.display_addtobag !== 'undefined'
    && checkoutFeatureStatus === 'enabled') {
    return drupalSettings.add_to_bag.display_addtobag;
  }

  return false;
};

/**
 * Add the markup for configurable drawer.
 */
const createConfigurableDrawer = (force) => {
  if (isAddToBagEnabled() || force) {
    const id = 'configurable-drawer';
    if (!document.getElementById(id)) {
      const element = document.createElement('div');
      element.id = id;
      document.body.appendChild(element);
    }
  }
};

export {
  isAddToBagEnabled,
  createConfigurableDrawer,
};
