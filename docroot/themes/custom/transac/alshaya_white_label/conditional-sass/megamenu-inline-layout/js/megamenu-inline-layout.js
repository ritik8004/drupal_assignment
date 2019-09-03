/**
 * @file
 * Mega menu inline layout.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.megaMenuInlineLayout = {
    attach: function (context, settings) {
      if ($(window).width() > 1023 && $('.block-alshaya-main-menu').length) {
        var parent = $('.branding__menu .block-alshaya-main-menu li.menu--one__list-item');

        $('.block-alshaya-main-menu').mouseleave(function () {
          $(parent).removeClass('active');
          $('.menu--two__list-item').removeClass('active');
        });

        $(parent).hover(function () {
          $(parent).removeClass('active');
          $(this).addClass('active');
          $(this).find('.menu__links__wrapper > .menu--two__list-item:first').addClass('active');
        });

        $('.menu__links__wrapper > .menu--two__list-item').hover(function () {
          $('.menu--two__list-item').removeClass('active');
          $(this).addClass('active');
        });

        $('.menu__links__wrapper > .menu--two__list-item').each(function () {
          if ($(this).find('.menu--three__list').length < 1) {
            $(this).addClass('last-element');
          }
        });
      }
    }
  };

})(jQuery, Drupal);
