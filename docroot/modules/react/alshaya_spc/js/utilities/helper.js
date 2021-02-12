/**
 * Helper function to check if Postpay is enabled.
 *
 * This will be true only when alshaya_bnpl module is enabled.
 */
export default function isPostpayEnabled() {
  let enabled = false;
  if (typeof drupalSettings.postpay_widget_info !== 'undefined'
    && typeof drupalSettings.postpay !== 'undefined') {
    enabled = true;
  }
  return enabled;
}
