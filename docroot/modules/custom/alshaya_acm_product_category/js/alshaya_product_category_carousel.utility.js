window.commerceBackend = window.commerceBackend || {};

/**
 * Fetches the data required for building the carousel.
 *
 * @returns {Object}
 *   The data required for builing the carousel.
 */
window.commerceBackend.getCarouselData = function (categoryId) {
  return drupalSettings.alshayaProductCarousel[categoryId];
}
