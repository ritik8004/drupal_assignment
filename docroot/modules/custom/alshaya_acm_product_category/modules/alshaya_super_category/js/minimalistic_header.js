/**
 * @file
 * VS minimalistic header js.
 */

(function ($) {
  'use strict';
  $(".block-alshaya-super-category a.menu--one__link").mouseover(function () {
    var activeImage = $(this).find('.image-container');
    activeImage.attr('src', activeImage.data("hover-image"));
  }).mouseout(function () {
    var inactiveImage = $(this).find('.image-container');
    inactiveImage.attr('src', inactiveImage.data("org-image"));
  });

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
