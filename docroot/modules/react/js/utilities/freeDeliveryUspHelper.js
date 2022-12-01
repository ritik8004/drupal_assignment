import { hasValue } from './conditionsUtility';

/**
 * Helper function to check if Free Delivery Usp is enabled.
 */
export default function isFreeDeliveryUspEnabled() {
  return hasValue(drupalSettings.freeDeliveryUspEnabled);
}
