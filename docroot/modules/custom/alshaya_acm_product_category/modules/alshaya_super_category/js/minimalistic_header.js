/**
 * @file
 * VS minimalistic header js.
 */

(function ($) {
  'use strict';

  // Only on mobile.
  if ($(window).width() < 768) {
    $(window).on('scroll', function () {
      if ($(this).scrollTop() > 0) {
        $('body').addClass('hide-minimalistic-header');
      } else {
        $('body').removeClass('hide-minimalistic-header');
      }
    });
  }
})(jQuery);
