/**
 * @file
 * JS code to integrate with GTM.
 */
 (function ($,Drupal, dataLayer) {



 /**
   * Function to push the vouchers return events to data layer.
   *
   * @param {object} vouchers
   *   Object containing the basic vouchers details.
   * @param {string} eventAction
   *   The event that is getting performed during product return.
   * @param {object} vouchersListDes
   *   Object containing the basic vouchersListDes details.
   */


    // This function is called when the click on the discount voucher link
    Drupal.discountVoucherData = function () {
      // Prepare the return data.
      var returnData = {
        event: 'discountVoucherClickPopup',
        eventCategory: "memberOffer",
        eventAction: "discountVoucher",
        eventLabel: "voucher_link",
      }
      // Proceed only if dataLayer exists.
      if (dataLayer) {
        dataLayer.push(returnData);
      }
    }

// This function is called when the click on the discount voucher link
Drupal.voucherOfferSelected = function (vouchers, eventAction) {

  // Prepare the return data.
  var returnData = {
    event: 'voucherOfferSelected',
    eventAction: eventAction,
    eventLabel: vouchers,
    eventCategory: 'memberOffer',
    }
  // Proceed only if dataLayer exists.
  if (dataLayer) {
    dataLayer.push(returnData);
  }
}
// This function is called when the apply offer
Drupal.voucherOfferSelectedApply = function (vouchersListDes, eventAction) {

  // Prepare the return data.
  var returnData = {
    event: 'voucherOfferSelectedApply',
    eventAction: eventAction,
    eventLabel: vouchersListDes,
    eventCategory: 'memberOffer',
    }
  // Proceed only if dataLayer exists.
  if (dataLayer) {
    dataLayer.push(returnData);
  }
}

  })($,Drupal, dataLayer);
