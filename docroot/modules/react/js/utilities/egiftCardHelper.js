/**
 * Helper function to check if egift card is enabled.
 */
export default function isEgiftCardEnabled() {
  let egiftCardStatus = false;
  if (typeof drupalSettings.egiftCard !== 'undefined'
    && typeof drupalSettings.egiftCard.enabled !== 'undefined') {
    egiftCardStatus = drupalSettings.egiftCard.enabled;
  }

  return egiftCardStatus;
}
