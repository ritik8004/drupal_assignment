import { hasValue } from './conditionsUtility';

/**
 * Helper function to get pdp div count for dynamic yield.
 */
export const getPdpDivsCount = () => {
  if (hasValue(drupalSettings.dynamicYieldConfig)
    && hasValue(drupalSettings.dynamicYieldConfig.pdpEmptyDivs)) {
    return drupalSettings.dynamicYieldConfig.pdpEmptyDivs;
  }
  return 0;
};

/**
 * Helper function to get cart div count for dynamic yield.
 */
export const getCartDivsCount = () => {
  if (hasValue(drupalSettings.dynamicYieldConfig)
    && hasValue(drupalSettings.dynamicYieldConfig.cartEmptyDivs)) {
    return drupalSettings.dynamicYieldConfig.cartEmptyDivs;
  }
  return 0;
};
