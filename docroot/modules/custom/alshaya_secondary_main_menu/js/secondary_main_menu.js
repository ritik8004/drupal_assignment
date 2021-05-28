/**
 * @file
 * Script for Alshaya secondary main menu behaviors.
 */
(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.alshayaSecondaryMainMenu = {
    attach: function (context) {
      if ($('#block-alshayasecondarymainmenu').length) {
        if ($(window).width() > 768) {
          $('#block-branding, #block-alshayasecondarymainmenu')
            .wrapAll('<div class="secondary-main-menu-wrapper"></div>');
          $('.secondary--main--menu').show();
        }
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
