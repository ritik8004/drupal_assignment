/**
 * Helper function to get egift card enabled.
 */
function isEgiftCardEnabled() {
  let egiftCardStatus = false;
  if (typeof drupalSettings.egiftCard !== 'undefined'
    && typeof drupalSettings.egiftCard.enabled !== 'undefined') {
    egiftCardStatus = drupalSettings.egiftCard.enabled;
  }

  return egiftCardStatus;
}

export default isEgiftCardEnabled;
