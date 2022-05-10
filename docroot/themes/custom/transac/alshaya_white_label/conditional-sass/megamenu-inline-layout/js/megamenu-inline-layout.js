/**
 * @file
 * Mega menu inline layout.
 */

(function ($, Drupal) {

  var menuLevel2 = '.menu--two__list';
  var menuLevel3 = '.menu--three__list';
  var menuLevel4 = '.menu--four__list';
  var activeMenuPartent = 'ul.menu__list';
  var lastList = 'lastList';

  Drupal.behaviors.mainMenuInlineLayout = {
    attach: function (context, settings) {
      // Return if the placeholders text there in code.
      if ($('.menu__list-item:contains(#rcs.menuItem.name#)').length > 0) {
        return;
      }

      // Feature only needed for desktop and if megamenu is present.
      if ($(window).width() > 1023 && $('.block-alshaya-main-menu').length) {
        $('.block-alshaya-main-menu').once('mainMenuInlineLayout').each(function () {
          var parent = $('.branding__menu .block-alshaya-main-menu li.menu--one__list-item');
          var listTwoItem = $('.menu--two__list-item');
          var listThreeItem = $('.menu--three__list-item');

          // Add required class when four levels of navigation is present.
          $('.menu--one__list-item:has(.menu--four__list-item)').addClass('has--three-levels');

          // On mouseleave removed active class from all the places.
          $('.block-alshaya-main-menu').mouseleave(function () {
            $(parent).removeClass('active');
            $(listTwoItem).removeClass('active');
            $(listThreeItem).removeClass('active');
            $(parent).parent().removeClass('active--menu--links');
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
            var activeMenu = $(this).find('.menu__links__wrapper > .menu--two__list-item:not(.move-to-right):first');
            activeMenu.addClass('active');
            $(this).find('ul.menu--two__list').css('z-index', '2');

            // If first L2 doesn't has L3 by default move right category to left.
            if (activeMenu.hasClass('last-element')) {
              $('.move-to-right').addClass('move-to-left');
            }

            // Calculate l2 height
            if ($(this).hasClass('has-child')) {
              activeMenu.parents(menuLevel2).css('height', 'auto');
              Drupal.setInlineMenuHeight(activeMenu, menuLevel3);
            }
          });

          // On hover of l2 item add active class.
          $('.menu__links__wrapper > .menu--two__list-item:not(.move-to-right)').once('menu-hover').on().hover(function () {
            var activeMenu = $(this);
            Drupal.activateMenu(listTwoItem, activeMenu, menuLevel3);

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
            Drupal.activateMenu(listThreeItem, activeMenu, menuLevel4);
          });

          // On hover of l4 item reset l2 height.
          $('.menu--four__list-item').once('menu-hover').on('hover', function () {
            var activeMenu = $(this);
            activeMenu.parents(menuLevel2).css('height', 'auto');
            Drupal.setInlineMenuHeight(activeMenu, lastList);
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

        });
      }
    }
  };

  Drupal.activateMenu = function (menuLevel, activeMenu, menuChild) {
    $(menuLevel).removeClass('active');
    activeMenu.addClass('active');
    // Reset l2 height
    activeMenu.parents(menuLevel2).css('height', 'auto');
    Drupal.setInlineMenuHeight(activeMenu, menuChild);
  };

  Drupal.setInlineMenuHeight = function (activeMenu, menuChild) {
    var currentMenuLevel2Height = activeMenu.parents(menuLevel2).first().height();
    var parentHeight;
    var childHeight;

    switch (menuChild) {
      case ".menu--three__list":
        if(!($(activeMenu).is('[class*="last-element"]'))) {
          childHeight = activeMenu.children(menuChild).first().height();
          Drupal.setL2MenuHeight(activeMenu, currentMenuLevel2Height, 0, childHeight);
        }
        break;

      case ".menu--four__list":
        parentHeight = activeMenu.parents(activeMenuPartent).first().height();
        if(!($(activeMenu).is('[class*="last-element"]'))) {
          childHeight = activeMenu.children(menuChild).prop('scrollHeight');
          Drupal.setL2MenuHeight(activeMenu, currentMenuLevel2Height, parentHeight, childHeight);
        }
        else {
          Drupal.setL2MenuHeight(activeMenu, currentMenuLevel2Height, parentHeight, 0);
        }
        break;

      case "lastList":
        parentHeight = activeMenu.parents(activeMenuPartent).prop('scrollHeight');
        childHeight = activeMenu.parents(menuLevel3).first().height();
        Drupal.setL2MenuHeight(activeMenu, currentMenuLevel2Height, parentHeight, childHeight);
        break;
    }
  };

  Drupal.setL2MenuHeight = function (activeMenu, currentMenuLevel2Height, parentHeight, childHeight) {
    var menuLevel2Height = Math.max(currentMenuLevel2Height, parentHeight, childHeight) + 8;
    activeMenu.parents(menuLevel2).css('height', menuLevel2Height + 'px');
  };

})(jQuery, Drupal);
