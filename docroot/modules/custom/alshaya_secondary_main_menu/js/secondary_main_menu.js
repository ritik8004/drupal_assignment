/**
 * @file
 * Script for Alshaya secondary main menu behaviors.
 */
(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.alshayaSecondaryMainMenu = {
    attach: function (context) {
      if ($('#block-alshayasecondarymainmenu').length && $('secondary-main-menu-wrapper').length == 0) {
        if ($(window).width() > 768) {
          $('#block-branding, #block-alshayasecondarymainmenu')
            .wrapAll('<div class="secondary-main-menu-wrapper"></div>');
          $('.secondary--main--menu .column').each(function (index) {
            let menuItemsCount = $(this).find("li.menu--three__list-item").length
            if (menuItemsCount > 18) {
              let newColumn = '<div class="column new--column new--column_' + index + '"></div>';
              $(newColumn).insertAfter($(this));
              $(this).find("li.menu--three__list-item")
                .slice(18).appendTo('.new--column_' + index);
            }
          })
          $('.secondary--main--menu ul > li.menu--one__list-item').each(function () {
            let menuTextLength = $(this).find('> .menu__link-wrapper > .menu__link').text().length;
            if(menuTextLength > 23) {
              $(this).addClass('wrap-link-text')
            }
          })
          $('.secondary--main--menu').show();
        } else {
          $('#block-alshayamainmenu ul.menu--one__list').append($('#block-alshayasecondarymainmenu .secondary--main--menu'));
          $('.secondary--main--menu').prepend('<li class="secondary-main-menu-header closed">' + Drupal.t('More') + ' </li>')
          $('#block-alshayamainmenu .secondary--main--menu').show();
          $('.secondary-main-menu-header').on('click', function () {
            $('.secondary--main--menu > ul').toggle();
            $('.secondary-main-menu-header').toggleClass('closed');
          })
        }
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
