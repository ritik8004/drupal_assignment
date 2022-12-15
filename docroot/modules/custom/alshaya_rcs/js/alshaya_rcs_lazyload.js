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
            // We don't want the same element to be observed again when it
            // appears again in the view port, if we will not do this then
            // browser will again try to load the same image.
            imgObserver.unobserve(lazyImage);
          }
        }
      })
    });
    // Filter out all the img tags having data-src attribute.
    jQuery('img[data-src]').once('rcs-lazy-load').each(function (index, item) {
      if ($(this).attr('data-src') != '') {
        imageObserver.observe(item);
      }
    });
  });
})(jQuery, Drupal);
