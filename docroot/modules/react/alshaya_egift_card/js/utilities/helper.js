/**
 * Helper function to get egift card enabled.
 */
function isEgiftCardEnabled() {
  let egiftCardStatus = 0;
  if (typeof drupalSettings.egiftCard !== 'undefined'
    && typeof drupalSettings.egiftCard.enabled !== 'undefined') {
    egiftCardStatus = drupalSettings.egiftCard.enabled;
  }

  return egiftCardStatus;
}

export default isEgiftCardEnabled;
