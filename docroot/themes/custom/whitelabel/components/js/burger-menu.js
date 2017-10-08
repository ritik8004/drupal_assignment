/**
 * @file
 * Sliders.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.menuItemsLength = {
    attach: function (context, settings) {
      var $mainNavigationBlock = $('.block-logo-navigation');
      var $subNavigationBlock = $('.block-sub-navigation');
      var $subNavigationMenu = $('.navigation__sub-menu');
      var $subNavigationItems = $subNavigationMenu.find('li');

      if ($subNavigationItems.length > 2) {
        $mainNavigationBlock.addClass('show-menu-button');
        $subNavigationBlock.addClass('vertical-layout');
      }
    }
  };

  Drupal.behaviors.slideOutMenu = {
    attach: function (context, settings) {
      const menuButton = $('.burger');
      const closeButton = $('.menu-close');
      const menuContent = $('.navigation__sub-menu');
      const menuLogos = $('.menu-logo-navigation');

      menuButton.click(function () {
        menuButton.addClass('is-hidden');
        menuContent.addClass('is-active');
        menuLogos.addClass('is-active-ul');
        closeButton.addClass('is-active-close');
      });

      closeButton.click(function () {
        menuButton.removeClass('is-hidden');
        menuContent.removeClass('is-active');
        menuLogos.removeClass('is-active-ul');
        closeButton.removeClass('is-active-close');
      });
    }
  };

})(jQuery, Drupal);
