/**
 * @file
 * Main Menu.
 */

/* global debounce */

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
        var submenu;

        if ($(window).width() <= 1024) {
          submenu = $(this).find('> .menu__list .menu--three__list-item');
        }
        else {
          submenu = $(this).find('> .menu__list .menu--two__list-item');
        }

        if (submenu.length > 0) {
          $(this).addClass('has-child');
          linkWrapper.addClass('contains-sublink');
          linkWrapper.once().after('<span class="menu__in"></span>');
        }
      });

      var $menuIn = $('.has-child:not(".max-depth") .menu__link-wrapper', context);
      $menuIn.on('click', function () {
        $(this).next('.menu__in').next().toggleClass('menu__list--active');
      });

      var $menuInFirst = $('.has-child:not(".max-depth") > .menu__link-wrapper');
      $menuInFirst.on('click', function () {
        $('.menu--one__list-item.has-child').addClass('not-active');
        $(this).parent().removeClass('not-active').addClass('active-menu');
      });

      var $menuBack = $('.back--link', context);
      $menuBack.on('click', function () {
        $(this).parents('.menu__list').first().toggleClass('menu__list--active');
      });

      var $menuBackFirst = $('.menu--two__list > .menu__links__wrapper > .back--link');
      $menuBackFirst.on('click', function () {
        $('.menu--one__list-item.has-child').removeClass('not-active active-menu');
      });

      $('.mobile--menu, .mobile--search').click(function (e) {
        e.preventDefault();
      });

      $('.hamburger--menu').click(function () {
        if ($('.search-active').length > 0) {
          $('.c-header__region .block-views-exposed-filter-blocksearch-page').toggle().toggleClass('show-search');
          $('.search-active').removeClass('search-active');
        }

        $('.main--menu').addClass('menu--active');
        $('.block-alshaya-super-category .main--menu ').removeClass('menu--active');
        $('html').addClass('html--overlay');
        $('body').addClass('mobile--overlay');
      });

      $('.c-menu-primary .mobile--search').off().on('click', function (e) {
        e.preventDefault();
        $('.c-header__region .block-views-exposed-filter-blocksearch-page').toggle().toggleClass('show-search');
        $('.c-header__region .block-views-exposed-filter-blocksearch-page input.ui-autocomplete-input').focus();
        $(this).parent().toggleClass('search-active');
      });

      // Hide mobile search box, when clicked anywhere else.
      $(window).on('click touchstart', function (e) {
        if (!$(e.target).is('.c-menu-primary .mobile--search')) {
          // Check if element is Visible.
          if ($('.c-menu-primary #block-exposedformsearchpage').is(':visible')) {
            $('.c-menu-primary #block-exposedformsearchpage').hide();
            $('.c-menu-primary .mobile--search').parent().toggleClass('search-active');
          }
        }
      });

      // Stop event from inside container to propogate out.
      $('.c-menu-primary #block-exposedformsearchpage').on('click touchstart', function (event) {
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

      $('.branding__menu .has-child .menu--one__link, .branding__menu .has-child .menu--two__list').hover(function () {
        $('body').addClass('overlay');
      }, function () {
        $('body').removeClass('overlay');
      });

      // Close mobile menu when clicked outside the menu.
      var mobileMenu = $('.main--menu');
      $('body', context).once().click(function (e) {
        if (mobileMenu.hasClass('menu--active') && e.target === $('.menu--active')[0]) {
          $('.mobile--close').trigger('click');
        }
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

      /**
       * Add active state to the menu.
       */

      if ($('.block-alshaya-main-menu').length) {
        var parent = $('.branding__menu .block-alshaya-main-menu li.menu--one__list-item');

        $('.branding__menu .block-alshaya-main-menu').mouseenter(function () {
          setTimeout(function () {
            $(parent).parent().addClass('active--menu--links');
          }, 500);
          $(parent).children('.menu--two__list').css('transition-delay', '0.3s');
        });

        $('.block-alshaya-main-menu').mouseleave(function () {
          $(parent).parent().removeClass('active--menu--links');
          $(parent).children('.menu--two__list').css('transition-delay', '0s');
        });
      }

      /**
       * Make Header sticky on scroll.
       */

      if ($('.branding__menu').length) {
        var position = $('.branding__menu').offset().top;
        var nav = $('.branding__menu');
        var topHeader = $('.header--wrapper');
        $(window, context).once().scroll(function () {
          if ($(this).scrollTop() > position) {
            $('body').addClass('header--fixed');
            nav.addClass('navbar-fixed-top');
            topHeader.addClass('navbar-fixed-top');
          }
          else {
            nav.removeClass('navbar-fixed-top');
            topHeader.removeClass('navbar-fixed-top');
            $('body').removeClass('header--fixed');
          }
        });
      }

      // Add class for three level navigation.
      $('.menu--one__list-item:not(:has(.menu--four__list-item))').addClass('has--three-levels');

      // Set menu level2 height on desktop and tablet.
      var windowWidth = $(window).width();
      var menuLevel2 = $('.menu--two__list');

      function setMenuHeight() {
        if (menuLevel2.length > 0 && windowWidth > 1024) {
          var maxHeight = menuLevel2.map(function () {
            return $(this).height();
          })
              .toArray()
              .reduce(function (first, second) {
                return Math.max(first, second);
              });

          menuLevel2.each(function () {
            $(this).height(maxHeight);
          });
        }
      }
      setMenuHeight();
      $(window).resize(debounce(function () {
        setMenuHeight();
      }, 250));
    }
  };

})(jQuery, Drupal);
