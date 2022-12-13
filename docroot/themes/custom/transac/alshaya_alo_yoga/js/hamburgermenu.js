/**
 * @file
 * Custom js file.
 */

(function (Drupal) {

  Drupal.behaviors.aloHamburgerMenu = {
    attach: function (context, settings) {
      document.addEventListener("DOMContentLoaded", function () {
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
        const arr = document.querySelectorAll('img')
        arr.forEach((v) => {
          imageObserver.observe(v);
        })
      })
    }
  };

})(Drupal);
