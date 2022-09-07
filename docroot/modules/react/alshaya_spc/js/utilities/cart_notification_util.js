import { hasValue } from '../../../js/utilities/conditionsUtility';

/**
 * Helper function to check if Cart notification drawer is enabled.
 */
const isCartNotificationDrawerEnabled = () => {
  if (hasValue(drupalSettings.cart)
    && hasValue(drupalSettings.cart.cartNotificationDrawer)) {
    return drupalSettings.cart.cartNotificationDrawer;
  }

  return false;
};

export default isCartNotificationDrawerEnabled;
