/**
 * @file
 * JS code to integrate with GTM.
 */
 (function (Drupal, dataLayer) {

   // This function is called when the click on the discount voucher link
    Drupal.alshayaSeoGtmPushVoucherLinkClick = function () {
      // Prepare the voucher click data,.
      var voucherClickData = {
        event: 'voucherLinkClick',
        eventCategory: "memberOffer",
        eventAction: "discountVoucher",
        eventLabel: "voucher_link",
      }
      // Proceed only if dataLayer exists.
      if (dataLayer) {
        dataLayer.push(voucherClickData);
      }
    }

  /**
   * Function to push the voucher click events to data layer.
   *
   * @param {object} vouchersSelected
   *   Object containing the basic vouchers details.
   * @param {string} eventAction
   *   The event that is getting performed during product return.
   */

// This function is called when voucher(s) are selected by customer.
Drupal.alshayaSeoGtmPushVoucherOfferSelect = function (vouchersSelected, eventAction) {

  // Prepare the voucher data.
  var voucherSelectedData = {
    event: 'VoucherOfferSelect',
    eventAction: eventAction,
    eventLabel: vouchersSelected,
    eventCategory: 'memberOffer',
    }
  // Proceed only if dataLayer exists.
  if (dataLayer) {
    dataLayer.push(voucherSelectedData);
  }
}

 /**
   * Function to push the voucher offer events to data layer.
   * @param {object} appliedVouchers
   *   Object containing the basic vouchersListDes details.
   * @param {string} eventAction
   *   The event that is getting performed during product return.
   */

// This function is called when offers are applied by customer
Drupal.alshayaSeoGtmPushVoucherOfferSelectedApply = function (appliedVouchers, eventAction) {

  // Prepare the voucher data.
  var returnData = {
    event: 'voucherApplied',
    eventAction: eventAction,
    eventLabel: appliedVouchers,
    eventCategory: 'memberOffer',
    }
  // Proceed only if dataLayer exists.
  if (dataLayer) {
    dataLayer.push(returnData);
  }
}

  })(Drupal, dataLayer);
