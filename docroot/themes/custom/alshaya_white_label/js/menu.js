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
      });

      $('.c-menu-primary .mobile--search').click(function () {
        $('.c-menu-primary #block-exposedformsearchpage').toggle();
      });

      $('.parent--level').mouseover(function () {
        $('.child--first', this).stop(true, true).show();
      }).mouseout(function () {
        $('.child--first').stop(true, true).hide();
      });

    }
  };

})(jQuery, Drupal);
