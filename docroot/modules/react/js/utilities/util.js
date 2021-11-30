/**
 * Provides the current currency code.
 *
 * @return null|string
 *   The currency code if present or null.
 */
export default function getCurrencyCode() {
  const alshayaSpc = drupalSettings.alshaya_spc;
  if (Object.prototype.hasOwnProperty.call(alshayaSpc, 'currency_config')) {
    return alshayaSpc.currency_config.currency_code;
  }

  return null;
}
