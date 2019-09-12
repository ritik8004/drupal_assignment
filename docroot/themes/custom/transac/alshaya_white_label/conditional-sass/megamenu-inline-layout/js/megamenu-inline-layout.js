/**
 * @file
 * Mega menu inline layout.
 */

(function ($, Drupal) {
  'use strict';

  // Feature only needed for desktop and if megamenu is present.
  if ($(window).width() > 1023 && $('.block-alshaya-main-menu').length) {
    var parent = $('.branding__menu .block-alshaya-main-menu li.menu--one__list-item');

    // On mouseleave removed active class from all the places.
    $('.block-alshaya-main-menu').mouseleave(function () {
      $(parent).removeClass('active');
      $('.menu--two__list-item').removeClass('active');
      $('.move-to-right').removeClass('move-to-left');
    });

    // On hove of L1 item make first L2 item active by default.
    $(parent).hover(function () {
      $(parent).removeClass('active');
      $(this).addClass('active');
      $(this).find('.menu__links__wrapper > .menu--two__list-item:not(.move-to-right):first').addClass('active');
    });

    // On hover of l2 item add active class.
    $('.menu__links__wrapper > .menu--two__list-item:not(.move-to-right)').hover(function () {
      $('.menu--two__list-item').removeClass('active');
      $(this).addClass('active');

      // Adding class to check the l2 has no more child so that right side category needs to move to left.
      if ($('.last-element').hasClass('active') && $('.move-to-right.move-to-left').length < 1) {
        $('.move-to-right').addClass('move-to-left');
      }
      else if ($('.move-to-right').hasClass('move-to-left') && $('.last-element.active').length < 1) {
        $('.move-to-right').removeClass('move-to-left');
      }
    });

    $('.menu__links__wrapper > .menu--two__list-item').each(function () {
      if ($(this).find('.menu--three__list').length < 1) {
        $(this).addClass('last-element');
      }
    });
  }

})(jQuery, Drupal);
