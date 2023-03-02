/**
 * @file
 * JS code to integrate GTM with DY.
 */

(function ($, Drupal, debounce) {
  Drupal.behaviors.dyBanners = {
    attach: function () {
      var gtmPageType = $('body').attr('gtm-container');
      // DY banners promotions impression/click events.
      var dyBanners = $('div.dy_unit');
      // Create an array to store all dy banners promotions data on
      // page load itself so that later we can fetch these values for
      // promotion impression and promotion click events.
      var dyBannersPromotions = [];
      if (dyBanners.length) {
        dyBannersPromotions = Drupal.prepareDyBannersPromotionsData(dyBanners, gtmPageType);
        if (dyBannersPromotions.length) {
          Drupal.alshayaSeoGtmPushDyPromotionEvents('promotionImpression', Drupal.fetchDyBannersPromotionsImpression(dyBannersPromotions));
        }
      }

      // Tracking dy banners promotion impression on scroll.
      $(window).once('alshaya-seo-dy-banners-promotion-impression').on('scroll', debounce(function (event) {
        if (dyBannersPromotions.length) {
          Drupal.alshayaSeoGtmPushDyPromotionEvents('promotionImpression', Drupal.fetchDyBannersPromotionsImpression(dyBannersPromotions));
        }
      }, 500));

      /**
       * Tracking dy banners promotion clicks.
       */
      dyBanners.once('alshaya-seo-dy-banners-promotion-click').on('click', function () {
        // Get the clicked dy banner id.
        var bannerId = $(this).find('div[id^="dybanner_"]').attr('id');
        var promotions = [];
        dyBannersPromotions.every(promotion => {
          // Get promotion data for the banner clicked.
          if (bannerId !== promotion.bannerId) {
            // Continue with the loop.
            return true;
          }
          var promotionData = {
            id : promotion.id,
            name: promotion.name,
            creative: promotion.creative,
            position: promotion.position,
          };
          promotions.push(promotionData);
        });
        Drupal.alshayaSeoGtmPushDyPromotionEvents('promotionClick', promotions);
      });
    }
  };

  /**
   * Helper function to prepare dy banners promotion data.
   *
   * @param {Object} dyBanners
   *   The list of all the dy banners element on the page.
   * @param {string} gtmPageType
   *   Type of the page.
   *
   * @returns {Array}
   *   Array of promotions.
   */
  Drupal.prepareDyBannersPromotionsData = function (dyBanners, gtmPageType) {
    var dyPromotions = [];
    var dyPromotionCounter = 1;
    // Iterate over DY banners elements to prepare
    // promotions data.
    dyBanners.each(function () {
      var promotionData = {};
      var bannerElement = $(this).find('div[id^="dybanner_"]');
      // Skip if id starting with 'dybanner_' is not found.
      if (!bannerElement.length) {
        return;
      }
      var promotionId = '';
      var bannerTextSelectors = ['h1', 'h2', 'h3'];
      // Add banner heading text in 'id' field.
      // Use 'h2' if 'h1' is empty if both 'h1' & 'h2' are empty
      // or not available use 'h3'.
      bannerTextSelectors.every(selector => {
        if (!Drupal.hasValue(bannerElement.find(selector).text())) {
          // Continue to next iteration.
          return true;
        }
        promotionId = bannerElement.find(selector).text();
      });
      promotionData.id = promotionId;
      // Add page type in 'name' field.
      promotionData.name = gtmPageType;
      // Prepare data for creative field.
      var bannerId = bannerElement.attr('id');
      var bannerClass = Drupal.hasValue(bannerElement.attr('class'))
      ? bannerElement.attr('class').split(' ')[0]
      : '';
      // Creative field requires the id and first class of the
      // selector separated by pipe symbol - 'id|class'
      // Eg: 'dybanner_1234_en|dy-tb-box'.
      var creative = Drupal.hasValue(bannerClass)
      ? `${bannerId}|${bannerClass}`
      : bannerId;
      promotionData.creative = creative;
      promotionData.position = dyPromotionCounter;
      promotionData.bannerId = bannerId;
      // impressionProcessed field is used to determine if
      // promotion impression is pushed or not.
      promotionData.impressionProcessed = false;
      dyPromotions.push(promotionData);
      dyPromotionCounter++;
    });
    return dyPromotions;
  }

  /**
   * Helper function to fetch promotion impressions for dy banners.
   *
   * @param {Array} promotions
   *   Array of promotions for all dy banners available in the page.
   *
   * @returns {Array}
   *   Array of promotion impressions that are visible.
   */
  Drupal.fetchDyBannersPromotionsImpression = function (promotions) {
    var promotionImpressions = [];
    promotions.forEach(promotion => {
      // If promotion impression is not tracked for this banner
      //  and banner is visible to user.
      if (!promotion.impressionProcessed
        && $('#' + promotion.bannerId).isElementInViewPort(0,0)) {
          var promotionImpression = {
            id : promotion.id,
            name: promotion.name,
            creative: promotion.creative,
            position: promotion.position,
          };
        // Update impressionProcessed property for promotions
        // to avoid firing this again.
        var index = promotions.findIndex(
          item => item.bannerId === promotion.bannerId
        );
        promotions[index].impressionProcessed = true;
        promotionImpressions.push(promotionImpression);
      }
    });
    return promotionImpressions;
  }

  /**
   * Helper function to push DY promotion impression & click events to GTM.
   *
   * @param {string} eventName
   *   Name of the event.
   * @param {Array} promotions
   *   Promotion impression/click data for GTM.
   */
  Drupal.alshayaSeoGtmPushDyPromotionEvents = function (eventName, promotions) {
    if (!promotions.length > 0) {
      return;
    }
    if (eventName == 'promotionClick') {
      var data = {
        event: eventName,
        ecommerce: {
          promoClick: {
            promotions: promotions
          }
        }
      };
    } else {
      var data = {
        event: eventName,
        ecommerce: {
          promoView: {
            promotions: promotions
          }
        }
      };
    }

    dataLayer.push(data);
  }

})(jQuery, Drupal, Drupal.debounce);
