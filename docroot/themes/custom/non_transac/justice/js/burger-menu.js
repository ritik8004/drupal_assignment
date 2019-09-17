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
      var $body = $('body');

      if ($subNavigationItems.length > 2) {
        $mainNavigationBlock.addClass('show-menu-button');
        $subNavigationBlock.addClass('vertical-layout');
        $body.addClass('has-burger-menu');
      }
    }
  };

  Drupal.behaviors.slideOutMenu = {
    attach: function (context, settings) {
      var menuButton = $('.burger');
      var closeButton = $('.menu-close');
      var menuContent = $('.navigation__sub-menu');
      var menuLogos = $('.menu-logo-navigation');
      var overlayContent = $('.empty-overlay');

      function toggleMenu() {
        menuButton.toggleClass('is-hidden');
        menuContent.toggleClass('is-active');
        menuLogos.toggleClass('is-active-ul');
        closeButton.toggleClass('is-active-close');
        overlayContent.toggleClass('overlay-content');
        menuContent.find('li, li > ul').removeClass('active');
      }

      menuButton.once().on('click', toggleMenu);
      closeButton.once().on('click', toggleMenu);

      if ($(window).width() < 768) {
        menuContent.on('click', 'a', function (e) {
          var $this = $(this);
          if ($this.next().length > 0) {
            if ($this.closest('li').hasClass('active')) {
              menuContent.find('li, li > ul').removeClass('active');
            }
            else {
              $this.closest('li').addClass('active');
              $this.closest('li').find('ul').addClass('active');
            }
            e.preventDefault();
          }
        });
      }
    }
  };

})(jQuery, Drupal);
