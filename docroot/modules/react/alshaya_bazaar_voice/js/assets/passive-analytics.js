/**
 * @file
 * Contains all passive analytics events for bazaarvoice.
 */

(function ($, Drupal) {
    'use strict';

    Drupal.alshayaBazaarvoiceAnalytics = Drupal.alshayaBazaarvoiceAnalytics || {};

    /**
     * Helper function to push complete page view to analytics.
     *
     * @param reviewData
     * @param productId
     */
    Drupal.alshayaBazaarvoiceAnalytics.trackPageView = function (productStats) {
        var pageViewData = {
            bvProduct: 'RatingsAndReviews',
            productId: productStats.Id,
            brand: productStats.Brand.Name,
            type: 'Product',
            categoryId: productStats.CategoryId,
            numReviews: productStats.ReviewStatistics.TotalReviewCount,
            avgRating: productStats.ReviewStatistics.AverageOverallRating,
            percentRecommended: (productStats.ReviewStatistics.RecommendedCount/productStats.ReviewStatistics.TotalReviewCount) * 100,
        }
        BV.pixel.trackPageView(pageViewData);
    };

    /**
     * Helper function to push each content to analytics data.
     *
     * @param reviewData
     * @param productId
     */
    Drupal.alshayaBazaarvoiceAnalytics.trackImpression = function (reviewData, productStats) {
        Object.values(reviewData.Results).forEach((content) => {
            pushContentToBVAnalytics(content, 'review', productStats);
        });
        Object.values(reviewData.Includes.Comments).forEach((content) => {
            pushContentToBVAnalytics(content, 'comment', productStats);
        });
    };

    /**
     * Helper function to push page view data to CGC analytics.
     *
     * @param inViewData
     * @param containerId
     */
    Drupal.alshayaBazaarvoiceAnalytics.trackInView = function (inViewData, containerId) {
        BV.pixel.trackInView(inViewData, {
            minPixels: 250,
            containerId: containerId
        })
    };

    /**
     * Helper function to push page view data visible for a set amount of time.
     * 
     * @param inViewData
     * @param containerId
     */
    Drupal.alshayaBazaarvoiceAnalytics.trackViewedCGC = function (inViewData, containerId) {
        BV.pixel.trackViewedCGC(inViewData, {
            minPixels: 250,
            minTime: 2500,
            containerId: containerId
        })
     };

    /**
     * Helper function to push content data to bv analytics.
     *
     * @param content
     * @param contentType
     * @param productId
     */
    function pushContentToBVAnalytics(content,contentType, productStats) {
        BV.pixel.trackImpression({
            contentId: content.Id,
            productId: productStats.Id,
            categoryId: productStats.CategoryId,
            contentType: contentType,
            bvProduct: 'RatingsAndReviews',
            brand: productStats.Brand.Name,
        });
    }

    // Process review data as soon as all reviews load on the screen.
    document.addEventListener('bvReviewsTracking', function (e) {
        var productId = drupalSettings.productReviewStats.productId;
        var productStats = drupalSettings.productReviewStats.statistics[productId];
        var containerId = 'reviews-section';

        // This method communicates data specific to the product page
        Drupal.alshayaBazaarvoiceAnalytics.trackPageView(productStats);

        // This method communicates the various pieces of consumer 
        // generated content on a given page back to Bazaarvoice.
        Drupal.alshayaBazaarvoiceAnalytics.trackImpression(e.detail, productStats);
        
        // Prepare in view data for track view and CGC events.
        var inViewData = {
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            brand: productStats.Brand.Name,
        };

        // This method is triggered when consumer-generated content 
        // is first made visible in the browsers viewport.
        Drupal.alshayaBazaarvoiceAnalytics.trackInView(inViewData, containerId);

        // This method is is triggered when consumer-generated content
        // is made visible for a set amount of time.
        Drupal.alshayaBazaarvoiceAnalytics.trackViewedCGC(inViewData, containerId);

    });
    
})(jQuery, Drupal);
