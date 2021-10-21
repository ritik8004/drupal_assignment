/**
 * Utility function to get tooltip msg for points on hold.
 */
function getTooltipPointsOnHoldMsg() {
  return Drupal.t('Your points will be credited to your account. You will be able to redeem them 14 days after delivery as per Aura Terms & Conditions and Alshaya exchange and refund policies.');
}

/**
 * Get the complete path for the middleware API.
 *
 * @param {string} path
 *  The API path.
 *
 * @returns {string}
 *   The complete middware API url.
 */
function i18nMiddleWareUrl(path) {
  const langcode = window.drupalSettings.path.currentLanguage;
  return `${window.drupalSettings.alshaya_spc.middleware_url}/${path}?lang=${langcode}`;
}

export {
  getTooltipPointsOnHoldMsg,
  i18nMiddleWareUrl,
};
