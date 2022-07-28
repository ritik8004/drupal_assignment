/**
 * @file
 * Script for Alshaya secondary main menu behaviors.
 */
(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.alshayaSecondaryMainMenu = {
    attach: function (context) {
      if ($('#block-alshayasecondarymainmenu').length && $('secondary-main-menu-wrapper').length == 0) {
        if ($(window).width() > 1024) {
          $('#block-alshayasecondarymainmenu').once('alshayaSecondaryMainMenu').wrapAll('<div class="secondary-main-menu-wrapper"></div>');
          $('.secondary--main--menu .column').each(function (index) {
            const menuItemsCount = $(this).find("li.menu--three__list-item").length;
            const numberOfItems = 16;

            if (menuItemsCount > numberOfItems) {
              let $pointerElement = $(this);
              for (let i = 1; i < Math.ceil(menuItemsCount / numberOfItems); i++) {
                let columnWrapper = '<div class="column new--column new--column_' + index + '"></div>';
                const $newCol = $(columnWrapper).insertAfter($pointerElement);
                $pointerElement = $newCol;

                $(this)
                  .find("li.menu--three__list-item")
                  .slice(numberOfItems)
                  .slice(0, numberOfItems)
                  .appendTo($newCol);
              }
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
          let megamenu = $('.block-alshaya-main-menu ul.menu--one__list');
          megamenu.append($('.promo-wrapper'));
          megamenu.append($('#block-alshayasecondarymainmenu .secondary--main--menu'));
          $('.secondary--main--menu').once('alshayaSecondaryMainMenu').prepend('<li class="secondary-main-menu-header closed">' + Drupal.t('More') + ' </li>')
          $('.block-alshaya-main-menu .secondary--main--menu').show();
          $('.main--menu .promo-wrapper').show();
          $('.secondary-main-menu-header').on('click', function () {
            $('.secondary-main-menu-header').toggleClass('closed');
          })
        }
      }
    }
  };
})(jQuery, Drupal);
