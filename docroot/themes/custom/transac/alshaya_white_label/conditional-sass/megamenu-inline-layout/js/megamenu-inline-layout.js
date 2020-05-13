/**
 * @file
 * Mega menu inline layout.
 */

(function ($, Drupal) {
  'use strict';

  // Feature only needed for desktop and if megamenu is present.
  if ($(window).width() > 1023 && $('.block-alshaya-main-menu').length) {
    var parent = $('.branding__menu .block-alshaya-main-menu li.menu--one__list-item');
    var listTwoItem = $('.menu--two__list-item');
    var listThreeItem = $('.menu--three__list-item');

    Drupal.activateMenu = function (menuLevel, activeMenu) {
      $(menuLevel).removeClass('active');
      activeMenu.addClass('active');
    };

    // Add required class when four levels of navigation is present.
    $( '.menu--one__list-item:has(.menu--four__list-item)').addClass('has--three-levels');

    // On mouseleave removed active class from all the places.
    $('.block-alshaya-main-menu').mouseleave(function () {
      $(parent, listTwoItem, listThreeItem).removeClass('active');

      // Checking if first l2 doesn't have l3 and it is in active state.
      if (!$('.menu__links__wrapper > .menu--two__list-item.last-element:not(.move-to-right):first').hasClass('active')) {
        $('.move-to-right').removeClass('move-to-left');
      }
    });

    // On hover of L1 item make first L2 item active by default.
    $(parent).once('menu-hover').on().hover(function () {
      // reset classes and z-index.
      $(parent).removeClass('active');
      $('.move-to-right').removeClass('move-to-left');
      $('ul.menu--two__list').css('z-index', '1');

      // apply classes and z-index.
      $(this).addClass('active');
      $(this).find('.menu__links__wrapper > .menu--two__list-item:not(.move-to-right):first').addClass('active');
      $(this).find('ul.menu--two__list').css('z-index', '2');

      // If first L2 doesn't has L3 by default move right category to left.
      if ($(this).find('.menu__links__wrapper .menu--two__list-item.active:not(.move-to-right):first').hasClass('last-element')) {
        $('.move-to-right').addClass('move-to-left');
      }
    });

    // On hover of l2 item add active class.
    $('.menu__links__wrapper > .menu--two__list-item:not(.move-to-right)').once('menu-hover').on().hover(function () {
      var activeMenu = $(this);
      Drupal.activateMenu(listTwoItem, activeMenu);

      // Adding class to check the l2 has no more child so that right side category needs to move to left.
      if ($('.last-element').hasClass('active') && $('.move-to-right.move-to-left').length < 1) {
        $('.move-to-right').addClass('move-to-left');
      }
      else if ($('.move-to-right').hasClass('move-to-left') && $('.last-element.active').length < 1) {
        $('.move-to-right').removeClass('move-to-left');
      }
    });

    // On hover of l3 item add active class.
    $('.menu--three__list-item').once('menu-hover').on().hover(function () {
      var activeMenu = $(this);
      Drupal.activateMenu(listThreeItem, activeMenu);
    });

    // Add class for l2 ietms without any children
    $('.menu__links__wrapper > .menu--two__list-item').each(function () {
      if ($(this).find('.menu--three__list').length < 1) {
        $(this).addClass('last-element');
      }
    });

    // Add class for l3 ietms without any children
    $('.level-three__wrapper > .menu--three__list-item').each(function () {
      if ($(this).find('.menu--four__list').length < 1) {
        $(this).addClass('last-element-l4');
      }
    });
  }

})(jQuery, Drupal);
