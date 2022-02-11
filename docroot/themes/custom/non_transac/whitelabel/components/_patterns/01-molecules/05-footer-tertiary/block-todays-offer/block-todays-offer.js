(function ($) {

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

    /*
    Today's offer sticky on scroll.
    */
    function checkOffset() {
      if ($('.block-todays-offer h2').offset().top + $('.block-todays-offer h2').height() >= $('.block-todays-offer').offset().top - 30) {
        $('.block-todays-offer h2').addClass('label-not-fixed'); // restore on scroll down
        $('.block-todays-offer').removeClass('todays-offer-fixed'); // restore on scroll down
      }

      if ($(document).scrollTop() + window.innerHeight < $('.block-todays-offer').offset().top) {
        $('.block-todays-offer h2').removeClass('label-not-fixed');
        $('.block-todays-offer').addClass('todays-offer-fixed');
      }
    }

    $(document).scroll(function () {
      checkOffset();
    });
  });
})(jQuery);
