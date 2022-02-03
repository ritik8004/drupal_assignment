/**
 * @file
 * Order detail page.
 */
(function ($, Drupal) {

  Drupal.behaviors.orderDetail = {
    attach: function () {
      var cancelOrderLink = $('.order-detail .user__order--detail .order-summary-row .cancel-item a');

      $(cancelOrderLink).once('redirect-link').on('click', function (e) {
        e.preventDefault();
        $('html, body').animate({
          scrollTop: $('#cancelled-items').offset().top
        }, 350);
      });
    }
  };
})(jQuery, Drupal);
