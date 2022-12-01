/**
 * @file
 * JS code to integrate Aura with GTM.
 */

 (function ($, Drupal, dataLayer, drupalSettings) {

   // GTM values to be used for Aura Analytics.
   const GTM_AURA_VALUES = {
     NON_AURA : 'non aura', // Used for anonymous users.
     AURA_DEFAULT_TIER : 'Tier1', // Default Aura Tier for authenticated non-aura users.
     AURA_QUICK_ENROLLED : 'quick enrolled', // Aura enrollment status for Tier1 users.
     AURA_FULL_ENROLLED : 'full enrolled', // Aura enrollment status for users above Tier1.
     AURA_POINTS_EMPTY : 'points empty', // Aura points is 0.
     AURA_POINTS_PRESENT : 'points present', // Aura points > 0.
   };

   /**
    * This function prepares aura common details dataset to be added
    * to gtm checkout steps event from window spcStaticStorage
    * object which is created from alshaya_spc module.
    */
   Drupal.alshayaSeoGtmPrepareAuraCommonDataFromCart = function () {
     // Prepare the aura dataset.
     var gtmData = {};
     // These values will be used for anonymous users.
     gtmData.aura_Status = gtmData.aura_enrollmentStatus = GTM_AURA_VALUES.NON_AURA;
     gtmData.aura_balStatus = GTM_AURA_VALUES.AURA_POINTS_EMPTY;
     gtmData.aura_pointsTotal = 0;
     try {
       var userAPCDetails = window.spcStaticStorage.cart_raw.customer.custom_attributes;

       if (drupalSettings.userDetails.userID > 0 && typeof userAPCDetails !== 'undefined') {
         // These values will be used for logged-in users but not using Aura.
         gtmData.aura_Status = drupalSettings.aura.allAuraTier.shortValue[GTM_AURA_VALUES.AURA_DEFAULT_TIER].toLowerCase();
         gtmData.aura_enrollmentStatus = GTM_AURA_VALUES.AURA_QUICK_ENROLLED;
         var auraTier = userAPCDetails.filter(item => item.attribute_code === 'tier_code');
         if (Drupal.hasValue(auraTier)) {
           // These values will be used for logged-in users using Aura.
           auraTier = auraTier[0].value;
           gtmData.aura_Status = drupalSettings.aura.allAuraTier.shortValue[auraTier].toLowerCase();
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
  Drupal.alshayaSeoGtmPushAuraCommonData = function (data) {
    // Prepare the aura dataset.
    var gtmData = {};

    /**
     * 3 cases considered in following conditions:
     *  - anonymous users
     *  - logged-in users but not using aura
     *  - logged-in users using aura
     */
    try {
      if (drupalSettings.userDetails.userID === 0) {
        gtmData.aura_Status = gtmData.aura_enrollmentStatus = GTM_AURA_VALUES.NON_AURA;
      } else {
        data.tier = typeof data.tier === 'undefined' ? GTM_AURA_VALUES.AURA_DEFAULT_TIER : data.tier;
        gtmData.aura_Status = drupalSettings.aura.allAuraTier.shortValue[data.tier].toLowerCase();
        gtmData.aura_enrollmentStatus = data.tier === GTM_AURA_VALUES.AURA_DEFAULT_TIER ? GTM_AURA_VALUES.AURA_QUICK_ENROLLED : GTM_AURA_VALUES.AURA_FULL_ENROLLED;
      }

      if (typeof data.points !== 'undefined' && data.points > 0) {
        gtmData.aura_balStatus = GTM_AURA_VALUES.AURA_POINTS_PRESENT;
        gtmData.aura_pointsTotal = data.points;
      } else {
        gtmData.aura_balStatus = GTM_AURA_VALUES.AURA_POINTS_EMPTY;
        gtmData.aura_pointsTotal = 0;
      }

      // Proceed only if dataLayer exists.
      if (dataLayer) {
        dataLayer.push(gtmData);
      }
    }
    catch (e) {
      Drupal.logJavascriptError('error-push-aura-data-gtm-all-pages', e);
    }
  };

})(jQuery, Drupal, dataLayer, drupalSettings);
