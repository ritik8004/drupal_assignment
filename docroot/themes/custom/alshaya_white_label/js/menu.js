/**
 * @file
 * Main Menu.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.mainMenu = {
    attach: function (context, settings) {

      var css = document.createElement('style');
      css.type = 'text/css';
      document.head.appendChild(css);
      function setMenuWidth() {
        var menuWidth = $('.menu--one__list').width();
        css.innerHTML = '.menu--two__list, .menu--three__list, .menu--four__list { width: ' + menuWidth + 'px}';
      }

      setMenuWidth();
      $(window).resize(function () {
        setMenuWidth();
      });

      var $listItems = $('.menu__list-item');
      $listItems.each(function () {
        var linkWrapper = $(this).find('> .menu__link-wrapper');
        var submenu = $(this).find('> .menu__list');

        if (submenu.length > 0) {
          $(this).addClass('has-child');
          linkWrapper.addClass('contains-sublink');
          linkWrapper.after('<span class="menu__in"></span>');
        }
      });

      var $menuIn = $('.has-child .menu__link-wrapper');
      $menuIn.click(function () {
        $(this).next('.menu__in').next().toggleClass('menu__list--active');
      });

      var $menuBack = $('.back--link');
      $menuBack.click(function () {
        $(this).parents('.menu__list').first().toggleClass('menu__list--active');
      });

      $('.mobile--menu, .mobile--search').click(function (e) {
        e.preventDefault();
      });

      $('.hamburger--menu').click(function () {
        $('.main--menu').addClass('menu--active');
        $('html').addClass('html--overlay');
        $('body').addClass('mobile--overlay');
      });

      $('.c-menu-primary .mobile--search').click(function () {
        $('.c-menu-primary #block-exposedformsearchpage').toggle();
        $(this).parent().toggleClass('search-active');
      });

      // Hide mobile search box, when clicked anywhere else.
      $(window).bind('click touchstart', function (e) {
        if (!$(e.target).is('.c-menu-primary .mobile--search')) {
          // Check if element is Visible.
          if ($('.c-menu-primary #block-exposedformsearchpage').is(':visible')) {
            $('.c-menu-primary #block-exposedformsearchpage').hide();
            $('.c-menu-primary .mobile--search').parent().toggleClass('search-active');
          }
        }
      });

      // Stop event from inside container to propogate out.
      $('.c-menu-primary #block-exposedformsearchpage').bind('click touchstart', function (event) {
        event.stopPropagation();
      });

      $('.menu--one__list-item.has-child').each(function () {
        $('.menu--one__list-item.has-child').mouseenter(function () {
          $('.menu--two__list li:first', this).addClass('first--child_open');
        });
      });

      $('.menu--one__list-item.has-child').each(function () {
        $('.menu--one__list-item.has-child').mouseleave(function () {
          $('.menu--two__list li:first', this).removeClass('first--child_open');
        });
      });

      $('.menu--two__list-item .menu-two__link-wrapper').hover(function () {
        $('.menu--two__list-item').removeClass('first--child_open');
      });

      // @TODO: Refactor to reduce complexity.
      $('.mobile--close').on('click', function (e) {
        $('.main--menu').removeClass('menu--active');
        $('.c-menu-secondary').removeClass('block--display');
        $('.shop').addClass('active');
        $('.account--logged_in').removeClass('active');
        $('.account').removeClass('active');
        $('html').removeClass('html--overlay');
        $('body').removeClass('mobile--overlay');
        $('.c-my-account-nav').removeClass('block--display');
        $('.mobile--close').removeClass('block--display');
        $('.remove--toggle').removeClass('remove--toggle');
        $('.menu--one__list').find('.menu__list--active').removeClass('.menu__list--active');
      });

      var header_timer;
      $('.main--menu').hover(function () {
        header_timer = setTimeout(function () {
          $('body').addClass('overlay');
        }, 700);
      }, function () {
        clearTimeout(header_timer);
        $('body').removeClass('overlay');
      });

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
        $('.c-my-account-nav').addClass('remove--toggle');
      });

      $('.logged-in .account--logged_in').click(function () {
        $('.account--logged_in').addClass('active');
        $('.shop').removeClass('active');
        $('.c-my-account-nav').addClass('block--display');
        $('.menu--one__list').addClass('remove--toggle');
        $('.menu--one__list').removeClass('block--display');
        $('.c-my-account-nav').removeClass('remove--toggle');
        $('.c-my-account-nav').removeClass('remove--toggle');
      });

      $('.logged-in .shop').click(function () {
        $('.shop').addClass('active');
        $('.account--logged_in').removeClass('active');
        $('.c-menu-secondary').addClass('block--display');
        $('.menu--one__list').removeClass('remove--toggle');
        $('.menu--one__list').addClass('block--display');
        $('.c-menu-secondary').addClass('remove--toggle');
        $('.c-my-account-nav').removeClass('block--display');
      });

      // Toggle Function for Store Locator.
      $(document).off().on('click', '.hours--label', function () {
        $(this).toggleClass('open');
      });

      /**
      * Add active state to the menu.
      */

      if ($('.block-alshaya-main-menu').length) {
        var parent = $('.block-alshaya-main-menu li.menu--one__list-item');

        $('.block-alshaya-main-menu').mouseenter(function () {
          setTimeout(function () {
            $(parent).parent().addClass('active--menu--links');
          }, 500);
        });

        $('.block-alshaya-main-menu').mouseleave(function () {
          $(parent).parent().removeClass('active--menu--links');
        });
      }

      /**
      * Make Header sticky on scroll.
      */

      if ($('.branding__menu').length) {
        var position = $('.branding__menu').offset().top;
        var nav = $('.branding__menu');

        $(window).scroll(function () {
          if ($(this).scrollTop() > position) {
            $('body').addClass('header--fixed');
            nav.addClass('navbar-fixed-top');
          }
          else {
            nav.removeClass('navbar-fixed-top');
            $('body').removeClass('header--fixed');
          }
        });
      }

      // Add class for three level navigation.
      $('.menu--one__list-item:not(:has(.menu--four__list-item))').addClass('has--three-levels');
    }
  };

})(jQuery, Drupal);
