/**
 * @file
 * Main Menu.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.mainMenu = {
    attach: function (context, settings) {

      $('.mobile--menu, .mobile--search').click(function (e) {
        e.preventDefault();
      });

      $('.hamburger--menu').click(function () {
        $('.main--menu').toggle();
        $('body').addClass('mobile--overlay');
        $('.mobile--close').addClass('block--display');
      });

      $('.c-menu-primary .mobile--search').click(function () {
        $('.c-menu-primary #block-exposedformsearchpage').toggle();
      });

      $('.parent--level').mouseover(function () {
        $('.child--first', this).stop(true, true).show();
        $('body').addClass('overlay');
      }).mouseout(function () {
        $('.child--first').stop(true, true).hide();
        $('body').removeClass('overlay');
      });

      $('.mobile--close').on('click', function (e) {
        $('.main--menu').toggle();
        $('body').removeClass('mobile--overlay');
      });

    }
  };

})(jQuery, Drupal);
