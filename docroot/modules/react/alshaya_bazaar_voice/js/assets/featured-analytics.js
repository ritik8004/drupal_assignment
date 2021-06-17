/**
 * @file
 * Contains all featured analytics events for bazaarvoice.
 */

(function ($, Drupal) {
    'use strict';

    Drupal.alshayaBVFeaturedAnalytics = Drupal.alshayaBVFeaturedAnalytics || {};
    var productId = drupalSettings.productReviewStats.productId;
    var productStats = drupalSettings.productReviewStats.statistics[productId];

    Drupal.alshayaBVFeaturedAnalytics.trackFeaturedEvent = function (eventData) {
        BV.pixel.trackEvent('Feature', eventData);
    }

    // Process write review click data as user clicks on button.
    document.addEventListener('bvWriteReviewClick', function (e) {
        const eventData = {
            type: 'Used',
            name: 'write',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: e.detail.detail1,
            detail2: e.detail.detail2,
        }
        Drupal.alshayaBVFeaturedAnalytics.trackFeaturedEvent(eventData);
    });

    // Process positive feedback click data as user clicks on yes.
    document.addEventListener('bvPositiveHelpfulnessClick', function (e) {
        const eventData = {
            type: 'Used',
            name: 'helpfulness',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: e.detail.detail1,
            detail2: e.detail.detail2,
        }
        Drupal.alshayaBVFeaturedAnalytics.trackFeaturedEvent(eventData);
    });

    // Process negative feedback click data as user clicks on no.
    document.addEventListener('bvNegativeHelpfulnessClick', function (e) {
        const eventData = {
            type: 'Used',
            name: 'helpfulness',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: e.detail.detail1,
            detail2: e.detail.detail2,
        }
        Drupal.alshayaBVFeaturedAnalytics.trackFeaturedEvent(eventData);
    });

    // Process report feedback click data as user clicks on report.
    document.addEventListener('bvReportFeedbackClick', function (e) {
        const eventData = {
            type: 'Used',
            name: 'report',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: e.detail.detail1,
            detail2: e.detail.detail2,
        }
        Drupal.alshayaBVFeaturedAnalytics.trackFeaturedEvent(eventData);
    });

    // Process rating details click data as user clicks on filter option.
    document.addEventListener('bvRatingFilterClick', function (e) {
        const eventData = {
            type: 'Used',
            name: 'filter',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: 'stars',
            detail2: e.detail.value.split(':')[1],
        }
        Drupal.alshayaBVFeaturedAnalytics.trackFeaturedEvent(eventData);
    });

    // Process filter details click data as user clicks on filter option.
    document.addEventListener('bvReviewFilterClick', function (e) {
        const eventData = {
            type: 'Used',
            name: 'filter',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: e.detail.value,
            detail2: 'true',
        }
        Drupal.alshayaBVFeaturedAnalytics.trackFeaturedEvent(eventData);
    });

    // Process review count click as user clicks on count link.
    document.addEventListener('bvReviewCountClick', function (e) {
        const eventData = {
            type: 'Used',
            name: 'link',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: e.detail.detail1,
            detail2: e.detail.detail2,
        }
        Drupal.alshayaBVFeaturedAnalytics.trackFeaturedEvent(eventData);
    });

    // Process sort click data as user clicks on sort option.
    document.addEventListener('bvSortOptionsClick', function (e) {
        const eventData = {
            type: 'Used',
            name: 'sort',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: e.detail.value.split(':')[0],
            detail2: ''
        }
        Drupal.alshayaBVFeaturedAnalytics.trackFeaturedEvent(eventData);
    });
    
    // Process review submit click data as user submits a review.
    document.addEventListener('bvReviewSubmissionClick', function (e) {
        const eventData = {
            type: 'Used',
            name: 'submit',
            brand: productStats.Brand.Name,
            productId: productId,
            bvProduct: 'RatingsAndReviews',
            categoryId: productStats.CategoryId,
            detail1: e.detail.detail1,
            detail2: e.detail.detail2,
        }
        Drupal.alshayaBVFeaturedAnalytics.trackFeaturedEvent(eventData);
    });
    
})(jQuery, Drupal);
