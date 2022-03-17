import { hasValue } from './conditionsUtility';

/**
 * Helper function to check if Online Returns is enabled.
 */
const isOnlineReturnsEnabled = () => {
  if (hasValue(drupalSettings.onlineReturns)
    && hasValue(drupalSettings.onlineReturns.enabled)) {
    return drupalSettings.onlineReturns.enabled;
  }

  return false;
};

export default isOnlineReturnsEnabled;
