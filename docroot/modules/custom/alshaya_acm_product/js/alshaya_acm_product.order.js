/**
 * Global variable which will contain acq_product related data/methods among
 * other things.
 */
window.commerceBackend = window.commerceBackend || {};

(function acmProductOrders(drupalSettings) {
  /**
   * Gets data for Order Details page
   *
   * @returns {Promise}
   *   Order details data.
   */
  window.commerceBackend.getOrderDetailsData = function getOrderDetailsData() {
    return new Promise(function (resolve) {
      return resolve(drupalSettings.order);
    });
  }
})(drupalSettings);
