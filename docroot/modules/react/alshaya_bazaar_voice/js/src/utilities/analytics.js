/**
 * @file
 * Contains analytics and datalayer events for bazaarvoice.
 */

const bvPixelUtility = (typeof drupalSettings.productReviewStats !== 'undefined' && typeof BV !== 'undefined')
  ? BV.pixel
  : null;

/**
 * Helper function to push content data to datalayer.
 *
 * @param eventName
 * @param contentData
 */
function pushContentToDataLayer(eventName, contentData) {
  window.dataLayer.push({
    event: eventName,
    details: contentData,
  });
}

/**
 * Helper function to push content data to bv analytics.
 *
 * @param content
 * @param contentType
 */
function pushContentToBVAnalytics(content, contentType, productData) {
  const inViewData = {
    contentId: content.Id,
    productId: productData.Id,
    categoryId: productData.CategoryId,
    categoryName: drupalSettings.productInfo[productData.Id].gtm_attributes.category,
    contentType,
    bvProduct: 'RatingsAndReviews',
    brand: productData.Brand.Name,
  };
  bvPixelUtility.trackImpression(inViewData);
}

/**
 * Helper function to push complete page view to analytics.
 */
function trackPageView(productData, productSku) {
  const pageViewData = {
    bvProduct: 'RatingsAndReviews',
    productId: productData.Id,
    brand: productData.Brand.Name,
    type: 'Product',
    categoryId: productData.CategoryId,
    categoryName: drupalSettings.productInfo[productSku].gtm_attributes.category,
    numReviews: productData.ReviewStatistics.TotalReviewCount,
    avgRating: Math.round(productData.ReviewStatistics.AverageOverallRating * 100) / 100,
    percentRecommended: Math.round((productData.ReviewStatistics.RecommendedCount
        / productData.ReviewStatistics.TotalReviewCount) * 100),
  };
  bvPixelUtility.trackPageView(pageViewData);
  pushContentToDataLayer('BV_trackPageView', pageViewData);
}

/**
 * Helper function to push each content to analytics data.
 *
 * @param reviewData
 */
export function trackContentImpression(reviewData) {
  if (drupalSettings.productReviewStats && bvPixelUtility !== null) {
    const { productData } = drupalSettings.productReviewStats;
    if (reviewData.Results && Object.keys(reviewData.Results).length > 0) {
      Object.values(reviewData.Results).forEach((content) => {
        pushContentToBVAnalytics(content, 'review', productData);
      });
    }
    if (reviewData.Includes.Comments && Object.keys(reviewData.Includes.Comments).length > 0) {
      Object.values(reviewData.Includes.Comments).forEach((content) => {
        pushContentToBVAnalytics(content, 'comment', productData);
      });
    }
  }
}

/**
 * Helper function to process datalayer push when element is visible in viewport.
 *
 * @param eventName
 * @param inViewData
 */
function processAnalyticsDatalayerPush(eventName, inViewData) {
  const reviewElement = document.getElementById('reviews-section');
  function reviewsListener(event) {
    event.preventDefault();
    if (window.scrollY > reviewElement.offsetTop) {
      pushContentToDataLayer(eventName, inViewData);
      window.removeEventListener('scroll', reviewsListener);
    }
  }
  window.addEventListener('scroll', reviewsListener);
}

/**
 * Helper function to push page view data to CGC analytics.
 *
 * @param inViewData
 * @param containerId
 */
function trackInView(inViewData, containerId) {
  bvPixelUtility.trackInView(inViewData, {
    minPixels: 250,
    containerId,
  });
  processAnalyticsDatalayerPush('BV_trackInView', inViewData);
}

/**
 * Helper function to push page view data visible for a set amount of time.
 *
 * @param inViewData
 * @param containerId
 */
function trackViewedCGC(inViewData, containerId) {
  bvPixelUtility.trackViewedCGC(inViewData, {
    minPixels: 250,
    minTime: 2500,
    containerId,
  });
  processAnalyticsDatalayerPush('BV_trackViewedCGC', inViewData);
}

/**
 * Function to track all the passive analytics of BV.
 *
 * @param reviewData
 */
export function trackPassiveAnalytics() {
  if (drupalSettings.productReviewStats && bvPixelUtility !== null) {
    const { productData, productSku } = drupalSettings.productReviewStats;
    const containerId = 'reviews-section';

    // This method communicates data specific to the product page
    trackPageView(productData, productSku);

    // Prepare in view data for track view and CGC events.
    const inViewData = {
      productId: productData.Id,
      bvProduct: 'RatingsAndReviews',
      brand: productData.Brand.Name,
    };

    // This method is triggered when consumer-generated content
    // is first made visible in the browsers viewport.
    trackInView(inViewData, containerId);

    // This method is is triggered when consumer-generated content
    // is made visible for a set amount of time.
    trackViewedCGC(inViewData, containerId);
  }
}

/**
 * Function to track all the featured analytics of BV.
 *
 * @param analyticsData
 */
export function trackFeaturedAnalytics(analyticsData) {
  if (drupalSettings.productReviewStats && bvPixelUtility !== null) {
    const { productData } = drupalSettings.productReviewStats;
    const eventData = {
      type: analyticsData.type,
      name: analyticsData.name,
      brand: productData.Brand.Name,
      productId: productData.Id,
      bvProduct: 'RatingsAndReviews',
      categoryId: productData.CategoryId,
      categoryName: drupalSettings.productInfo[productData.Id].gtm_attributes.category,
      detail1: analyticsData.detail1,
      detail2: analyticsData.detail2,
    };
    bvPixelUtility.trackEvent('Feature', eventData);
    pushContentToDataLayer(`BV_feature${analyticsData.name}Click`, eventData);
  }
}

export default {
  trackPassiveAnalytics,
  trackFeaturedAnalytics,
  trackContentImpression,
};
