import { hasValue } from './conditionsUtility';

/**
 * Helper function to check if Cart notification drawer is enabled.
 */
const isCartNotificationDrawerEnabled = () => hasValue(drupalSettings.cart)
    && hasValue(drupalSettings.cart.cartNotificationDrawer);

export default isCartNotificationDrawerEnabled;
