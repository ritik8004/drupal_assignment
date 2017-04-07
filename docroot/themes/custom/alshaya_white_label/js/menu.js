/**
 * @file
 * Main Menu.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.mainMenu = {
    attach: function (context, settings) {

      var $listItems = $('.menu__list-item');
      $listItems.each(function () {
        var linkWrapper = $(this).find('> .menu__link-wrapper');
        var link = linkWrapper.find('.menu__link');
        var submenu = $(this).find('> .menu__list');

        if (submenu.length > 0) {
          $(this).addClass('has-child');
          linkWrapper.after('<span class="menu__in"></span>');
          submenu.prepend('<span class="menu__list-item back--link"><span class="menu__back"></span> <span>' + link.text() + ' </span> </span> ');
        }
      });

      var $menuIn = $('.menu__in');
      $menuIn.click(function () {
        $(this).next().toggleClass('menu__list--active');
      });

      var $menuBack = $('.back--link');
      $menuBack.click(function () {
        $(this).parent().toggleClass('menu__list--active');
      });

      $('.mobile--menu, .mobile--search').click(function (e) {
        e.preventDefault();
      });

      $('.hamburger--menu').click(function () {
        $('.main--menu').toggle();
        $('body').addClass('mobile--overlay');
        $('.mobile--close').addClass('block--display');
      });

      $('.form-item-coupon label').click(function () {
        $('.form-item-coupon #edit-coupon').slideToggle();
      });

      $('.c-menu-primary .mobile--search').click(function () {
        $('.c-menu-primary #block-exposedformsearchpage').toggle();
      });

      $('.mobile--close').on('click', function (e) {
        $('.main--menu').toggle();
        $('body').removeClass('mobile--overlay');
      });

      $('.parent--level').on('click', function () {
        $(this).addClass('current--clicked').siblings().removeClass('current--clicked');
      });

      $('.level--two').on('click', function () {
        $(this).addClass('current--clicked').siblings().removeClass('current--clicked');
      });

    }
  };

})(jQuery, Drupal);
