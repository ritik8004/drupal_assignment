/**
 * @file
 * JS code to integrate with GTM.
 */

 (function ($, Drupal, dataLayer) {

  Drupal.behaviors.alshayaHelloMember = {
    attach: function (context) {
      $('.hello-membership-terms .contact-us',context).once('helloMemberTermsConditions').on('click', function () {
        Drupal.alshayaSeoGtmPusHmAutoEnrollClickContact();
      });
    }
  };

  /**
   * This function is called when the click on the discount voucher link.
   */
  Drupal.alshayaSeoGtmPushVoucherLinkClick = function () {
    // Prepare the voucher click data,.
    var voucherClickData = {
      event: 'hellomember',
      eventCategory: "memberOffer",
      eventAction: "discountVoucher link click",
    }
    // Proceed only if dataLayer exists.
    if (dataLayer) {
      dataLayer.push(voucherClickData);
    }
  }

  /**
   * Function to push the voucher click events to data layer.ß
   * @param {object} vouchersSelected
   *   Object containing the basic vouchers details.
   * @param {string} eventAction
   *   The event that is getting performed when we perform action.
   */
  Drupal.alshayaSeoGtmPushVoucherOfferSelect = function (vouchersSelected, eventAction) {

    // Prepare the voucher data.
    var voucherSelectedData = {
      event: 'hellomember',
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
   *  Function to push the voucher offer events to data layer.
   * @param {object} appliedVouchers
   *   Object containing the basic vouchersListDes details.
   * @param {string} eventAction
   *
   */
  Drupal.alshayaSeoGtmPushVoucherOfferSelectedApply = function (appliedVouchers, eventAction) {
    // Prepare the voucher data.
    var voucherAppliedData = {
      event: 'hellomember',
      eventAction: eventAction,
      eventLabel: appliedVouchers,
      eventCategory: 'memberOffer',
      }
    // Proceed only if dataLayer exists.
    if (dataLayer) {
      dataLayer.push(voucherAppliedData);
    }
  }

  /**
   * Function to push the benefits offer data to gtm.
   *
   * @param {object} benefitsData
   *  Object containing benefits offer data that is clicked.
   */
  Drupal.alshayaSeoGtmPushBenefitsOffer = function (benefitsData) {
    // Prepare the benefit offer data.
    var benefitGtmData = {
      event: "myaccount",
      eventAction: 'benefits - detail view',
      eventLabel: benefitsData.promotionType + '_' + benefitsData.description,
      eventCategory: 'myaccount',
      eventValue: 0,
      nonInteraction: 0,
    }
    dataLayer.push(benefitGtmData);
  }

  /**
   * Function to push the benefits cart data to gtm when benefits added to cart.
   *
   * @param {object} benefitsData
   *  Object containing benefits offer data that is clicked.
   */
  Drupal.alshayaSeoGtmPushBenefitAddToBag = function (benefitsData) {
    // Prepare the benefit offer data.
    var benefitGtmData = {
      event: "myaccount",
      eventAction: 'benefits - add to bag',
      eventLabel: benefitsData.title + '_' + benefitsData.promotionType,
      eventCategory: 'myaccount',
      eventValue: 0,
      nonInteraction: 0,
    }
    dataLayer.push(benefitGtmData);
  }

  /**
   * Function to push the button data to gtm when button on
   * Benefit landing page is clicked.
   *
   * @param {object} benefitsData
   *  Object containing benefits offer data that is clicked.
   */
  Drupal.alshayaSeoGtmPushBenefitButton = function (benefitsData) {
    // Prepare the benefit button data.
    var benefitGtmData = {
      eventCategory : 'my accounts interaction',
      eventAction: 'benefits - ' + benefitsData.buttonName,
      eventLabel: benefitsData.myBenefit.name,
      event: "myaccount",
    }
    dataLayer.push(benefitGtmData);
  }

  /**
   * Function to push the benefit show more click to gtm.
   *
   * @param {string} expanded
   *  State of show more button clicked.
   */
  Drupal.alshayaSeoGtmPushBenefitShowmore = function (expanded) {
    // Prepare the show more gtm data.
    var benefitShowMoreData = {
      event: "myaccount",
      eventAction: 'benefits - expand',
      eventLabel: expanded ? 'Show less' : 'Show all',
      eventCategory: 'myaccount',
      eventValue: 0,
      nonInteraction: 0,
    }
    dataLayer.push(benefitShowMoreData);
  }

  /**
   * Function to push the benefits qr code click to gtm.
   *
   * @param {string} benefitName
   *  Title of the benefit offer.
   * @param {string} benefitType
   *  Description of benefit type.
   */
  Drupal.alshayaSeoGtmPushBenefitQrData = function (benefitName, benefitType) {
    // Prepare the benefit qr code data.
    var benefitsQRdata = {
      event: "myaccount",
      eventAction: 'benefits - view qr code',
      eventLabel: benefitType + '_' + benefitName,
      eventCategory: 'myaccount',
      eventValue: 0,
      nonInteraction: 0,
    }
    dataLayer.push(benefitsQRdata);
  }

  /**
   * Function to push the loyalty switch events to data layer.
   *
   * @param {string} method
   *   Loyalty type selected by customer.
   */
  Drupal.alshayaSeoGtmLoyaltySwitch = function (method) {
    // Prepare the loyalty switch data.
    var loyaltySwitchData = {
      event: 'loyaltySwitch',
      eventAction: "loyalty_switch",
      eventLabel: method,
      eventCategory: "loyalty switch",
      eventValue: 0,
      nonInteraction: 0,
    }
    // Proceed only if dataLayer exists.
    if (dataLayer) {
      dataLayer.push(loyaltySwitchData);
    }
  }

  /**
   * Function to push the loyalty type errors to data layer.
   *
   * @param {string} method
   *   Loyalty type selected by customer.
   */
  Drupal.alshayaSeoGtmLoyaltyOptionsError = function (method, message) {
    // Prepare the loyalty error data.
    var errorData = {
      event: 'warning',
      eventAction: method,
      eventLabel: message,
      eventCategory: 'warning',
      eventValue: 0,
      nonInteraction: 0,
    }
    // Proceed only if dataLayer exists.
    if (dataLayer) {
      dataLayer.push(errorData);
    }
  }

  /**
   * This function is called when landed on the points view page.
   */
  Drupal.alshayaSeoGtmPushPoints = function () {
    // Prepare the points view data.
    var pointsData = {
      event: 'my accounts',
      eventCategory: "my accounts",
      eventAction: "points - view",
    }
    // Proceed only if dataLayer exists.
    if (dataLayer) {
      dataLayer.push(pointsData);
    }
  }

  /**
   * This function is called when click on the points view all link.
   */
  Drupal.alshayaSeoGtmPushPointsViewAll = function () {
    // Prepare the points view all click data.
    var pointsViewAll = {
      event: 'my accounts',
      eventCategory: "my accounts",
      eventAction: "points - view all",
    }
    // Proceed only if dataLayer exists.
    if (dataLayer) {
      dataLayer.push(pointsViewAll);
    }
  }

  /**
   * This function is called when birthday pop-up load.
   */
  Drupal.alshayaSeoGtmPushBirthdayPopupView = function (voucherName) {
    // Prepare the popup event data.
    var popUpData = {
      event: 'popup',
      eventCategory: "popup",
      eventAction: "hmspecialvoucher - show",
      eventLabel: voucherName,
    }
    // Proceed only if dataLayer exists.
    if (dataLayer) {
      dataLayer.push(popUpData);
    }
  }

  /**
   * This function is called when birthday pop-up close.
   */
  Drupal.alshayaSeoGtmPushBirtdayPopupClose = function (voucherName) {
    // Prepare the popup event data.
    var popUpData = {
      event: 'popup',
      eventCategory: "popup",
      eventAction: "hmspecialvoucher - close",
      eventLabel: voucherName,
    }
    // Proceed only if dataLayer exists.
    if (dataLayer) {
      dataLayer.push(popUpData);
    }
  }

  /**
   * This function is called when birthday pop-up click.
   */
  Drupal.alshayaSeoGtmPushBirtdayPopupClick = function (voucherName) {
    // Prepare the popup event data.
    var birthdayPopupClick = {
      event: 'popup',
      eventCategory: "popup",
      eventAction: "hmspecialvoucher - click",
      eventLabel: voucherName,
    }
    // Proceed only if dataLayer exists.
    if (dataLayer) {
      dataLayer.push(birthdayPopupClick);
    }
  }

  /**
   * This function is called when hello member welcome pop up is displayed.
   */
  Drupal.alshayaSeoGtmPushHmAutoEnrollView = function () {
    // Prepare the Popup Data.
    var popupData = {
      event: 'popup',
      eventAction: 'hmAutoEnroll - view',
      eventCategory: 'pop-up',
    }
    // Proceed only if dataLayer exists.
    if (dataLayer) {
      dataLayer.push(popupData);
    }
  }

  /**
   * This function is called when user clics on contact us on popup.
   */
  Drupal.alshayaSeoGtmPusHmAutoEnrollClickContact = function () {
    // Prepare the contact us onclick data.
    var contactUsClick = {
      event: 'popup',
      eventAction: 'hmAutoEnroll-click-contact_us',
      eventCategory: 'pop-up',
    }
    // Proceed only if dataLayer exists.
    if (dataLayer) {
      dataLayer.push(contactUsClick);
    }
  }

})(jQuery, Drupal, dataLayer);
