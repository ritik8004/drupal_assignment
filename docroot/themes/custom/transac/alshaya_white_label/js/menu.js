/**
 * @file
 * Main Menu.
 */

/* global debounce */

(function ($, Drupal) {
  'use strict';

  var css = document.createElement('style');
  css.type = 'text/css';
  document.head.appendChild(css);
  function setMenuWidth() {
    var menuWidth = $('.menu--one__list').width();
    css.innerHTML = '.menu--two__list, .menu--three__list, .menu--four__list { width: ' + menuWidth + 'px}';
  }

  $(window).resize(function () {
    setMenuWidth();
  });

  Drupal.behaviors.mainMenu = {
    attach: function (context, settings) {
      $('html').once('setMenuWidth').each(function () {
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
        $(this).next('.menu__in').next().addClass('menu__list--active');
      });

      var $menuInFirst = $('.has-child:not(".max-depth") > .menu__link-wrapper');
      $menuInFirst.on('click', function () {
        $('.menu--one__list-item.has-child').addClass('not-active');
        $(this).parent().removeClass('not-active').addClass('active-menu');
      });

      var $menuBack = $('.back--link', context);
      $menuBack.on('click', function () {
        $(this).parents('.menu__list').first().removeClass('menu__list--active');
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

      $('.c-menu-primary .mobile--search').once('mainMenu').on('click', function (e) {
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
        $('.menu--two__list li:first', this).addClass('first--child_open');
        if (typeof Drupal.blazy !== 'undefined') {
          Drupal.blazy.revalidate();
        }
      }, function () {
        $('body').removeClass('overlay');
        $('.menu--two__list li:first', this).removeClass('first--child_open');
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
      var menuHovered = 0;
      if ($('.block-alshaya-main-menu').length) {
        var parent = $('.branding__menu .block-alshaya-main-menu li.menu--one__list-item');

        $('.branding__menu .block-alshaya-main-menu').mouseenter(function () {
          menuHovered = 1;
          $(parent).parent().addClass('active--menu--links');
        });

        $('.block-alshaya-main-menu').mouseleave(function () {
          $(parent).parent().removeClass('active--menu--links');
          menuHovered = 0;
        });
      }

      $('.branding__menu .block-alshaya-main-menu li.menu--one__list-item').mouseenter(function () {
        if (menuHovered === 1) {
          $('ul.menu--two__list').css('transition', 'none');
          $('.menu-backdrop').css('transition', 'none');
        }
        else {
          $('ul.menu--two__list').css('transition', 'all var(--menuTiming) ease-in 300ms');
          $('.menu-backdrop').css('transition', 'all var(--menuTiming) ease-in 300ms');
        }
      });

      $('.branding__menu .block-alshaya-main-menu li.menu--one__list-item').mouseleave(function () {
        if (menuHovered === 1) {
          $('ul.menu--two__list').css('transition', 'all var(--menuTiming) ease-in 300ms');
          $('.menu-backdrop').css('transition', 'all var(--menuTiming) ease-in 300ms');
        }
      });

      // Add class for three level navigation.
      $('.menu--one__list-item:not(:has(.menu--four__list-item))').addClass('has--three-levels');

      // Set menu level2 height on desktop and tablet.
      var windowWidth = $(window).width();
      var menuLevel2 = $('.menu--two__list');
      var menuBackdrop = $('.menu-backdrop');

      function setMenuHeight() {
        if (menuLevel2.length > 0 && windowWidth > 1024) {
          var calcMaxHeight;
          if (!$('.block-alshaya-main-menu').hasClass('megamenu-inline-layout')) {
            calcMaxHeight = $('.block-alshaya-main-menu').height() + $('.block-alshaya-main-menu').offset().top;
          }
          var maxHeight = menuLevel2.map(function () {
            return $(this).height();
          })
              .toArray()
              .reduce(function (first, second) {
                return Math.max(first, second);
              });

          menuBackdrop.height(maxHeight);
          menuLevel2.each(function () {
            $(this).height(maxHeight);
            if (!$('.block-alshaya-main-menu').hasClass('megamenu-inline-layout')) {
              $(this).css('max-height', 'calc(100vh - ' + calcMaxHeight + 'px)');
            }
          });
        }
      }
      setMenuHeight();
      $(window).resize(debounce(function () {
        setMenuHeight();
      }, 250));

      var menuTiming = $('.main--menu').attr('data-menu-timing');
      if (menuTiming !== 'undefined') {
        $(':root').css({'--menuTiming': menuTiming + 'ms'});
      }

      // Adding Class to parent on hover of a menu-item without child.
      if ($(window).width() > 1023) {
        $('.block-alshaya-main-menu li.menu--one__list-item:not(.has-child)').hover(function () {
          $(this).parent().addClass('active--menu--without__child');
        }, function () {
          $(this).parent().removeClass('active--menu--without__child');
        });
      }

      function stickyHeader() {
        $(window, context).once().on('scroll', function () {
          var position = $('.region__banner-top').outerHeight();

          if ($(this).scrollTop() > position) {
            $('.branding__menu').addClass('navbar-fixed-top');
            $('body').addClass('header--fixed');
          }
          else {
            $('.branding__menu').removeClass('navbar-fixed-top');
            $('body').removeClass('header--fixed');
          }
        });
      }

      $(window, context).on('load', function () {
        // Apply the sticky header only on non plp,srp,promo pages.
        if ($(window).width() < 768 && $('.region__banner-top').length > 0 && $('.c-plp').length < 1) {
          stickyHeader();
        }
      });
    }
  };

})(jQuery, Drupal);
