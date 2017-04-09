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
        $('.main--menu').show();
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
        $('.main--menu').hide();
        $('.c-menu-secondary').addClass('remove--toggle');
        $('body').removeClass('mobile--overlay');
      });

      $('.menu--one__list-item').hover(
        function () {
          $('body').addClass('overlay');
        },
        function () {
          $('body').removeClass('overlay');
        }
      );

      $('.logged-out .account').click(function () {
        $('.account').addClass('active');
        $('.shop').removeClass('active');
        $('.c-menu-secondary').addClass('block--display');
        $('.menu--one__list').addClass('remove--toggle');
        $('.menu--one__list').removeClass('block--display');
        $('.c-menu-secondary').removeClass('remove--toggle');
      });

      $('.logged-out .shop').click(function () {
        $('.shop').addClass('active');
        $('.account').removeClass('active');
        $('.c-menu-secondary').removeClass('block--display');
        $('.menu--one__list').removeClass('remove--toggle');
        $('.menu--one__list').addClass('block--display');
        $('.c-menu-secondary').addClass('remove--toggle');
      });

      $('.logged-in .account--logged_in').click(function () {
        $('.account--logged_in').addClass('active');
        $('.shop').removeClass('active');
        $('.my-account-nav').addClass('block--display');
        $('.menu--one__list').addClass('remove--toggle');
        $('.menu--one__list').removeClass('block--display');
        $('.my-account-nav').removeClass('remove--toggle');
      });

      $('.logged-in .shop').click(function () {
        $('.shop').addClass('active');
        $('.account--logged_in').removeClass('active');
        $('.c-menu-secondary').addClass('block--display');
        $('.menu--one__list').removeClass('remove--toggle');
        $('.menu--one__list').addClass('block--display');
        $('.c-menu-secondary').addClass('remove--toggle');
      });

    }
  };

})(jQuery, Drupal);
