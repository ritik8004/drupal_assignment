/**
 * @file
 * RCS Free gift js file.
 */

(function ($, Drupal) {
  Drupal.behaviors.freeGiftsSlider = {
    attach: function (context, settings) {
      // On dialog close remove the free gift overlay related classes.
      $('.free-gifts-modal-overlay #free-gift-drupal-modal').once().on('dialogclose', function () {
        if ($('body').hasClass('free-gift-promo-list-overlay')) {
          $('body').removeClass('free-gift-promo-list-overlay');
        }
        $('body').removeClass('free-gifts-modal-overlay');
      });
    }
  }
})(jQuery, Drupal);
