/**
 * @file
 * Contains analytics and datalayer events for bazaarvoice.
 */
const { productId } = drupalSettings.productReviewStats;
const productStats = drupalSettings.productReviewStats.statistics[productId];

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
 * @param productId
 */
function pushContentToBVAnalytics(content, contentType) {
  const inViewData = {
    contentId: content.Id,
    productId: productStats.Id,
    categoryId: productStats.CategoryId,
    contentType,
    bvProduct: 'RatingsAndReviews',
    brand: productStats.Brand.Name,
  };
  // eslint-disable-next-line
  BV.pixel.trackImpression(inViewData);
}

/**
 * Helper function to push complete page view to analytics.
 *
 * @param productStats
 */
function trackPageView() {
  const pageViewData = {
    bvProduct: 'RatingsAndReviews',
    productId: productStats.Id,
    brand: productStats.Brand.Name,
    type: 'Product',
    categoryId: productStats.CategoryId,
    numReviews: productStats.ReviewStatistics.TotalReviewCount,
    avgRating: productStats.ReviewStatistics.AverageOverallRating,
    percentRecommended: (productStats.ReviewStatistics.RecommendedCount
        / productStats.ReviewStatistics.TotalReviewCount) * 100,
  };
  // eslint-disable-next-line
  BV.pixel.trackPageView(pageViewData);
  pushContentToDataLayer('trackPageView', pageViewData);
}

/**
 * Helper function to push each content to analytics data.
 *
 * @param reviewData
 */
function trackImpression(reviewData) {
  if (reviewData.Results && Object.keys(reviewData.Results).length > 0) {
    Object.values(reviewData.Results).forEach((content) => {
      pushContentToBVAnalytics(content, 'review');
    });
  }
  if (reviewData.Includes.Comments && Object.keys(reviewData.Includes.Comments).length > 0) {
    Object.values(reviewData.Includes.Comments).forEach((content) => {
      pushContentToBVAnalytics(content, 'comment');
    });
  }
}

/**
 * Helper function to push page view data to CGC analytics.
 *
 * @param inViewData
 * @param containerId
 */
function trackInView(inViewData, containerId) {
  // eslint-disable-next-line
  BV.pixel.trackInView(inViewData, {
    minPixels: 250,
    containerId,
  });
  pushContentToDataLayer('trackInView', inViewData);
}

/**
 * Helper function to push page view data visible for a set amount of time.
 *
 * @param inViewData
 * @param containerId
 */
function trackViewedCGC(inViewData, containerId) {
  // eslint-disable-next-line
  BV.pixel.trackViewedCGC(inViewData, {
    minPixels: 250,
    minTime: 2500,
    containerId,
  });
  pushContentToDataLayer('trackViewedCGC', inViewData);
}

/**
 * Function to track all the passive analytics of BV.
 *
 * @param reviewData
 */
export const trackPassiveAnalytics = (reviewData) => {
  const containerId = 'reviews-section';

  // This method communicates data specific to the product page
  trackPageView();

  // This method communicates the various pieces of consumer
  // generated content on a given page back to Bazaarvoice.
  trackImpression(reviewData);

  // Prepare in view data for track view and CGC events.
  const inViewData = {
    productId,
    bvProduct: 'RatingsAndReviews',
    brand: productStats.Brand.Name,
  };

  // This method is triggered when consumer-generated content
  // is first made visible in the browsers viewport.
  trackInView(inViewData, containerId);

  // This method is is triggered when consumer-generated content
  // is made visible for a set amount of time.
  trackViewedCGC(inViewData, containerId);
};

/**
 * Function to track all the featured analytics of BV.
 *
 * @param productStats
 */
export const trackFeaturedAnalytics = (analyticsData) => {
  const eventData = {
    type: analyticsData.type,
    name: analyticsData.name,
    brand: productStats.Brand.Name,
    productId,
    bvProduct: 'RatingsAndReviews',
    categoryId: productStats.CategoryId,
    detail1: analyticsData.detail1,
    detail2: analyticsData.detail2,
  };
  // eslint-disable-next-line
  BV.pixel.trackEvent('Feature', eventData);
  pushContentToDataLayer('bvReviewsFeature', eventData);
};

export default {
  trackPassiveAnalytics,
  trackFeaturedAnalytics,
};
