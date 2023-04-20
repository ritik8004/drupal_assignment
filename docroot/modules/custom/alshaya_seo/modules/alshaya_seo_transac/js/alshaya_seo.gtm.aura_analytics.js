/**
 * @file
 * JS code to integrate Aura with GTM.
 */

 (function ($, Drupal, dataLayer, drupalSettings) {

   // GTM values to be used for Aura Analytics.
   const GTM_AURA_VALUES = {
     NON_AURA : 'non aura', // Used for anonymous users.
     AURA_USER : 'aura user', // Used for email matching but not aura linked users.
     AURA_DEFAULT_TIER : 'Tier1', // Default Aura Tier for authenticated non-aura users.
     AURA_QUICK_ENROLLED : 'quick enrolled', // Aura enrollment status for Tier1 users.
     AURA_FULL_ENROLLED : 'full enrolled', // Aura enrollment status for users above Tier1.
     AURA_POINTS_EMPTY : 'points empty', // Aura points is 0.
     AURA_POINTS_PRESENT : 'points present', // Aura points > 0.
     AURA_POINTS_REDEEMED : 'redeemed', // Aura points redeemed during checkout.
     AURA_POINTS_NOT_REDEEMED : 'not redeemed', // Aura points not redeemed during checkout.
     AURA_BALANCE_MORE_THAN_ORDER_VALUE : 'balPoints > orderValue', // Total Aura points worth money > Order value.
     AURA_BALANCE_LESS_THAN_ORDER_VALUE : 'balPoints < orderValue', // Total Aura points worth money < Order value.
     AURA_EVENT_NAME : 'aura', // Aura event name.
     AURA_EVENT_CATEGORY : 'aura', // Aura event category name.
     AURA_EVENT_ACTION_USE_POINTS : 'use points', // Aura event action name when user redeems aura points during purchase in checkout page.
     AURA_EVENT_ACTION_REMOVE_POINTS : 'remove points', // Aura event action name when user removes aura points during purchase in checkout page.
     AURA_EVENT_ACTION_CLICK_APPSTORE : 'click appstore', // Aura event action name when user clicks on download IOS app button.
     AURA_EVENT_ACTION_CLICK_PLAYSTORE : 'click playstore', // Aura event action name when user clicks on download Android app button.
     AURA_EVENT_ACTION_SIGN_UP : 'SignUp_join aura', // Aura event action name when user clicks on Sign Up Aura button.
     AURA_EVENT_ACTION_SIGN_IN_ALREADY_MEMBER : 'SignIn_already a member', // Aura event action name when user clicks on Already a member button.
     AURA_EVENT_ACTION_SIGN_IN_NOT_YOU : 'SignIn_not you', // Aura event action name when user clicks on Sign in button.
     AURA_EVENT_ACTION_LINK_YOUR_CARD : 'link your card', // Aura event action name when user clicks on Link Aura account button.
   };

   /**
    * This function prepares aura common details dataset to be added
    * to gtm checkout steps event from window spcStaticStorage
    * object which is created from alshaya_spc module.
    */
   Drupal.alshayaSeoGtmPrepareAuraCommonDataFromCart = function () {
     // Prepare the aura dataset.
     var gtmData = Drupal.getItemFromLocalStorage('gtm_aura_common_data');
     var rawCartData = window.commerceBackend.getRawCartDataFromStorage();
     try {
       var userAPCDetails = rawCartData.customer.custom_attributes;
       if (gtmData.aura_Status !== undefined && gtmData.aura_Status !== GTM_AURA_VALUES.NON_AURA && typeof userAPCDetails !== 'undefined') {
         // These values will be used for Aura signed up anonymous users
         // and logged-in users but not using Aura.
         gtmData.aura_Status = gtmData.aura_Status === GTM_AURA_VALUES.AURA_USER ? GTM_AURA_VALUES.AURA_USER : drupalSettings.aura.gtmAllAuraTier.shortValue[GTM_AURA_VALUES.AURA_DEFAULT_TIER].toLowerCase();
         gtmData.aura_enrollmentStatus = GTM_AURA_VALUES.AURA_QUICK_ENROLLED;
         var auraTier = userAPCDetails.filter(item => item.attribute_code === 'tier_code');
         if (Drupal.hasValue(auraTier)) {
           // These values will be used for logged-in users using Aura.
           auraTier = auraTier[0].value;
           gtmData.aura_Status = gtmData.aura_Status === GTM_AURA_VALUES.AURA_USER ? GTM_AURA_VALUES.AURA_USER : drupalSettings.aura.gtmAllAuraTier.shortValue[auraTier].toLowerCase();
           gtmData.aura_enrollmentStatus = auraTier === GTM_AURA_VALUES.AURA_DEFAULT_TIER ? GTM_AURA_VALUES.AURA_QUICK_ENROLLED : GTM_AURA_VALUES.AURA_FULL_ENROLLED;
           var auraPoints = userAPCDetails.filter(item => item.attribute_code === 'apc_points')[0].value;
           gtmData.aura_balStatus = auraPoints > 0 ? GTM_AURA_VALUES.AURA_POINTS_PRESENT : GTM_AURA_VALUES.AURA_POINTS_EMPTY;
           gtmData.aura_pointsTotal = auraPoints;
         }
       }
     }
     catch (e) {
       Drupal.logJavascriptError('error-prepare-aura-data-from-cart', e);
     }

     return gtmData;
   };

  /**
   * This function is called when pushing aura common details
   * to gtm data event in all pages except Checkout page.
   */
  Drupal.alshayaSeoGtmPushAuraCommonData = function (data, loyaltyStatus = null, pushDatalayer = true) {
    // Prepare the aura dataset.
    var gtmData = {};

    /**
     * 4 cases considered in following conditions:
     *  - anonymous users and aura not signed in
     *  - anonymous users but aura signed in
     *  - logged-in users but not using aura
     *  - logged-in users using aura
     */
    try {
      if (data.nonAura) {
        gtmData.aura_Status = gtmData.aura_enrollmentStatus = GTM_AURA_VALUES.NON_AURA;
      } else {
        data.tier = typeof data.tier === 'undefined' ? GTM_AURA_VALUES.AURA_DEFAULT_TIER : data.tier;
        // For users with matching email but not linked aura status should be 'aura user'.
        gtmData.aura_Status = loyaltyStatus === drupalSettings.aura.allAuraStatus.APC_NOT_LINKED_DATA ? GTM_AURA_VALUES.AURA_USER : drupalSettings.aura.gtmAllAuraTier.shortValue[data.tier].toLowerCase();
        gtmData.aura_enrollmentStatus = data.tier === GTM_AURA_VALUES.AURA_DEFAULT_TIER ? GTM_AURA_VALUES.AURA_QUICK_ENROLLED : GTM_AURA_VALUES.AURA_FULL_ENROLLED;
      }

      if (typeof data.points !== 'undefined' && data.points > 0) {
        gtmData.aura_balStatus = GTM_AURA_VALUES.AURA_POINTS_PRESENT;
        gtmData.aura_pointsTotal = data.points;
      } else {
        gtmData.aura_balStatus = GTM_AURA_VALUES.AURA_POINTS_EMPTY;
        gtmData.aura_pointsTotal = 0;
      }

      // Adding Aura common details in localstorage to use in all Aura events.
      // Check for events "event name: aura".
      Drupal.addItemInLocalStorage('gtm_aura_common_data', gtmData);

      // Proceed only if dataLayer exists.
      if (dataLayer && pushDatalayer) {
        dataLayer.push(gtmData);
      }
    }
    catch (e) {
      Drupal.logJavascriptError('error-push-aura-data-gtm-all-pages', e);
    }
  };

   /**
    * This function prepares aura details dataset to be added
    * to gtm checkout step 3 and 4 event specifically.
    */
   Drupal.alshayaSeoGtmPrepareAuraCheckoutStepDataFromCart = function (cartData) {
     // Prepare the aura dataset.
     var gtmData = {};
     // These values will be used for anonymous users.
     gtmData.aura_balRedemption = gtmData.aura_balPointsVSorderValue = GTM_AURA_VALUES.NON_AURA;
     gtmData.aura_pointsUsed = 0;
     const auraBlock = $('.spc-aura-checkout-rewards-block');
     // Aura points earned should only be available for Aura users.
     gtmData.aura_pointsEarned = auraBlock.attr('data-earn-aura-points') !== undefined
       && auraBlock.attr('data-aura-loyalty-status') !== undefined
       && parseInt(auraBlock.attr('data-aura-loyalty-status'))
       ? parseInt(auraBlock.attr('data-earn-aura-points')) : 0;
     try {
       // User's Aura status.
       var auraStatus = Drupal.getItemFromLocalStorage('gtm_aura_common_data') ? Drupal.getItemFromLocalStorage('gtm_aura_common_data').aura_Status : null;
       if (Drupal.hasValue(auraStatus) && auraStatus !== GTM_AURA_VALUES.NON_AURA) {
         // These values will be used for Aura signed up anonymous users
         // and logged-in users but not using Aura.
         gtmData.aura_balRedemption = GTM_AURA_VALUES.AURA_POINTS_NOT_REDEEMED;
         gtmData.aura_balPointsVSorderValue = GTM_AURA_VALUES.AURA_BALANCE_LESS_THAN_ORDER_VALUE;
         var rawCartData = window.commerceBackend.getRawCartDataFromStorage();
         var userAPCDetails = rawCartData.customer.custom_attributes;
         if (typeof userAPCDetails !== 'undefined' && Drupal.hasValue(userAPCDetails.filter(item => item.attribute_code === 'tier_code'))) {
           // These values will be used for logged-in users using Aura.
           if (typeof cartData.totals.paidWithAura !== 'undefined' && cartData.totals.paidWithAura > 0) {
             gtmData.aura_balRedemption = GTM_AURA_VALUES.AURA_POINTS_REDEEMED;
             gtmData.aura_pointsUsed = $('.successful-redeem-msg').attr('data-aura-points-used');
           }
           if (typeof $('.spc-aura-highlight').attr('data-aura-money') !== 'undefined') {
             gtmData.aura_balPointsVSorderValue = $('.spc-aura-highlight').attr('data-aura-money') > cartData.totals.base_grand_total
               ? GTM_AURA_VALUES.AURA_BALANCE_MORE_THAN_ORDER_VALUE
               : GTM_AURA_VALUES.AURA_BALANCE_LESS_THAN_ORDER_VALUE;
           }
         }
         // Adding Aura common details in localstorage to use in all Aura events.
         Drupal.addItemInLocalStorage('gtm_aura_balpoints_vs_order', gtmData.aura_balPointsVSorderValue);
       }
     }
     catch (e) {
       Drupal.logJavascriptError('error-prepare-aura-data-from-cart-for-checkout-step-3-4', e);
     }

     return gtmData;
   };

  /**
    * This function pushes aura event details data to datalayer.
  */
  Drupal.alshayaSeoGtmPushAuraEventData = function (data) {
    try {
      var auraGTMData = {};
      if (data.action !== undefined || data.label !== undefined) {
        auraGTMData['event'] = GTM_AURA_VALUES.AURA_EVENT_NAME;
        auraGTMData['eventCategory'] = GTM_AURA_VALUES.AURA_EVENT_CATEGORY;
        auraGTMData['eventAction'] = data.action !== undefined && GTM_AURA_VALUES[data.action] !== undefined ? GTM_AURA_VALUES[data.action] : null;
        auraGTMData['eventLabel'] = data.label !== undefined ? data.label : null;
      }

      if (dataLayer && Object.keys(auraGTMData).length !== 0) {
        dataLayer.push(auraGTMData);
      }
    }
    catch (e) {
      Drupal.logJavascriptError('error-push-aura-events', e);
    }
  };

})(jQuery, Drupal, dataLayer, drupalSettings);
