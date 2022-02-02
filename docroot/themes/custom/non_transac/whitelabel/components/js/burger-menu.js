/**
 * @file
 * Sliders.
 */

(function ($, Drupal) {

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
      }

      menuButton.once().on('click', toggleMenu);
      closeButton.once().on('click', toggleMenu);
    }
  };

})(jQuery, Drupal);
