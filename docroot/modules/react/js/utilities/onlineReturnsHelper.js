import { hasValue } from './conditionsUtility';

/**
 * Helper function to check if Online Returns is enabled.
 */
const isOnlineReturnsEnabled = () => hasValue(drupalSettings.onlineReturns);

export default isOnlineReturnsEnabled;
