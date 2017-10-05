(function ($) {
  'use strict';

  $(document).ready(function () {
    var $offer_toggler = $('.block-todays-offer h2');
    var $offer_content = $('.block-todays-offer .field-paragraph-content');

    $($offer_toggler).on('click', function () {
      $(this).parent().toggleClass('active-offer');
      $($offer_content).toggle();
      $('body').toggleClass('overlay-content');
    });
  });
})(jQuery);
