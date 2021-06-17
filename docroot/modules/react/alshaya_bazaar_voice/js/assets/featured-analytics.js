/**
 * @file
 * Contains all featured analytics events for bazaarvoice.
 */

(function ($, Drupal) {
    'use strict';

    Drupal.alshayaBazaarvoiceAnalytics = Drupal.alshayaBazaarvoiceAnalytics || {};

    // Process write review click data as user clicks on button.
    document.addEventListener('bvWriteReviewClick', function (e) {
        var productId = drupalSettings.productReviewStats.productId;
        var productStats = drupalSettings.productReviewStats.statistics[productId];
        BV.pixel.trackEvent('Feature', {
            type: 'Used',
            name: 'write',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: e.detail.detail1,
            detail2: e.detail.detail2,
        });
    });

    // Process positive feedback click data as user clicks on yes.
    document.addEventListener('bvPositiveHelpfulnessClick', function (e) {
        var productId = drupalSettings.productReviewStats.productId;
        var productStats = drupalSettings.productReviewStats.statistics[productId];
        BV.pixel.trackEvent('Feature', {
            type: 'Used',
            name: 'helpfulness',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: e.detail.detail1,
            detail2: e.detail.detail2,
        });
    });

    // Process negative feedback click data as user clicks on no.
    document.addEventListener('bvNegativeHelpfulnessClick', function (e) {
        var productId = drupalSettings.productReviewStats.productId;
        var productStats = drupalSettings.productReviewStats.statistics[productId];
        BV.pixel.trackEvent('Feature', {
            type: 'Used',
            name: 'helpfulness',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: e.detail.detail1,
            detail2: e.detail.detail2,
        });
    });

    // Process report feedback click data as user clicks on report.
    document.addEventListener('bvReportFeedbackClick', function (e) {
        var productId = drupalSettings.productReviewStats.productId;
        var productStats = drupalSettings.productReviewStats.statistics[productId];
        BV.pixel.trackEvent('Feature', {
            type: 'Used',
            name: 'report',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: e.detail.detail1,
            detail2: e.detail.detail2,
        });
    });

    // Process rating details click data as user clicks on filter option.
    document.addEventListener('bvRatingFilterClick', function (e) {
        var productId = drupalSettings.productReviewStats.productId;
        var productStats = drupalSettings.productReviewStats.statistics[productId];
        console.log(e);
        BV.pixel.trackEvent('Feature', {
            type: 'Used',
            name: 'filter',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: 'stars',
            detail2: e.detail.value.split(':')[1],
        });
    });

    // Process filter details click data as user clicks on filter option.
    document.addEventListener('bvReviewFilterClick', function (e) {
        var productId = drupalSettings.productReviewStats.productId;
        var productStats = drupalSettings.productReviewStats.statistics[productId];
        BV.pixel.trackEvent('Feature', {
            type: 'Used',
            name: 'filter',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: e.detail.value,
            detail2: 'true',
        });
    });

    // Process review count click as user clicks on count link.
    document.addEventListener('bvReviewCountClick', function (e) {
        var productId = drupalSettings.productReviewStats.productId;
        var productStats = drupalSettings.productReviewStats.statistics[productId];
        BV.pixel.trackEvent('Feature', {
            type: 'Used',
            name: 'link',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: e.detail.detail1,
            detail2: e.detail.detail2,
        });
    });

    // Process sort click data as user clicks on sort option.
    document.addEventListener('bvSortOptionsClick', function (e) {
        var productId = drupalSettings.productReviewStats.productId;
        var productStats = drupalSettings.productReviewStats.statistics[productId];
        BV.pixel.trackEvent('Feature', {
            type: 'Used',
            name: 'sort',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: e.detail.value.split(':')[0],
            detail2: ''
        });
    });
    
    // Process review submit click data as user submits a review.
    document.addEventListener('bvReviewSubmissionClick', function (e) {
        var productId = drupalSettings.productReviewStats.productId;
        var productStats = drupalSettings.productReviewStats.statistics[productId];
        BV.pixel.trackEvent('Feature', {
            type: 'Used',
            name: 'submit',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: e.detail.detail1,
            detail2: e.detail.detail2,
        });
    });
    
})(jQuery, Drupal);