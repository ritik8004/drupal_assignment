/**
 * Helper function to check if Add to Bag is enabled.
 */
const isAddToBagEnabled = () => {
  // Get global add to cart status.
  const { checkoutFeatureStatus } = drupalSettings;

  if (typeof drupalSettings.add_to_bag !== 'undefined' && checkoutFeatureStatus === 'enabled') {
    return drupalSettings.add_to_bag.display_addtobag;
  }

  return false;
};

/**
 * Add the markup for configurable drawer.
 */
const createConfigurableDrawer = () => {
  if (isAddToBagEnabled()) {
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
