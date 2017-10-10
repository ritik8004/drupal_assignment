(function ($) {
  'use strict';

  $(document).ready(function () {
    var $offer_toggler = $('.block-todays-offer h2');
    var $offer_content = $('.block-todays-offer .field-paragraph-content');
    var $overlay_content = $('.empty-overlay');
    var $body = $('body');

    $($offer_toggler).on('click', function () {
      $(this).parent().toggleClass('active-offer');
      $($offer_content).slideToggle('500');
      $($body).toggleClass('active-todays-offer');
      $($overlay_content).toggleClass('overlay-content');
    });
  });
})(jQuery);
