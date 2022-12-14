/**
 * @file
 * Custom js file.
 */

(function ($, Drupal) {

  document.addEventListener('DOMContentLoaded', function () {
    var imageObserver = new IntersectionObserver((entries, imgObserver) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          var lazyImage = entry.target
          // Check if data-src value is present.
          if (Drupal.hasValue(lazyImage.dataset)
            && Drupal.hasValue(lazyImage.dataset.src)) {
            lazyImage.src = lazyImage.dataset.src
          }
        }
      })
    });
    // Filter out all the img tags having data-src attribute.
    var arr = jQuery('img').once('rcs-lazy-load').filter(function () {
      return jQuery(this).attr('data-src') && $(this).attr('data-src') != "";
    });
    arr.map((index, item) => {
      imageObserver.observe(item);
    });
  });
})(jQuery, Drupal);
