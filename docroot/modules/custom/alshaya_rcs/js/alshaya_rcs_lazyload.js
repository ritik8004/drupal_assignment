/**
 * @file
 * Custom js file.
 */

(function ($, Drupal) {

  document.addEventListener('DOMContentLoaded', function () {
    const imageObserver = new IntersectionObserver((entries, imgObserver) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const lazyImage = entry.target
          // Check if data-src value is present.
          if (Drupal.hasValue(lazyImage.dataset)
            && Drupal.hasValue(lazyImage.dataset.src)) {
            lazyImage.src = lazyImage.dataset.src
          }
        }
      })
    });
    // Filter out all the img tags having data-src attribute.
    const arr = jQuery('img').once().filter(function () {
      return jQuery(this).attr('data-src') && $(this).attr('data-src') != "";
    });
    arr.map((index, item) => {
      imageObserver.observe(item);
    });
  });
})(jQuery, Drupal);
