/**
 * @file
 * JS code to integrate with GTM.
 */
(function (Drupal, dataLayer) {

  /**
   * Function to push the product return events to data layer.
   *
   * @param {object} products
   *   Object containing the products that is getting returned.
   * @param {object} order
   *   Object containing the basic order details.
   * @param {string} eventAction
   *   The event that is getting performed during product return.
   */
  Drupal.alshayaSeoGtmPushReturn = function (products, order, eventAction) {
    // Prepare the return data.
    var returnData = {
      event: "returns",
      eventCategory: "return",
      eventAction: eventAction,
      eventLabel: "",
      eventValue: 0,
      nonInteraction: 0,
      ecommerce: {
        detail: {
          ...order,
          products: products
        }
      }
    }
    // Proceed only if dataLayer exists.
    if (dataLayer) {
      dataLayer.push(returnData);
    }
  }

})(Drupal, dataLayer);
